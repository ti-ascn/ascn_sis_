<?php

namespace App\Exports;

use App\Models\Factura;
use App\Models\CotizacionPlantilla;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CotizacionExport
{
    private int $productosFila = 0;
    private int $productosFilasInsertadas = 0;
    private int $cuentasFilasInsertadas = 0;
    private string $colContador = '';
    private string $colDesc = '';
    private string $colCant = '';
    private string $colUnid = '';
    private string $colPrecio = '';
    private string $colTotal = '';
    private string $cuentasCol = '';
    private string $cuentasNroCol = '';
    private int $cuentasFila = 0;
    private string $firmaImagenCol = '';
    private int $firmaImagenFila = 0;

    private array $datos = [];
    private array $mapa = [];
    private string $simbol = 'S/';

    private const CFG_PLACEHOLDERS = [
        'PRODUCTOS_FILA_INICIO',
        'CONTADOR_PRODUCTO',
        'COL_DESCRIPCION',
        'COL_CANTIDAD',
        'COL_UNIDAD',
        'COL_PRECIO',
        'COL_TOTAL',
        'CUENTAS_BLOQUE',
        'CUENTAS_NRO',
        'FIRMA_IMAGEN',
    ];

    public function __construct(
        private Factura $factura,
        private CotizacionPlantilla $plantilla,
    ) {
    }

    // ══════════════════════════════════════════════════════════════
    //  ENTRY POINT
    // ══════════════════════════════════════════════════════════════

    public function generarYGuardar(): string
    {
        $rutaPlantilla = Storage::path($this->plantilla->archivo);
        $spreadsheet = IOFactory::load($rutaPlantilla);
        $sheet = $spreadsheet->getActiveSheet();

        $this->simbol = $this->factura->tipo_moneda === 'USD' ? '$' : 'S/';
        $this->mapa = $this->plantilla->mapa_celdas ?? [];
        $this->datos = $this->recopilarDatos();

        $this->descubrirConfiguracion($sheet);   // Fase 1: lee y borra cfg placeholders
        $this->reemplazarPlaceholders($sheet);   // Fase 2: reemplaza {DATOS}
        $this->escribirProductos($sheet);        // Fase 3: escribe filas de productos
        $this->escribirCuentas($sheet);          // Fase 4: escribe cuentas bancarias
        $this->insertarFirmaImagen($spreadsheet, $sheet); // Fase 5

        $rutaSalida = storage_path(
            'app/temp/cotizacion_' . $this->factura->id_factura . '_' . time() . '.xlsx'
        );
        @mkdir(dirname($rutaSalida), 0755, true);
        (new Xlsx($spreadsheet))->save($rutaSalida);

        return $rutaSalida;
    }

    // ══════════════════════════════════════════════════════════════
    //  FASE 1 — DESCUBRIR Y LIMPIAR PLACEHOLDERS DE CONFIGURACIÓN
    //
    //  Se escanea la hoja completa buscando los nombres de cfg (con
    //  o sin llaves). Cuando los encuentra, guarda la coord y borra
    //  la celda. Después de esta fase, la fila de config de productos
    //  queda vacía y lista para ser usada como primera fila real.
    // ══════════════════════════════════════════════════════════════

    private function descubrirConfiguracion($sheet): void
    {
        foreach ($sheet->getRowIterator() as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $raw = trim((string) $cell->getValue());
                if ($raw === '')
                    continue;

                $key = trim($raw, '{}');
                if (!in_array($key, self::CFG_PLACEHOLDERS, true))
                    continue;

                $col = $cell->getColumn();
                $fila = $cell->getRow();

                switch ($key) {
                    case 'PRODUCTOS_FILA_INICIO':
                        $this->productosFila = $fila;
                        break;
                    case 'CONTADOR_PRODUCTO':
                        $this->colContador = $col;
                        break;
                    case 'COL_DESCRIPCION':
                        $this->colDesc = $col;
                        break;
                    case 'COL_CANTIDAD':
                        $this->colCant = $col;
                        break;
                    case 'COL_UNIDAD':
                        $this->colUnid = $col;
                        break;
                    case 'COL_PRECIO':
                        $this->colPrecio = $col;
                        break;
                    case 'COL_TOTAL':
                        $this->colTotal = $col;
                        break;
                    case 'CUENTAS_BLOQUE':
                        $this->cuentasCol = $col;
                        $this->cuentasFila = $fila;
                        break;
                    case 'CUENTAS_NRO':
                        $this->cuentasNroCol = $col;
                        break;
                    case 'FIRMA_IMAGEN':
                        $this->firmaImagenCol = $col;
                        $this->firmaImagenFila = $fila;
                        break;
                }

                $cell->setValue(null); // limpiar el placeholder de config
            }
        }

        // Fallbacks si la plantilla no tiene alguno
        if (!$this->productosFila)
            $this->productosFila = 25;
        if (!$this->colDesc)
            $this->colDesc = 'B';
        if (!$this->colCant)
            $this->colCant = 'E';
        // colUnid: sin fallback → si la plantilla no tiene {COL_UNIDAD} no se escribe unidad
        if (!$this->colPrecio)
            $this->colPrecio = 'G';
        if (!$this->colTotal)
            $this->colTotal = 'H';
        if (!$this->cuentasNroCol && $this->cuentasCol) {
            $idx = Coordinate::columnIndexFromString($this->cuentasCol);
            $this->cuentasNroCol = Coordinate::stringFromColumnIndex($idx + 2);
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  FASE 2 — REEMPLAZAR {PLACEHOLDERS} DE DATOS
    //
    //  Escanea TODA la hoja sin excepción. Esto es correcto porque:
    //  - Los cfg placeholders ya fueron borrados en Fase 1
    //  - La fila de config de productos ahora está vacía
    //  - {SUBTOTAL}, {IGV}, {TOTAL} están en filas DEBAJO de productos
    //    y necesitan ser reemplazados normalmente
    //
    //  Solo reemplaza celdas cuyo contenido sea EXACTAMENTE {NOMBRE}.
    //  Texto sin llaves nunca se toca (evita borrar etiquetas visuales).
    // ══════════════════════════════════════════════════════════════

    private function reemplazarPlaceholders($sheet): void
    {
        $lookup = [];
        foreach ($this->datos as $clave => $valor) {
            $lookup['{' . $clave . '}'] = $valor;
            if (!empty($this->mapa[$clave])) {
                $custom = trim($this->mapa[$clave], '{}');
                $lookup['{' . $custom . '}'] = $valor;
            }
        }

        // Escanear TODA la hoja — sin saltar ninguna fila
        foreach ($sheet->getRowIterator() as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $val = (string) $cell->getValue();

                // Procesar solo si la celda contiene llaves
                if (strpos($val, '{') === false || strpos($val, '}') === false)
                    continue;

                $originalVal = $val;
                $valTrimmed = trim($val);
                $esExacto = array_key_exists($valTrimmed, $lookup);

                // Hacer el reemplazo de texto en la celda
                foreach ($lookup as $search => $replace) {
                    if (strpos($val, $search) !== false) {
                        $val = str_replace($search, $replace ?? '', $val);
                    }
                }

                if ($val !== $originalVal) {
                    $cell->setValue($val === '' ? null : $val);
                    $cell->getStyle()->getAlignment()->setWrapText(true);

                    // Si la celda era EXACTAMENTE el placeholder (ej. {TOTAL}), centramos por defecto
                    // para preservar el comportamiento visual anterior. Si es texto mezclado, respetamos la alineación.
                    if ($esExacto) {
                        $cell->getStyle()->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                            ->setVertical(Alignment::VERTICAL_CENTER);
                    }
                }
            }
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  FASE 3 — ESCRIBIR PRODUCTOS
    // ══════════════════════════════════════════════════════════════

    private function escribirProductos($sheet): void
    {
        $detalles = $this->factura->detalles()->with('producto')->get();
        if ($detalles->isEmpty())
            return;

        $cant = $detalles->count();
        $estiloBase = $this->capturarEstiloFila($sheet, $this->productosFila);

        $this->productosFilasInsertadas = max(0, $cant - 1);
        if ($this->productosFilasInsertadas > 0) {
            $sheet->insertNewRowBefore($this->productosFila + 1, $this->productosFilasInsertadas);
        }

        $esUsd = $this->factura->tipo_moneda === 'USD';
        $precioDolar = $esUsd ? (float) ($this->factura->precio_dolar ?? 0) : 0;

        foreach ($detalles as $i => $detalle) {
            $fila = $this->productosFila + $i;
            if ($i > 0)
                $this->aplicarEstiloFila($sheet, $estiloBase, $fila);

            // Contador
            if ($this->colContador) {
                $c = $sheet->getCell($this->colContador . $fila);
                $c->setValue($i + 1);
                $this->centrar($c);
            }

            // Descripción: alineada a la izquierda, sin wrap
            if ($this->colDesc) {
                $c = $sheet->getCell($this->colDesc . $fila);
                $c->setValue(strtoupper($this->nombreProducto($detalle)));
                $c->getStyle()->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(false);
            }

            // Cantidad
            if ($this->colCant) {
                $c = $sheet->getCell($this->colCant . $fila);
                $c->setValue((float) $detalle->cantidad);
                $this->centrar($c);
            }

            // Unidad
            if ($this->colUnid) {
                $c = $sheet->getCell($this->colUnid . $fila);
                $c->setValue($detalle->unidad_medida ?? $detalle->producto->unidad_medida ?? '');
                $this->centrar($c);
            }

            // Precio unitario (con conversión USD si aplica)
            if ($this->colPrecio) {
                $precioRaw = (float) $detalle->precio_unitario;
                $precio = ($esUsd && $precioDolar > 0)
                    ? round($precioRaw / $precioDolar, 2)
                    : $precioRaw;
                $c = $sheet->getCell($this->colPrecio . $fila);
                $c->setValue($this->simbol . ' ' . number_format($precio, 2));
                $this->centrar($c);
            }

            // Total línea (con conversión USD si aplica)
            if ($this->colTotal) {
                $totalRaw = (float) $detalle->precio_total;
                $totalVal = ($esUsd && $precioDolar > 0)
                    ? round($totalRaw / $precioDolar, 2)
                    : $totalRaw;
                $c = $sheet->getCell($this->colTotal . $fila);
                $c->setValue($this->simbol . ' ' . number_format($totalVal, 2));
                $this->centrar($c);
            }
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  FASE 4 — CUENTAS BANCARIAS
    // ══════════════════════════════════════════════════════════════

    private function escribirCuentas($sheet): void
    {
        if (!$this->cuentasCol || !$this->cuentasFila)
            return;

        $emisor = $this->factura->emisor;
        if (!$emisor)
            return;

        $q = $emisor->cuentas()->where('sede', 'Trujillo')->where('estado', 1);
        if ($this->factura->tipo_moneda === 'USD') {
            $q->where('tipo', 1);
        } else {
            $q->where('tipo', 0);
        }

        $cuentas = $q->get()->filter(
            fn($c) =>
            !empty(trim($c->nro_cuenta ?? '')) || !empty(trim($c->nro_interbancario ?? ''))
        );

        if ($cuentas->isEmpty())
            return;

        $filaReal = $this->calcularFilaReal($this->cuentasFila);
        $totalFilas = $cuentas->count() * 2;
        if ($totalFilas > 1) {
            $this->cuentasFilasInsertadas = $totalFilas - 1;
            $sheet->insertNewRowBefore($filaReal + 1, $this->cuentasFilasInsertadas);

            // Limpiar formato visual en columnas del costado
            $filaInicio = $filaReal + 1;
            $filaFinal = $filaReal + $this->cuentasFilasInsertadas;

            $sheet->getStyle("D{$filaInicio}:H{$filaFinal}")
                ->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE,
                        ],
                    ],
                ]);
        }

        $estiloFilaBanco = $this->capturarEstiloFila($sheet, $filaReal);
        $fila = $filaReal;

        foreach ($cuentas as $idx => $cuenta) {
            $monedaSufijo = (int) $cuenta->tipo === 1 ? ' (USD)' : ' (PEN)';
            $banco = strtoupper(trim($cuenta->nombre ?? ''));
            $nroCuenta = trim($cuenta->nro_cuenta ?? '');
            $nroCci = trim($cuenta->nro_interbancario ?? '');

            if ($idx > 0) {
                $this->aplicarEstiloFila($sheet, $estiloFilaBanco, $fila);
                $this->aplicarEstiloFila($sheet, $estiloFilaBanco, $fila + 1);
            }

            if (!empty($nroCuenta)) {
                $cB = $sheet->getCell($this->cuentasCol . $fila);
                $cN = $sheet->getCell($this->cuentasNroCol . $fila);
                $cB->setValue($banco . ' CUENTA EN' . $monedaSufijo);
                $cN->setValueExplicit($nroCuenta, DataType::TYPE_STRING);
                $this->centrar($cB);
                $this->centrar($cN);
            }
            $fila++;

            if (!empty($nroCci)) {
                $cBC = $sheet->getCell($this->cuentasCol . $fila);
                $cCI = $sheet->getCell($this->cuentasNroCol . $fila);
                $cBC->setValue($banco . ' - CCI CUENTA EN' . $monedaSufijo);
                $cCI->setValueExplicit($nroCci, DataType::TYPE_STRING);
                $this->centrar($cBC);
                $this->centrar($cCI);
            }
            $fila++;
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  FASE 5 — FIRMA IMAGEN
    // ══════════════════════════════════════════════════════════════

    private function insertarFirmaImagen($spreadsheet, $sheet): void
    {
        if (!$this->firmaImagenCol || !$this->firmaImagenFila)
            return;

        $emisor = $this->factura->emisor;
        $branding = $emisor?->branding;
        if (!$branding || empty($branding->firma_imagen))
            return;

        $rutaImagen = $this->resolverRutaImagen($branding->firma_imagen);
        if (!$rutaImagen)
            return;

        try {
            $filaReal = $this->calcularFilaReal($this->firmaImagenFila);
            $anchoCol = $sheet->getColumnDimension($this->firmaImagenCol)->getWidth();
            $altoFila = $sheet->getRowDimension($filaReal)->getRowHeight();
            $anchoPx = max(120, (int) (($anchoCol > 0 ? $anchoCol : 15) * 7.5));
            $altoPx = max(80, (int) (($altoFila > 0 ? $altoFila : 15) * 1.33));

            // Respetar proporción de la imagen
            [$imgW, $imgH] = @getimagesize($rutaImagen) ?: [0, 0];
            if ($imgW > 0 && $imgH > 0) {
                $ratio = $imgW / $imgH;
                if ($ratio > ($anchoPx / $altoPx)) {
                    $altoPx = (int) ($anchoPx / $ratio);
                } else {
                    $anchoPx = (int) ($altoPx * $ratio);
                }
            }

            $drawing = new Drawing();
            $drawing->setPath($rutaImagen);
            $drawing->setCoordinates($this->firmaImagenCol . $filaReal);
            $drawing->setOffsetX(0);
            $drawing->setOffsetY(0);
            $drawing->setWidth($anchoPx);
            $drawing->setHeight($altoPx);
            $drawing->setResizeProportional(false);
            $drawing->setWorksheet($sheet);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  DATOS
    // ══════════════════════════════════════════════════════════════

    private function recopilarDatos(): array
    {
        $factura = $this->factura->load([
            'emisor',
            'emisor.branding',
            'emisor.cuentas',
            'emisor.direcciones',
            'empresa',
            'empresa.direcciones',
            'cliente',
            'cliente.direcciones',
        ]);

        $emisor = $factura->emisor;
        $branding = $emisor?->branding;
        $receptor = $factura->empresa ?? $factura->cliente;

        $domEmiso = $emisor?->direcciones
            ->where('tipo_domicilio', 'Fiscal')->where('estado', 1)
            ->sortByDesc('id_direccion')->first();

        $domReceptor = null;
        if ($factura->empresa) {
            // Buscar dirección fiscal activa de la empresa
            $domReceptor = $factura->empresa->direcciones
                ->where('tipo_domicilio', 'Fiscal')->where('estado', 1)
                ->sortByDesc('id_direccion')->first();
        } elseif ($factura->cliente) {
            // Receptor es un cliente natural — buscar su dirección fiscal
            $domReceptor = $factura->cliente->direcciones
                ->where('tipo_domicilio', 'Fiscal')->where('estado', 1)
                ->sortByDesc('id_direccion')->first();
        }

        // Primer Punto de Llegada activo del receptor
        $puntollegadaObj = null;
        $ruc = trim($factura->ruc_receptor ?? '');

        if ($ruc !== '') {
            $idEmpresa = \DB::table('empresas_clientes')->where('ruc', $ruc)->value('id_empresas_clientes');
            if ($idEmpresa) {
                $puntollegadaObj = \DB::table('direcciones')
                    ->where('id_empresas_clientes', $idEmpresa)
                    ->where('tipo_domicilio', 'Punto de Llegada')
                    ->where('estado', 1)
                    ->orderByDesc('id_direccion')
                    ->first();
            }
            if (!$puntollegadaObj) {
                $idCliente = \DB::table('clientes_auditoria')->where('ruc', $ruc)->value('id_cliente');
                if ($idCliente) {
                    $puntollegadaObj = \DB::table('direcciones')
                        ->where('id_cliente', $idCliente)
                        ->where('tipo_domicilio', 'Punto de Llegada')
                        ->where('estado', 1)
                        ->orderByDesc('id_direccion')
                        ->first();
                }
            }
        }

        // Fallback por si la relación directa existiera
        if (!$puntollegadaObj) {
            $puntollegadaObj = $factura->empresa?->ultimaDireccionLlegada
                ?? $factura->cliente?->ultimaDireccionLlegada;
        }

        $puntollegada = $this->formatearDomicilio($puntollegadaObj);
        // Totales: total_bruto incluye IGV
        $totalBruto = (float) $factura->total_bruto;
        $precioDolar = (float) ($factura->precio_dolar ?? 0);
        $esUsd = $this->factura->tipo_moneda === 'USD';

        if ($esUsd && $precioDolar > 0) {
            $totalBruto = round($totalBruto / $precioDolar, 2);
        }

        $subtotal = round($totalBruto / 1.18, 2);
        $igv = round($totalBruto - $subtotal, 2);
        $total = $totalBruto;
        $sim = $this->simbol;

        // Fecha
        $fechaEmision = null;
        if (!empty($factura->fecha_emision)) {
            try {
                $fechaEmision = Carbon::parse($factura->fecha_emision)->startOfDay();
            } catch (\Throwable) {
            }
        }
        $fechaFormateada = $fechaEmision?->format('d/m/Y') ?? '';

        [$tipoPago, $vigencia] = $this->calcularTipoPagoYVigencia($factura, $fechaEmision);

        $firmaNombre = trim($branding?->firma_nombre ?? '');
        $firmaCompleto = $firmaNombre !== ''
            ? 'GERENTE GENERAL - ' . strtoupper($firmaNombre)
            : 'GERENTE GENERAL';

        return [
            'FECHA' => $fechaFormateada,
            'FECHA_EMISOR' => $fechaFormateada,
            'NRO_COTIZACION' => $factura->nro_cotizacion ?? $factura->nombre_correlativo ?? '',
            'NRO_REQUERIMIENTO' => $factura->numero_requerimiento ?? $factura->numero_solicitud ?? '',

            'EMISOR_RUC' => $emisor?->ruc ?? '',
            'EMISOR_RAZON_SOCIAL' => $emisor?->razon_social ?? '',
            'EMISOR_DOMICILIO' => $this->formatearDomicilio($domEmiso),
            'EMISOR_DEPARTAMENTO' => $domEmiso?->region ?? '',
            'EMISOR_TELEFONO' => $emisor?->nro_celular ?? '',
            'EMISOR_WEB' => $emisor?->pagina_web ?? '',
            'EMISOR_EMAIL' => $emisor?->correo ?? '',

            'CLIENTE_RAZON_SOCIAL' => $receptor?->razon_social ?? '',
            'CLIENTE_RUC' => $receptor?->ruc ?? '',
            'CLIENTE_DOMICILIO' => $this->formatearDomicilio($domReceptor),
            'CLIENTE_DEPARTAMENTO' => $domReceptor?->region ?? '',
            'CLIENTE_EMAIL' => $receptor?->correo ?? $receptor?->contacto ?? '',
            'CLIENTE_PUNTO_LLEGADA' => $puntollegada,

            'TIPO_PAGO' => $tipoPago,
            'VIGENCIA' => $vigencia === '' ? '-' : $vigencia,

            'SUBTOTAL' => $sim . ' ' . number_format($subtotal, 2),
            'IGV' => $sim . ' ' . number_format($igv, 2),
            'TOTAL' => $sim . ' ' . number_format($total, 2),

            'FIRMA_NOMBRE' => $firmaCompleto,
            'FIRMA_CARGO' => 'GERENTE GENERAL',
            'TRANSPORTE' => '',
            'COMENTARIO' => '',
        ];
    }

    // ══════════════════════════════════════════════════════════════
    //  HELPERS
    // ══════════════════════════════════════════════════════════════

    private function calcularTipoPagoYVigencia(Factura $factura, ?Carbon $fechaEmision): array
    {
        if (empty($factura->fecha_pago)) {
            return ['CONTADO', ''];
        }

        try {
            $fechaPago = Carbon::parse($factura->fecha_pago)->startOfDay();
            $fechaBase = $fechaEmision ?? $fechaPago->copy();
            $dias = (int) $fechaBase->diffInDays($fechaPago, false);

            if ($dias <= 0)
                return ['CONTADO', ''];

            $meses = (int) round($dias / 30);
            if ($dias % 30 === 0 || ($meses >= 1 && abs($dias - $meses * 30) <= 2)) {
                $vigencia = $meses === 1 ? '1 MES' : "{$meses} MESES";
            } else {
                $vigencia = "{$dias} DÍA" . ($dias > 1 ? 'S' : '');
            }

            return ['CRÉDITO', $vigencia];
        } catch (\Throwable) {
            return ['CONTADO', ''];
        }
    }

    private function formatearDomicilio($dom): string
    {
        if (!$dom)
            return '';
        return implode(', ', array_filter([
            $dom->nombre ?? null,
            $dom->distrito ?? null,
            $dom->provincia ?? null,
            $dom->region ?? null,
        ]));
    }

    private function resolverRutaImagen(string $ruta): ?string
    {
        if (Storage::exists($ruta))
            return Storage::path($ruta);
        if (file_exists(public_path($ruta)))
            return public_path($ruta);
        if (filter_var($ruta, FILTER_VALIDATE_URL)) {
            $tmp = storage_path('app/temp/firma_' . $this->factura->id_factura . '.tmp');
            @mkdir(dirname($tmp), 0755, true);
            $data = @file_get_contents($ruta);
            if ($data) {
                file_put_contents($tmp, $data);
                return $tmp;
            }
        }
        return null;
    }

    private function nombreProducto($detalle): string
    {
        return $detalle->producto?->nombre
            ?? $detalle->nombre
            ?? (string) ($detalle->id_producto ?? '');
    }

    private function centrar($cell): void
    {
        $cell->getStyle()->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(false);
    }

    private function capturarEstiloFila($sheet, int $fila): array
    {
        $estilos = [];
        foreach ($sheet->getRowIterator($fila, $fila) as $row) {
            foreach ($row->getCellIterator() as $cell) {
                try {
                    $s = $cell->getStyle();
                    $estilos[$cell->getColumn()] = [
                        'font' => [
                            'bold' => $s->getFont()->getBold(),
                            'size' => $s->getFont()->getSize(),
                            'name' => $s->getFont()->getName(),
                            'color' => ['argb' => $s->getFont()->getColor()->getARGB()],
                        ],
                        'fill' => [
                            'fillType' => $s->getFill()->getFillType(),
                            'startColor' => ['argb' => $s->getFill()->getStartColor()->getARGB()],
                        ],
                        'alignment' => [
                            'horizontal' => $s->getAlignment()->getHorizontal(),
                            'vertical' => $s->getAlignment()->getVertical(),
                            'wrapText' => false,
                        ],
                    ];
                } catch (\Throwable) {
                }
            }
        }
        return $estilos;
    }

    private function aplicarEstiloFila($sheet, array $estilos, int $fila): void
    {
        foreach ($estilos as $col => $style) {
            try {
                $sheet->getStyle($col . $fila)->applyFromArray($style);
            } catch (\Throwable) {
            }
        }
    }

    private function calcularFilaReal(int $filaOriginal): int
    {
        $fila = $filaOriginal;
        if ($this->productosFila > 0 && $filaOriginal > $this->productosFila) {
            $fila += $this->productosFilasInsertadas;
        }
        if ($this->cuentasFila > 0 && $filaOriginal > $this->cuentasFila) {
            $fila += $this->cuentasFilasInsertadas;
        }
        return $fila;
    }
}
