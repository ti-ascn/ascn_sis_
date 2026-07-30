<?php

namespace App\Http\Controllers;

use App\Models\EstatusLibroContable;
use App\Models\Libro;
use App\Models\ValorUit;
use App\Models\EmpresasClientes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PDF;

class EstatusLibroContableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $estatusLibros = EstatusLibroContable::with('empresaCliente')->orderBy('id_estatus_libros', 'desc')->get();

        $empresas = EmpresasClientes::select('id_empresas_clientes', 'razon_social', 'ruc')
            ->whereIn('estado_actual', [1, 2])
            ->orderBy('razon_social')
            ->get();

        $libros = Libro::orderBy('nombre')->get();
        $valoresUit = ValorUit::orderBy('anio', 'desc')->get();
        $anios = $valoresUit->pluck('anio')->toArray();

        return view('estatus_libros.index', compact('estatusLibros', 'empresas', 'libros', 'valoresUit', 'anios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $userArea = Auth::user()->area->nombre ?? '';
        $isAdminOrGerente = in_array($userArea, ['ADMINISTRACION', 'GERENCIA']);
        $isContabilidad = ($userArea === 'CONTABILIDAD');

        // Solo Contabilidad, Administración o Gerencia pueden iniciar/crear registros
        if (!$isAdminOrGerente && !$isContabilidad) {
            return response()->json(['error' => 'No tiene permisos para iniciar un registro de estatus de libros.'], 403);
        }

        $rules = [
            'id_empresa_cliente' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'tipo_regimen' => 'required|in:mype,general,especial',
            'monto' => 'required_unless:tipo_regimen,especial|nullable|numeric|min:0',
            'anio' => ['required', 'integer', Rule::unique('estatus_libros_contables', 'anio')->where('id_empresa_cliente', $request->id_empresa_cliente)],
            'anio_obligacion' => 'required|integer',
            'id_libros' => 'nullable|array',
            'id_libros.*' => 'exists:libros,id_libro',
            'libros_obligados' => 'nullable|array',
        ];

        if ($isAdminOrGerente || $isContabilidad) {
            $rules['detalles_libros'] = 'nullable|array';
        }

        $validated = $request->validate($rules);

        // Pre-fill default values for detalles_libros if missing
        $detalles = $request->detalles_libros ?? [];
        foreach ($request->id_libros ?? [] as $libroId) {
            if (!isset($detalles[$libroId])) {
                $detalles[$libroId] = [];
            }
            $detalles[$libroId]['is_electronico'] = isset($detalles[$libroId]['is_electronico']) && $detalles[$libroId]['is_electronico'] ? true : false;
            $detalles[$libroId]['entregado_por_cliente'] = $this->convertirAlSiNo($detalles[$libroId]['entregado_por_cliente'] ?? 'no');
            $detalles[$libroId]['estatus_legalizacion'] = $detalles[$libroId]['estatus_legalizacion'] ?? 'n/a';
            $detalles[$libroId]['responsable_legalizacion'] = $detalles[$libroId]['responsable_legalizacion'] ?? '-';
            $detalles[$libroId]['entregado_al_cliente'] = $this->convertirAlSiNo($detalles[$libroId]['entregado_al_cliente'] ?? 'no');
        }

        $data = [
            'id_empresa_cliente' => $request->id_empresa_cliente,
            'tipo_regimen' => $request->tipo_regimen,
            'monto' => $request->tipo_regimen === 'especial' ? null : $request->monto,
            'anio' => $request->anio,
            'anio_obligacion' => $request->anio_obligacion,
            'id_libros' => $request->id_libros ?? [],
            'libros_obligados' => $request->libros_obligados ?? [],
            'detalles_libros' => $detalles,
            'updated_at_contabilidad' => now(),
            'updated_by_contabilidad' => Auth::user()->name ?? 'Desconocido',
        ];

        EstatusLibroContable::create($data);

        return redirect()->route('estatus_libros_contables.index')->with('success', 'Estatus de libros registrado correctamente.');
    }

    /**
     * Show a single resource as JSON (for AJAX edit loading).
     */
    public function show($id)
    {
        $estatusLibro = EstatusLibroContable::with('empresaCliente')->findOrFail($id);

        // Append empresa_nombre for the Detalles modal
        $data = $estatusLibro->toArray();
        $data['empresa_nombre'] = $estatusLibro->empresaCliente->razon_social ?? '—';

        return response()->json($data);
    }

    /**Convertir valores '1'/'0' a 'si'/'no' para compatibilidad*/
    private function convertirAlSiNo($valor)
    {
        if ($valor === '1' || $valor === 1 || $valor === true) {
            return 'si';
        }
        if ($valor === '0' || $valor === 0 || $valor === false) {
            return 'no';
        }
        return $valor;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $estatusLibro = EstatusLibroContable::findOrFail($id);

        $userArea = Auth::user()->area->nombre ?? '';
        $isAdminOrGerente = in_array($userArea, ['ADMINISTRACION', 'GERENCIA']);
        $isLegal = ($userArea === 'LEGAL');

        $data = [];

        // Validaciones condicionales por área
        $rules = [];

        if ($isAdminOrGerente || $userArea === 'CONTABILIDAD') {
            $rules = array_merge($rules, [
                'tipo_regimen' => 'required|in:mype,general,especial',
                'monto' => 'required_unless:tipo_regimen,especial|nullable|numeric|min:0',
                'anio' => ['required', 'integer', Rule::unique('estatus_libros_contables', 'anio')->where('id_empresa_cliente', $estatusLibro->id_empresa_cliente)->ignore($estatusLibro->id_estatus_libros, 'id_estatus_libros')],
                'id_libros' => 'nullable|array',
                'id_libros.*' => 'exists:libros,id_libro',
                'libros_obligados' => 'nullable|array',
                'detalles_libros' => 'nullable|array',
            ]);
        }

        if ($isAdminOrGerente || $userArea === 'CONTABILIDAD') {
            $rules['detalles_libros'] = 'nullable|array';
        }

        if ($isLegal) {
            $rules['detalles_libros'] = 'nullable|array';
        }

        $request->validate($rules);

        $contabilidadChanged = false;
        if ($isAdminOrGerente || $userArea === 'CONTABILIDAD') {
            $data['tipo_regimen'] = $request->tipo_regimen;
            $data['monto'] = $request->tipo_regimen === 'especial' ? null : $request->monto;
            $data['anio'] = $request->anio;
            $data['anio_obligacion'] = $request->anio_obligacion;
            $data['id_libros'] = $request->id_libros ?? [];
            $data['libros_obligados'] = $request->libros_obligados ?? [];

            $detalles = $request->detalles_libros ?? [];
            $oldDetalles = $estatusLibro->detalles_libros ?? [];

            foreach ($data['id_libros'] as $libroId) {
                if (!isset($detalles[$libroId])) {
                    $detalles[$libroId] = [];
                }
                $detalles[$libroId]['is_electronico'] = isset($detalles[$libroId]['is_electronico']) && $detalles[$libroId]['is_electronico'] ? true : false;

                // Convertir '1'/'0' a 'si'/'no'
                $detalles[$libroId]['entregado_por_cliente'] = $this->convertirAlSiNo($detalles[$libroId]['entregado_por_cliente'] ?? ($oldDetalles[$libroId]['entregado_por_cliente'] ?? 'no'));
                $detalles[$libroId]['estatus_legalizacion'] = $detalles[$libroId]['estatus_legalizacion'] ?? ($oldDetalles[$libroId]['estatus_legalizacion'] ?? 'n/a');
                $detalles[$libroId]['responsable_legalizacion'] = $detalles[$libroId]['responsable_legalizacion'] ?? ($oldDetalles[$libroId]['responsable_legalizacion'] ?? '-');
                $detalles[$libroId]['entregado_al_cliente'] = $this->convertirAlSiNo($detalles[$libroId]['entregado_al_cliente'] ?? ($oldDetalles[$libroId]['entregado_al_cliente'] ?? 'no'));

                // Enforce that if estatus_legalizacion is "si" in DB, it cannot be changed back
                if (($oldDetalles[$libroId]['estatus_legalizacion'] ?? '') === 'si') {
                    $detalles[$libroId]['estatus_legalizacion'] = 'si';
                }
            }

            $data['detalles_libros'] = $detalles;

            // Comparar
            $oldBooks = $estatusLibro->id_libros ?? [];
            $newBooks = $data['id_libros'];
            $booksChanged = (count($oldBooks) !== count($newBooks)) || count(array_diff($oldBooks, $newBooks)) > 0;

            $oldObligados = $estatusLibro->libros_obligados ?? [];
            $newObligados = $data['libros_obligados'];
            $obligadosChanged = (count($oldObligados) !== count($newObligados)) || count(array_diff($oldObligados, $newObligados)) > 0;

            if (
                $estatusLibro->tipo_regimen !== $data['tipo_regimen'] ||
                $estatusLibro->monto != $data['monto'] ||
                $estatusLibro->anio != $data['anio'] ||
                $booksChanged ||
                $obligadosChanged ||
                json_encode($estatusLibro->detalles_libros ?? []) !== json_encode($data['detalles_libros'])
            ) {
                $contabilidadChanged = true;
            }

            if ($contabilidadChanged) {
                $data['updated_at_contabilidad'] = now();
                $data['updated_by_contabilidad'] = Auth::user()->name ?? 'Desconocido';
            }
        }

        // ── Área LEGAL: puede actualizar estatus_legalizacion, responsable_legalizacion y los id_libros de libros del área 'legal' ──
        if ($isLegal) {
            $oldDetalles = $estatusLibro->detalles_libros ?? [];
            $incomingDetalles = $request->detalles_libros ?? [];
            $mergedDetalles = $oldDetalles;

            foreach ($incomingDetalles as $libroId => $campos) {
                $existente = $oldDetalles[$libroId] ?? [];

                // Nunca revertir legalización si ya fue aprobada
                $oldEstatusLeg = $existente['estatus_legalizacion'] ?? 'n/a';
                $newEstatusLeg = $campos['estatus_legalizacion'] ?? $oldEstatusLeg;
                if ($oldEstatusLeg === 'si') {
                    $newEstatusLeg = 'si';
                }

                $mergedDetalles[$libroId] = array_merge($existente, [
                    'is_electronico'           => isset($campos['is_electronico']) ? (bool)$campos['is_electronico'] : ($existente['is_electronico'] ?? false),
                    'entregado_por_cliente'    => $this->convertirAlSiNo($campos['entregado_por_cliente'] ?? ($existente['entregado_por_cliente'] ?? 'no')),
                    'entregado_al_cliente'     => $this->convertirAlSiNo($campos['entregado_al_cliente'] ?? ($existente['entregado_al_cliente'] ?? 'no')),
                    'estatus_legalizacion'     => $newEstatusLeg,
                    'responsable_legalizacion' => $campos['responsable_legalizacion'] ?? ($existente['responsable_legalizacion'] ?? '-'),
                ]);
            }

            $data['detalles_libros'] = $mergedDetalles;

            // Actualizar id_libros: preservar libros de otras áreas, reemplazar los de área 'legal'
            // con los enviados por el usuario LEGAL
            if ($request->has('id_libros')) {
                $data['id_libros'] = array_values(array_unique(array_map('intval', $request->id_libros ?? [])));
            }

            $data['updated_at_legal'] = now();
            $data['updated_by_legal'] = Auth::user()->name ?? 'Desconocido';
        }

        $estatusLibro->update($data);

        return redirect()->route('estatus_libros_contables.index')->with('success', 'Estatus de libros actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $estatusLibro = EstatusLibroContable::findOrFail($id);

        // Eliminar archivo físico si existe
        if ($estatusLibro->archivo_recepcion && file_exists(public_path($estatusLibro->archivo_recepcion))) {
            unlink(public_path($estatusLibro->archivo_recepcion));
        }

        if ($estatusLibro->archivo_entrega && file_exists(public_path($estatusLibro->archivo_entrega))) {
            unlink(public_path($estatusLibro->archivo_entrega));
        }

        $estatusLibro->delete();

        return redirect()->route('estatus_libros_contables.index')->with('success', 'Estatus de libros eliminado correctamente.');
    }

    /**
     * Fetch default books and calculate UITs.
     */
    public function getDefaultBooks(Request $request)
    {
        $request->validate([
            'tipo_regimen' => 'required|in:mype,general,especial',
            'monto' => 'required_unless:tipo_regimen,especial|nullable|numeric|min:0',
            'anio' => 'required|integer',
            'id_empresa' => 'nullable|exists:empresas_clientes,id_empresas_clientes',
        ]);

        $regimen = $request->tipo_regimen;
        $monto = (float) $request->monto;
        $anio = (int) $request->anio;
        $idEmpresa = $request->id_empresa;

        $todosLibros = Libro::orderBy('nombre')->get();
        $librosLegalObligados = collect();

        if ($idEmpresa) {
            $empresa = EmpresasClientes::find($idEmpresa);
            if ($empresa) {
                $razonSocial = strtoupper($empresa->razon_social);

                $isSAC = preg_match('/\bS\.?A\.?C\.?\b$/i', trim($razonSocial));
                $isEIRL = preg_match('/\bE\.?I\.?R\.?L\.?\b$/i', trim($razonSocial));

                if ($isSAC) {
                    $librosLegalObligados = Libro::where('area', 'legal')
                        ->where(function ($q) {
                            $q->where('nombre', 'like', '%Libro de Actas%')
                                ->orWhere('nombre', 'like', '%Libro de Matrículas de Acciones%')
                                ->orWhere('nombre', 'like', '%Libro de Matricula de Acciones%');
                        })->get();
                } elseif ($isEIRL) {
                    $librosLegalObligados = Libro::where('area', 'legal')
                        ->where('nombre', 'like', '%Libro de Actas%')->get();
                }
            }
        }

        if ($regimen === 'especial') {
            // "cuando el régimen es especial se seleccionan solo los libros de tipo 'especial'"
            $librosObligatorios = Libro::whereRaw('LOWER(tipo_regimen) = ?', ['especial'])
                ->where(function ($q) {
                    $q->whereRaw('LOWER(area) != ?', ['legal']);
                })
                ->get();

            $librosObligatorios = $librosObligatorios->merge($librosLegalObligados)->unique('id_libro');

            return response()->json([
                'success' => true,
                'valor_uit' => null,
                'uits' => null,
                'tramo' => 'Especial',
                'libros_obligados' => $librosObligatorios->values(),
                'todos_libros' => $todosLibros
            ]);
        }

        // Buscar el valor de la UIT para el año
        $uit = ValorUit::where('anio', $anio)->first();
        if (!$uit) {
            // Usar el más reciente si no existe
            $uit = ValorUit::orderBy('anio', 'desc')->first();
        }

        $valorUit = $uit ? (float) $uit->valor : 5500.00;
        $uitsCount = $monto / $valorUit;

        // Determinar tramo
        $tramoNum = 1;
        if ($regimen === 'mype') {
            if ($uitsCount <= 300) {
                $tramoNum = 1;
            } elseif ($uitsCount <= 500) {
                $tramoNum = 2;
            } else {
                $tramoNum = 3;
            }
        } else { // general
            if ($uitsCount <= 300) {
                $tramoNum = 1;
            } elseif ($uitsCount <= 500) {
                $tramoNum = 2;
            } elseif ($uitsCount <= 1700) {
                $tramoNum = 3;
            } else {
                $tramoNum = 4;
            }
        }

        // Obtener libros por defecto según régimen y tramo
        $librosObligatorios = Libro::whereRaw('LOWER(tipo_regimen) = ?', [strtolower($regimen)])
            ->whereRaw('LOWER(tramo) = ?', [strtolower((string) $tramoNum)])
            ->get();

        $librosObligatorios = $librosObligatorios->merge($librosLegalObligados)->unique('id_libro');

        return response()->json([
            'success' => true,
            'valor_uit' => $valorUit,
            'uits' => round($uitsCount, 0),
            'tramo' => "Tramo {$tramoNum}",
            'libros_obligados' => $librosObligatorios,
            'todos_libros' => $todosLibros
        ]);
    }

    /** Generar PDF de libros pendientes por entregar. */
    public function pdfPendientes($id)
    {
        $estatusLibro = EstatusLibroContable::with('empresaCliente')->findOrFail($id);

        $detalles = $estatusLibro->detalles_libros ?? [];
        $libros = $estatusLibro->libros;

        $librosPendientes = $libros->filter(function ($libro) use ($detalles) {
            $detalle = $detalles[$libro->id_libro] ?? [];
            return ($detalle['entregado_por_cliente'] ?? 'no') !== 'si';
        })->map(function ($libro) use ($detalles) {
            $detalle = $detalles[$libro->id_libro] ?? [];
            return [
                'nombre' => $libro->nombre,
                'is_electronico' => !empty($detalle['is_electronico']),
            ];
        })->values();

        $pdf = PDF::loadView('pdf.cargo_libros', [
            'tipo' => 'pendientes',
            'empresa' => $estatusLibro->empresaCliente,
            'libros' => $librosPendientes,
            'fecha' => now(),
            'nro_cargo' => '',
        ]);

        return $pdf->stream('libros_pendientes_' . $estatusLibro->empresaCliente->ruc . '.pdf');
    }

    /**
     * Store a new book dynamically.
     */
    public function storeLibroAjax(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo_regimen' => 'required|string|max:255',
            'tramo' => 'required|string|max:255',
            'area' => 'required|string|max:255',
        ]);

        $libro = Libro::create($request->all());

        return response()->json([
            'success' => true,
            'libro' => $libro,
        ]);
    }
}
