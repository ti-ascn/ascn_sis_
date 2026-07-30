<?php

namespace App\Exports;

use App\Models\EmpresasClientes;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CapitalesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected $empresas;
    protected $maxAumentos = 0;

    public function __construct()
    {
        // Obtenemos las empresas de estado A1 con capital y sus aumentos
        $this->empresas = EmpresasClientes::where('estado', 'A1')
            ->with(['capital.aumentos' => function ($q) {
                $q->orderBy('fecha', 'asc');
            }])->get();

        // Calcular el número máximo de aumentos para definir las columnas dinámicas
        foreach ($this->empresas as $empresa) {
            if ($empresa->capital) {
                $count = $empresa->capital->aumentos->count();
                if ($count > $this->maxAumentos) {
                    $this->maxAumentos = $count;
                }
            }
        }
    }

    public function collection()
    {
        return $this->empresas;
    }

    public function headings(): array
    {
        $headings = [
            'ESTADO',
            'RAZON SOCIAL',
            'RUC',
            'PARTIDA',
            'CAPITAL CONSTITUIDO',
            'ADICIONES',
            'TOTAL',
            'FECHA C. CONSTITUIDO',
            'OBSERVACIONES'
        ];

        for ($i = 1; $i <= $this->maxAumentos; $i++) {
            $headings[] = "FECHA ABONO $i";
            $headings[] = "MONTO ABONO $i";
            $headings[] = "BANCO ABONO $i";
            $headings[] = "OBSERVACIONES ABONO $i";
        }

        return $headings;
    }

    public function map($empresa): array
    {
        $capital = $empresa->capital;

        $row = [
            $empresa->estado,
            $empresa->razon_social,
            $empresa->ruc ?? '—',
            $capital ? $capital->partida : '—',
            $capital ? (float) $capital->capital_Constituido : '—',
            $capital ? (float) ($capital->total_capital - $capital->capital_Constituido) : '—',
            $capital ? (float) $capital->total_capital : '—',
            ($capital && $capital->fecha) ? \Carbon\Carbon::parse($capital->fecha)->format('d/m/Y') : '—',
            $capital ? ($capital->observaciones ?? '—') : '—'
        ];

        // Añadir datos de los aumentos si existen
        $aumentos = $capital ? $capital->aumentos->values() : collect();

        for ($i = 0; $i < $this->maxAumentos; $i++) {
            if (isset($aumentos[$i])) {
                $aumento = $aumentos[$i];
                $row[] = $aumento->fecha ? \Carbon\Carbon::parse($aumento->fecha)->format('d/m/Y') : '—';
                $row[] = (float) $aumento->monto_Capital;
                $row[] = $aumento->banco ?? 'SIN BANCO';
                $row[] = $aumento->observaciones ?? '—';
            } else {
                // Rellenar con vacío si no hay más aumentos para esta empresa
                $row[] = '—';
                $row[] = '—';
                $row[] = '—';
                $row[] = '—';
            }
        }

        return $row;
    }

    public function columnFormats(): array
    {
        // Formato para soles con 2 decimales fijos
        $solesFormat = '"S/ "#,##0';

        $formats = [
            'E' => $solesFormat, // CAPITAL CONSTITUIDO
            'F' => $solesFormat, // ADICIONES
            'G' => $solesFormat, // TOTAL
        ];

        $colIndex = 11; // La columna "MONTO ABONO 1" empieza en K (índice 11)
        for ($i = 0; $i < $this->maxAumentos; $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $formats[$colLetter] = $solesFormat;
            $colIndex += 4; // Avanzamos 4 columnas para el siguiente monto
        }

        return $formats;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
