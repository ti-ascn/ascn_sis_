<?php

namespace App\Http\Controllers;

use App\Models\CuentaEmpresa;
use App\Models\EmpresasClientes;
use App\Models\EstadoCuenta;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Str;

class EstadoCuentaController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'id_cuenta_empresa' => 'required|exists:cuentas_empresas,id_cuentas_empresa',
                'mes' => 'required|integer|min:1|max:12',
                'anio' => 'required|integer',
                'recepcion' => 'required|in:SI,NO,N/A',
                'estatus' => 'required',
                'observacion' => 'nullable|string',
                'archivo_pdf' => 'nullable|file|extensions:pdf|max:20480', // Cambiado mimes por extensions para permitir PDFs con clave
            ]);

            $estado = EstadoCuenta::updateOrCreate(
                [
                    'id_cuenta_empresa' => $request->id_cuenta_empresa,
                    'mes' => $request->mes,
                    'anio' => $request->anio,
                ],
                [
                    'recepcion' => $request->recepcion,
                    'estatus' => $request->estatus,
                    'observacion' => $request->observacion,
                    'id_usuario' => auth()->id(),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | PDF
            |--------------------------------------------------------------------------
            */
            if ($request->hasFile('archivo_pdf')) {
                $estado->load('cuenta.empresa');
                $estado->archivo_pdf = $this->guardarArchivoPdf($request->file('archivo_pdf'), $estado);
            }

            $estado->save();

            return back()->with('success', 'Estado de cuenta registrado correctamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e; // Dejar que Laravel maneje la validación normalmente
        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage())->withInput();
        }
    }

    public function index(Request $request)
    {
        $anio = $request->anio ?? now()->year;

        $cuentas = CuentaEmpresa::where('manejo_estado_cuenta', 1)
            ->with([
                'empresa',
                'estadosCuenta' => fn($q) => $q->where('anio', $anio),
                'estadosCuenta.usuario',
            ])
            ->get();

        return view('estados_cuenta.index', compact('cuentas', 'anio'));
    }


    public function exportarExcel(Request $request)
    {
        $anio = $request->anio ?? now()->year;

        $cuentas = CuentaEmpresa::where('manejo_estado_cuenta', 1)
            ->with([
                'empresa',
                'estadosCuenta' => fn($q) => $q->where('anio', $anio)
            ])
            ->get()
            ->sortBy(fn($cuenta) => $cuenta->empresa->ruc)
            ->values();

        $data = [];

        foreach ($cuentas as $cuenta) {
            $fila = [
                $cuenta->empresa->estado,
                $cuenta->empresa->ruc,
                $cuenta->empresa->razon_social,
                $cuenta->nombre . ' (' . ($cuenta->tipo == 0 ? 'S/' : '$') . ')',
            ];

            for ($m = 1; $m <= 12; $m++) {
                $estado = $cuenta->estadosCuenta->firstWhere('mes', $m);

                // 👉 SOLO SIGLA (como la vista)
                $fila[] = $estado ? $estado->estatus : '—';
            }

            $data[] = $fila;
        }

        $timestamp = now()->format('Ymd_His');
        $nombreArchivo = "ESTADOS_DE_CUENTA_{$anio}_{$timestamp}.xlsx";

        return Excel::download(
            new class ($data, $cuentas) implements FromArray, WithHeadings, WithStyles, ShouldAutoSize {

            private array $data;
            private $cuentas;

            public function __construct($data, $cuentas)
            {
                $this->data = $data;
                $this->cuentas = $cuentas;
            }

            public function array(): array
            {
                return $this->data;
            }

            public function headings(): array
            {
                return array_merge(
                    ['Estado', 'RUC', 'Razón Social', 'Cuenta'],
                    ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']
                );
            }

            public function styles(Worksheet $sheet)
            {
                $ultimaFila = $sheet->getHighestRow();

                for ($row = 2; $row <= $ultimaFila; $row++) {
                    for ($col = 5; $col <= 16; $col++) {

                        $cell = $sheet->getCellByColumnAndRow($col, $row);

                        // Mapeo de cuenta/mes
                        $cuentaIndex = $row - 2;
                        $mes = $col - 4;

                        $estado = $this->cuentas[$cuentaIndex]
                            ->estadosCuenta
                            ->firstWhere('mes', $mes);

                        if (!$estado) {
                            continue;
                        }

                        // 🎨 COLOR SOLO POR RECEPCIÓN
                        switch ($estado->recepcion) {
                            case 'SI':
                                $cell->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)
                                    ->getStartColor()->setRGB('22C55E'); // verde
                                break;

                            case 'NO':
                                $cell->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)
                                    ->getStartColor()->setRGB('EF4444'); // rojo
                                break;

                            case 'N/A':
                                $cell->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)
                                    ->getStartColor()->setRGB('000000'); // negro
                                $cell->getStyle()->getFont()->getColor()->setRGB('FFFFFF');
                                break;
                        }

                        // Centrar texto
                        $cell->getStyle()->getAlignment()->setHorizontal('center');
                        $cell->getStyle()->getAlignment()->setVertical('center');
                    }
                }
            }
            },
            $nombreArchivo
        );
    }

    public function todos(Request $request)
    {
        // Consulta todos los estados de cuenta con sus relaciones
        $query = EstadoCuenta::with(['cuenta.empresa', 'usuario'])
            ->orderBy('anio', 'desc')
            ->orderBy('mes', 'desc');

        // Filtros opcionales
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->whereHas('cuenta.empresa', function ($q) use ($buscar) {
                $q->where('razon_social', 'like', "%{$buscar}%")
                    ->orWhere('ruc', 'like', "%{$buscar}%");
            })->orWhereHas('cuenta', function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('anio')) {
            $query->where('anio', $request->anio);
        }

        if ($request->filled('mes')) {
            $query->where('mes', $request->mes);
        }

        $estados = $query->paginate(100);

        // Para los selects de filtros
        $anios = EstadoCuenta::select('anio')->distinct()->orderBy('anio', 'desc')->pluck('anio');
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

        return view('estados_cuenta.todos', compact('estados', 'anios', 'meses'));
    }

    public function destroy($id)
    {
        try {
            $estado = EstadoCuenta::findOrFail($id);

            // Eliminar PDF si existe
            if ($estado->archivo_pdf && file_exists(public_path($estado->archivo_pdf))) {
                @unlink(public_path($estado->archivo_pdf));
            }

            $estado->delete();

            return response()->json(['success' => true, 'message' => 'Registro eliminado correctamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }

    // También necesitamos un método para obtener un registro específico (para editar)
    public function show($id)
    {
        $estado = EstadoCuenta::with(['cuenta.empresa', 'usuario'])->findOrFail($id);
        return response()->json($estado);
    }

    // Método update existente (puedes crear uno específico para actualizar desde la nueva vista)
    public function update(Request $request, $id)
    {
        $request->validate([
            'recepcion' => 'required|in:SI,NO,N/A',
            'estatus' => 'required',
            'observacion' => 'nullable|string',
            'archivo_pdf' => 'nullable|file|extensions:pdf|max:10240',
        ]);

        $estado = EstadoCuenta::findOrFail($id);

        $estado->recepcion = $request->recepcion;
        $estado->estatus = $request->estatus;
        $estado->observacion = $request->observacion;
        $estado->id_usuario = auth()->id();

        // Manejo de PDF
        if ($request->hasFile('archivo_pdf')) {
            $estado->archivo_pdf = $this->guardarArchivoPdf($request->file('archivo_pdf'), $estado);
        }

        $estado->save();

        return response()->json(['success' => true, 'message' => 'Registro actualizado correctamente.', 'data' => $estado]);
    }
    /**Guarda el archivo PDF en el servidor con un nombre robusto que incluye timestamp*/
    private function guardarArchivoPdf($file, $estado): string
    {
        $empresa = $estado->cuenta->empresa;
        $ruc = $empresa->ruc;
        $mes = str_pad($estado->mes, 2, '0', STR_PAD_LEFT);
        $anio = $estado->anio;
        $periodo = "{$anio}{$mes}";

        $palabras = array_filter(
            preg_split('/[\s\-_\/\\\\]+/', Str::ascii($empresa->razon_social)),
            fn($p) => $p !== ''
        );
        $razonSocial = implode('_', array_map(
            fn($p) => strtoupper(preg_replace('/[^a-z0-9]/i', '', $p)),
            $palabras
        ));
        $razonSocial = substr($razonSocial, 0, 50); // Limitar longitud para evitar problemas de ruta larga

        $timestamp = now()->format('Ymd_His');
        $filename = "{$razonSocial}_{$ruc}_{$periodo}_ESTADO_CUENTA_{$anio}_{$mes}_{$timestamp}.PDF";

        $subcarpeta = "estados_cuenta/{$estado->id_cuenta_empresa}/{$estado->anio}";
        $dir = public_path($subcarpeta);

        if (!file_exists($dir)) {
            mkdir($dir, 0775, true);
        }

        if ($estado->archivo_pdf && file_exists(public_path($estado->archivo_pdf))) {
            @unlink(public_path($estado->archivo_pdf));
        }
        $file->move($dir, $filename);
        return "{$subcarpeta}/{$filename}";
    }
}

