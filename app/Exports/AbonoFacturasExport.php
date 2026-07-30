<?php

namespace App\Exports;

use App\Models\EmpresasClientes;
use App\Models\ClienteAuditoria;
use App\Models\Factura;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AbonoFacturasExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected $ruc;
    protected $mes;
    protected $anio;
    protected $tipo;         // 'empresa' | 'cliente'
    protected $nombreEntidad;
    protected $maxAbonos = 0;
    protected $filasConGrupo = [];
    protected $estado;       // 'todas' | 'ambas' | 'bancarizado' | 'bancarizado_cliente' | 'pendiente'
    protected $showConversionCols = false;
    protected $desde;        // 'YYYY-MM'
    protected $hasta;        // 'YYYY-MM'
    protected $rol;          // 'emisor' | 'receptor'

    public function __construct(string $ruc, ?int $mes, ?int $anio, string $tipo = 'empresa', string $estado = 'todas', ?string $desde = null, ?string $hasta = null, string $rol = 'receptor')
    {
        $this->ruc = $ruc;
        $this->mes = $mes;
        $this->anio = $anio;
        $this->tipo = $tipo;
        $this->estado = $estado;
        $this->desde = $desde;
        $this->hasta = $hasta;
        $this->rol = $rol;

        if ($this->desde && !$this->hasta) {
            $this->hasta = $this->desde;
        } elseif (!$this->desde && $this->hasta) {
            $this->desde = $this->hasta;
        }

        $this->filasConGrupo = [];

        $entidad = EmpresasClientes::where('ruc', $ruc)->first()
            ?? ClienteAuditoria::where('ruc', $ruc)->first();

        if ($entidad) {
            $this->nombreEntidad = $entidad->razon_social;
        } else {
            // Si no está en las tablas maestras, intentar sacarlo de alguna factura del periodo
            $queryEntidad = Factura::where($this->rol === 'emisor' ? 'ruc_emisor' : 'ruc_receptor', $ruc);
            if ($this->desde) {
                $queryEntidad->where('fecha_emision', '>=', $this->desde . '-01')
                    ->where('fecha_emision', '<=', \Carbon\Carbon::parse($this->hasta . '-01')->endOfMonth()->format('Y-m-d'));
            } elseif ($this->mes && $this->anio) {
                $queryEntidad->whereYear('fecha_emision', $this->anio)
                    ->whereMonth('fecha_emision', $this->mes);
            }
            $factura = $queryEntidad->first();

            $this->nombreEntidad = $factura?->empresa?->razon_social
                ?? ($factura?->cliente?->razon_social ?? $ruc);
        }

        $queryIds = Factura::where($this->rol === 'emisor' ? 'ruc_emisor' : 'ruc_receptor', $this->ruc);
        if ($this->desde) {
            $queryIds->where('fecha_emision', '>=', $this->desde . '-01')
                ->where('fecha_emision', '<=', \Carbon\Carbon::parse($this->hasta . '-01')->endOfMonth()->format('Y-m-d'));
        } else {
            $queryIds->whereYear('fecha_emision', $this->anio)
                ->whereMonth('fecha_emision', $this->mes);
        }
        $ids = $queryIds->pluck('id_factura');

        $this->maxAbonos = $ids->isEmpty() ? 0 :
            \App\Models\AbonoFactura::whereIn('id_factura', $ids)
                ->selectRaw('COUNT(*) as cnt')
                ->groupBy('id_factura')
                ->orderByDesc('cnt')
                ->value('cnt') ?? 0;

        $this->showConversionCols = Factura::whereIn('id_factura', $ids)
            ->where('tipo_moneda', 'PEN')
            ->whereHas('abonos', fn($q) => $q->where('moneda', 'USD'))
            ->exists();
    }

    public function collection()
    {
        // Buscar facturas donde esta empresa/cliente es el PROVEEDOR (ruc_emisor)
        $query = Factura::with([
            'empresa',
            'cliente',
            'emisor',
            'abonos' => function ($q) {
                $q->orderBy('fecha')->orderBy('id_abono_factura');
            }
        ])
            ->where($this->rol === 'emisor' ? 'ruc_emisor' : 'ruc_receptor', $this->ruc);

        if ($this->desde) {
            $query->where('fecha_emision', '>=', $this->desde . '-01')
                ->where('fecha_emision', '<=', \Carbon\Carbon::parse($this->hasta . '-01')->endOfMonth()->format('Y-m-d'));
        } else {
            $query->whereYear('fecha_emision', $this->anio)
                ->whereMonth('fecha_emision', $this->mes);
        }

        // *** FILTRO PRINCIPAL: Solo estados "Facturada" y "Pendiente" ***
        $query->whereIn('estado', ['Facturada', 'Pendiente']);

        // Si aún necesitas el filtro adicional por estado_abono (opcional)
        if ($this->estado === 'ambas') {
            $query->whereIn('estado_abono', ['bancarizado', 'bancarizado_cliente']);
        } elseif ($this->estado !== 'todas') {
            $query->where('estado_abono', $this->estado);
        }

        $facturas = $query->orderBy('fecha_emision')
            ->get();

        // Calcular el máximo número de abonos entre todas las facturas
        $this->maxAbonos = $facturas->max(fn($f) => $f->abonos->count()) ?? 0;

        $rows = collect();
        $rowIndex = 0;
        $grupoMap = [];
        $grupoCounter = 1;

        foreach ($facturas as $factura) {
            $razonReceptor = $factura->empresa->razon_social
                ?? ($factura->cliente->razon_social
                    ?? (EmpresasClientes::where('ruc', $factura->ruc_receptor)->value('razon_social')
                        ?? (ClienteAuditoria::where('ruc', $factura->ruc_receptor)->value('razon_social') ?? '—')));

            $contactoReceptor = $factura->empresa->contacto
                ?? ($factura->cliente->contacto ?? '—');

            $razonEmisor = EmpresasClientes::where('ruc', $factura->ruc_emisor)->value('razon_social')
                ?? (ClienteAuditoria::where('ruc', $factura->ruc_emisor)->value('razon_social')
                    ?? ($factura->emisor->razon_social ?? '—'));

            $simbolo = $factura->tipo_moneda === 'USD' ? '$' : 'S/';

            if ($factura->tipo_moneda === 'USD') {
                $montoBase = $factura->precio_dolar > 0
                    ? round($factura->total_neto / $factura->precio_dolar, 2)
                    : round($factura->total_neto, 2);
            } else {
                $montoBase = round($factura->total_neto, 2);
            }

            $totalAbonadoRaw = $factura->abonos->sum(function ($abono) use ($factura) {
                if ($abono->moneda === $factura->tipo_moneda) {
                    return $abono->monto;
                }
                $tc = $abono->precio_dolar > 0 ? $abono->precio_dolar : ($factura->precio_dolar > 0 ? $factura->precio_dolar : 1);
                if ($factura->tipo_moneda === 'USD' && $abono->moneda === 'PEN') {
                    return $abono->monto / $tc;
                } elseif ($factura->tipo_moneda === 'PEN' && $abono->moneda === 'USD') {
                    return $abono->monto * $tc;
                }
                return $abono->monto;
            });
            $totalAbonado = round($totalAbonadoRaw, 2);
            $montoPendiente = max(0, $montoBase - $totalAbonado);
            $grupoId = $factura->abonos->whereNotNull('id_grupo_abono')->first()?->id_grupo_abono;
            $loteLabel = '';
            if ($grupoId) {
                if (!isset($grupoMap[$grupoId])) {
                    $grupoMap[$grupoId] = $grupoCounter++;
                }
                $loteLabel = 'LOTE ' . $grupoMap[$grupoId];
                $this->filasConGrupo[] = $rowIndex;
            }

            $row = [
                $contactoReceptor,
                $loteLabel,
                $factura->ruc_emisor,
                $razonEmisor,
                $factura->nro_factura ?? 'Sin nro.',
                $factura->ruc_receptor,
                $razonReceptor,
                $factura->tipo_moneda,
                $montoBase,
                \Carbon\Carbon::parse($factura->fecha_emision)->format('d/m/Y'),
                $totalAbonado,
                $montoPendiente,
                strtoupper($factura->estado_abono ?? 'pendiente'),
            ];

            if ($this->showConversionCols) {
                $equivalenteUsd = '';
                $tipoCambio = '';

                if ($factura->tipo_moneda === 'PEN') {
                    $abonosUsd = $factura->abonos
                        ->where('moneda', 'USD')
                        ->where('precio_dolar', '>', 0)
                        ->sortBy(fn($a) => abs(
                            \Carbon\Carbon::parse($a->fecha)->diffInDays($factura->fecha_emision)
                        ));

                    $abonoRef = $abonosUsd->first();

                    if ($abonoRef) {
                        $tc = (float) $abonoRef->precio_dolar;
                        $tipoCambio = $tc;
                        $equivalenteUsd = $tc > 0 ? round($montoBase / $tc, 2) : '';
                    } elseif ($factura->abonos->where('moneda', 'USD')->isNotEmpty()) {
                        $tipoCambio = 'Sin T.C.';
                        $equivalenteUsd = '';
                    }
                }

                $row[] = $equivalenteUsd;
                $row[] = $tipoCambio;
            }

            foreach ($factura->abonos as $abono) {
                $row[] = \Carbon\Carbon::parse($abono->fecha)->format('d/m/Y');
                $row[] = $abono->monto;
                $row[] = $abono->moneda;
                $row[] = $abono->banco ?? '—';
                $row[] = $abono->nro_operacion ?? '—';
            }

            $abonosFaltantes = max(0, $this->maxAbonos - $factura->abonos->count());
            for ($i = 0; $i < $abonosFaltantes; $i++) {
                $row[] = '';
                $row[] = '';
                $row[] = '';
                $row[] = '';
                $row[] = '';
            }

            $rowIndex++;
            $rows->push($row);
        }

        return $rows;
    }

    public function headings(): array
    {
        $base = [
            'CONTACTO',
            'LOTE',
            'RUC PROVEEDOR',
            'RAZÓN SOCIAL PROVEEDOR',
            'NRO FACTURA',
            'RUC RECEPTOR',
            'RAZÓN SOCIAL RECEPTOR',
            'MONEDA',
            'TOTAL FACTURA',
            'FECHA FACTURA',
            'TOTAL ABONADO',
            'PENDIENTE',
            'ESTADO',
        ];

        if ($this->showConversionCols) {
            $base[] = 'EQUIVALENTE USD';
            $base[] = 'TIPO DE CAMBIO (S/)';
        }

        for ($i = 1; $i <= $this->maxAbonos; $i++) {
            $base[] = "ABONO {$i} - FECHA";
            $base[] = "ABONO {$i} - MONTO";
            $base[] = "ABONO {$i} - MONEDA";
            $base[] = "ABONO {$i} - BANCO";
            $base[] = "ABONO {$i} - NRO OPERACIÓN";
        }

        return $base;
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        // Cabecera principal: azul oscuro
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);

        $abonoStartIdx = $this->showConversionCols ? 16 : 14;

        if ($this->showConversionCols) {
            $sheet->getStyle("N1:O1")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '065F46']],
            ]);
        }

        // Cabeceras de abonos: morado
        $colIdx = $abonoStartIdx; // empieza en columna K (índice 11, base 1)
        for ($i = 1; $i <= $this->maxAbonos; $i++) {
            $colStart = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
            $colEnd = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 4);
            $sheet->getStyle("{$colStart}1:{$colEnd}1")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6B21A8']],
            ]);
            $colIdx += 5;
        }

        // Filas de datos
        if ($lastRow > 1) {
            $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
                'font' => ['size' => 9],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Filas alternas
            for ($row = 2; $row <= $lastRow; $row++) {
                if ($row % 2 === 0) {
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                    ]);
                }
            }

            // Columna ESTADO (M): colorear según valor
            for ($row = 2; $row <= $lastRow; $row++) {
                $estado = $sheet->getCell("M{$row}")->getValue();
                if ($estado === 'BANCARIZADO') {
                    $sheet->getStyle("M{$row}")->applyFromArray([
                        'font' => ['color' => ['rgb' => '166534'], 'bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCFCE7']],
                    ]);
                } else if ($estado === 'BANCARIZADO_CLIENTE') {
                    $sheet->getStyle("M{$row}")->applyFromArray([
                        'font' => ['color' => ['rgb' => '003f79'], 'bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '699bc8']],
                    ]);
                } else if ($estado === 'PENDIENTE') {
                    $sheet->getStyle("M{$row}")->applyFromArray([
                        'font' => ['color' => ['rgb' => '92400E'], 'bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF9C3']],
                    ]);
                }
            }

            // Columna PENDIENTE (L): rojo si > 0
            for ($row = 2; $row <= $lastRow; $row++) {
                $pendiente = $sheet->getCell("L{$row}")->getValue();
                if (is_numeric($pendiente) && $pendiente > 0) {
                    $sheet->getStyle("L{$row}")->getFont()->getColor()->setRGB('DC2626');
                    $sheet->getStyle("L{$row}")->getFont()->setBold(true);
                }
            }

            // Números: alineación derecha para montos
            foreach (['I', 'K', 'L'] as $col) {
                $sheet->getStyle("{$col}2:{$col}{$lastRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("{$col}2:{$col}{$lastRow}")
                    ->getNumberFormat()->setFormatCode('#,##0.00');
            }

            if ($this->showConversionCols) {
                $sheet->getStyle("N2:N{$lastRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("N2:N{$lastRow}")
                    ->getNumberFormat()->setFormatCode('#,##0.00');

                $sheet->getStyle("O2:O{$lastRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("O2:O{$lastRow}")
                    ->getNumberFormat()->setFormatCode('#,##0.000');

                for ($row = 2; $row <= $lastRow; $row++) {
                    $val = $sheet->getCell("N{$row}")->getValue();
                    if (is_numeric($val) && $val > 0) {
                        $sheet->getStyle("N{$row}:O{$row}")->applyFromArray([
                            'font' => ['color' => ['rgb' => '065F46'], 'bold' => true],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
                        ]);
                    }
                }
            }

            // Montos de abonos también
            $colIdx = $abonoStartIdx;
            for ($i = 1; $i <= $this->maxAbonos; $i++) {
                $colMonto = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
                $sheet->getStyle("{$colMonto}2:{$colMonto}{$lastRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("{$colMonto}2:{$colMonto}{$lastRow}")
                    ->getNumberFormat()->setFormatCode('#,##0.00');
                $colIdx += 5;
            }

            foreach ($this->filasConGrupo as $rowIdx) {
                $excelRow = $rowIdx + 2;
                $sheet->getStyle("A{$excelRow}:{$lastCol}{$excelRow}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EDE9FE'],
                    ],
                ]);
                $sheet->getStyle("B{$excelRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '6B21A8'], 'size' => 8],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
            }
        }

        return [];
    }

    public function registerEvents(): array
    {
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        return [
            AfterSheet::class => function (AfterSheet $event) use ($meses) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = $sheet->getHighestColumn();

                // Insertar 2 filas de título arriba
                $sheet->insertNewRowBefore(1, 2);

                // Título principal
                $sheet->mergeCells("A1:{$lastCol}1");
                $labelRol = $this->rol === 'emisor' ? 'PROVEEDOR' : 'RECEPTOR';
                $sheet->setCellValue('A1', $labelRol . ': ' . strtoupper($this->nombreEntidad) . ' — ABONOS DE FACTURAS');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                $sheet->mergeCells("A2:{$lastCol}2");
                $subtitulo = 'RUC: ' . $this->ruc . '   |   ';
                if ($this->desde) {
                    $txtDesde = ucfirst(\Carbon\Carbon::parse($this->desde . '-01')->isoFormat('MMMM YYYY'));
                    if ($this->desde === $this->hasta) {
                        $subtitulo .= "PERIODO: $txtDesde";
                    } else {
                        $txtHasta = ucfirst(\Carbon\Carbon::parse($this->hasta . '-01')->isoFormat('MMMM YYYY'));
                        $subtitulo .= "PERIODO: $txtDesde → $txtHasta";
                    }
                } else {
                    $nombreMes = $meses[$this->mes] ?? $this->mes;
                    $subtitulo .= "MES: $nombreMes $this->anio";
                }

                $sheet->setCellValue('A2', $subtitulo);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(18);

                // Freeze panes debajo de cabeceras
                $sheet->freezePane('A4');

                // Altura de la fila de encabezados
                $sheet->getRowDimension(3)->setRowHeight(36);
            },
        ];
    }

    public function title(): string
    {
        return 'Abonos Facturas';
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 30,  // CONTACTO
            'B' => 10,  // LOTE
            'C' => 16,  // RUC PROVEEDOR
            'D' => 38,  // RAZÓN SOCIAL PROVEEDOR
            'E' => 22,  // NRO FACTURA
            'F' => 14,  // RUC RECEPTOR
            'G' => 38,  // RAZÓN SOCIAL RECEPTOR
            'H' => 10,  // MONEDA
            'I' => 16,  // TOTAL FACTURA
            'J' => 14,  // FECHA FACTURA
            'K' => 16,  // TOTAL ABONADO
            'L' => 16,  // PENDIENTE
            'M' => 15,  // ESTADO
        ];

        // Columnas dinámicas de abonos (5 cols por abono)
        $abonoStartIdx = 14;
        if ($this->showConversionCols) {
            $widths['N'] = 18;
            $widths['O'] = 20;
            $abonoStartIdx = 16;
        }

        $colIdx = $abonoStartIdx;
        for ($i = 1; $i <= $this->maxAbonos; $i++) {
            foreach ([14, 14, 10, 16, 20] as $w) {
                $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $widths[$letter] = $w;
                $colIdx++;
            }
        }

        return $widths;
    }

    public function getNombreArchivo(): string
    {
        $nombre = preg_replace('/[^A-Za-z0-9_\-]/', '_', strtoupper($this->nombreEntidad));
        $nombre = preg_replace('/_+/', '_', trim($nombre, '_'));
        $meses = [
            1 => 'ENE',
            2 => 'FEB',
            3 => 'MAR',
            4 => 'ABR',
            5 => 'MAY',
            6 => 'JUN',
            7 => 'JUL',
            8 => 'AGO',
            9 => 'SEP',
            10 => 'OCT',
            11 => 'NOV',
            12 => 'DIC',
        ];

        if ($this->desde) {
            $desde = str_replace('-', '', $this->desde);
            $hasta = $this->hasta ? str_replace('-', '', $this->hasta) : 'FIN';
            $periodo = "{$desde}_a_{$hasta}";
        } else {
            $mes = $meses[$this->mes] ?? str_pad($this->mes, 2, '0', STR_PAD_LEFT);
            $periodo = "{$mes}_{$this->anio}";
        }

        $ts = now()->format('YmdHis');
        return "ABONOS_FACTURAS_{$nombre}_{$periodo}_{$ts}.xlsx";
    }
}