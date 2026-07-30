<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contrato;
use App\Models\EmpresasClientes;
use App\Models\ClienteAuditoria;
use App\Exports\ContratosExport;

class ContratoController extends Controller
{
    // Mostrar formulario (index)    
    public function index()
    {
        $contratos = Contrato::with(['empresaOrigen', 'empresaDestino', 'clienteAuditoriaDestino'])
            ->where('estado_actual', 1)
            ->get();
        $agrupado = [];
        foreach ($contratos as $contrato) {
            if (!$contrato->empresaOrigen) {
                continue;
            }
            $origenId = $contrato->id_empresa_origen;
            if (!isset($agrupado[$origenId])) {
                $agrupado[$origenId] = [
                    'origen' => $contrato->empresaOrigen,
                    'destinos' => [],
                    'clientes' => [],
                    'dest_ids' => [], // Seguimiento de destinos únicos para este origen
                    'cli_ids' => [],  // Seguimiento de clientes únicos para este origen
                ];
            }

            // Procesar Empresa Destino
            if ($contrato->empresaDestino) {
                $dId = $contrato->id_empresa_destino;
                if (!in_array($dId, $agrupado[$origenId]['dest_ids'])) {
                    $obj = clone $contrato->empresaDestino;
                    $obj->fecha_inicio = $contrato->fecha_inicio;
                    $obj->fecha_fin = $contrato->fecha_fin;
                    $obj->id_contrato = $contrato->id_contratos;
                    $obj->firmado = $contrato->firmado;
                    $agrupado[$origenId]['destinos'][] = $obj;
                    $agrupado[$origenId]['dest_ids'][] = $dId;
                }
            }

            // Procesar Cliente Auditoría
            if ($contrato->clienteAuditoriaDestino) {
                $cId = $contrato->id_cliente_auditoria_destino;
                if (!in_array($cId, $agrupado[$origenId]['cli_ids'])) {
                    $obj = clone $contrato->clienteAuditoriaDestino;
                    $obj->fecha_inicio = $contrato->fecha_inicio;
                    $obj->fecha_fin = $contrato->fecha_fin;
                    $obj->id_contrato = $contrato->id_contratos;
                    $obj->firmado = $contrato->firmado;
                    $agrupado[$origenId]['clientes'][] = $obj;
                    $agrupado[$origenId]['cli_ids'][] = $cId;
                }
            }
        }

        // Listas planas para el modal de relaciones
        $todosDestinos = [];
        $todosClientes = [];
        foreach ($agrupado as $grupo) {
            foreach ($grupo['destinos'] as $d) {
                $todosDestinos[$d->id_empresas_clientes] = $d;
            }
            foreach ($grupo['clientes'] as $c) {
                $todosClientes[$c->id_cliente] = $c;
            }
        }
        $todosDestinos = collect($todosDestinos)->sortBy('razon_social')->values();
        $todosClientes = collect($todosClientes)->sortBy('razon_social')->values();
        return view('contratos.index', compact('agrupado', 'todosDestinos', 'todosClientes'));
    }
    public function export(Request $request, $tipo)
    {
        if ($tipo == 'activos') {
            $contratosActivos = Contrato::with(['empresaOrigen', 'empresaDestino', 'clienteAuditoriaDestino'])->where('estado_actual', 1)->get();
            $contratosActivos = $contratosActivos->sortBy(function ($contrato) {
                $razonSocialDestino = $contrato->empresaDestino
                    ? $contrato->empresaDestino->razon_social
                    : ($contrato->clienteAuditoriaDestino ? $contrato->clienteAuditoriaDestino->razon_social : '');
                return strtoupper(implode('', array_map(function ($word) {
                    return strtoupper(substr($word, 0, 1));
                }, explode(' ', $razonSocialDestino))));
            });
            $fileName = 'CONTRATOS_ACTIVOS.xlsx';
        } else {
            return redirect()->back()->with('error', 'Tipo de exportación no válido');
        }
        $data = $this->getContratosFilaData($tipo, $contratos ?? $contratosActivos);
        return \Maatwebsite\Excel\Facades\Excel::download(new ContratosExport($data['filas']), $fileName);
    }
    public function getContratosFilaData($tipo, $contratos)
    {
        $filas = [];
        foreach ($contratos as $contrato) {
            $empresaOrigen = $contrato->empresaOrigen;
            $empresaDestino = $contrato->empresaDestino;
            $clienteAuditoriaDestino = $contrato->clienteAuditoriaDestino;

            $rucEmpresaOrigen = $empresaOrigen ? $empresaOrigen->ruc : '';
            $razonSocialEmpresaOrigen = $empresaOrigen ? $empresaOrigen->razon_social : '';

            $rucEmpresaDestino = $empresaDestino ? $empresaDestino->ruc : '';
            $razonSocialEmpresaDestino = $empresaDestino ? $empresaDestino->razon_social : '';

            $rucClienteAuditoria = $clienteAuditoriaDestino ? $clienteAuditoriaDestino->ruc : '';
            $razonSocialClienteAuditoria = $clienteAuditoriaDestino ? $clienteAuditoriaDestino->razon_social : '';

            $fechaInicio = $contrato->fecha_inicio ? \Carbon\Carbon::parse($contrato->fecha_inicio)->format('Y-m-d') : '';
            $fechaFin = $contrato->fecha_fin ? \Carbon\Carbon::parse($contrato->fecha_fin)->format('Y-m-d') : '';

            $filas[] = (object) [
                'ruc_empresa_origen' => $rucEmpresaOrigen,
                'razon_social_empresa_origen' => $razonSocialEmpresaOrigen,
                'ruc_empresa_destino' => $rucEmpresaDestino,
                'razon_social_empresa_destino' => $razonSocialEmpresaDestino,
                'ruc_cliente_auditoria' => $rucClienteAuditoria,
                'razon_social_cliente_auditoria' => $razonSocialClienteAuditoria,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'firmado' => $contrato->firmado ? 'SI' : 'NO',
            ];
        }
        return ['filas' => $filas];
    }

    public function store(Request $request)
    {
        // Filtrar bloques vacíos
        $request->merge([
            'destinos' => collect($request->destinos ?? [])
                ->filter(fn($d) => !empty($d['id_empresa_destino']))
                ->values()
                ->all(),
            'clientes' => collect($request->clientes ?? [])
                ->filter(fn($c) => !empty($c['id_cliente_auditoria_destino']))
                ->values()
                ->all(),
        ]);
        $validated = $request->validate([
            'id_empresa_origen' => 'required|exists:empresas_clientes,id_empresas_clientes',
            // Empresas destino
            'destinos' => 'nullable|array',
            'destinos.*.id_empresa_destino' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'destinos.*.fecha_inicio' => 'required|date',
            'destinos.*.fecha_fin' => 'required|date|after_or_equal:destinos.*.fecha_inicio',
            'destinos.*.firmado' => 'nullable',
            // Clientes auditoría
            'clientes' => 'nullable|array',
            'clientes.*.id_cliente_auditoria_destino' => 'required|exists:clientes_auditoria,id_cliente',
            'clientes.*.fecha_inicio' => 'required|date',
            'clientes.*.fecha_fin' => 'required|date|after_or_equal:clientes.*.fecha_inicio',
            'clientes.*.firmado' => 'nullable',
        ]);
        if (empty($request->destinos) && empty($request->clientes)) {
            return back()
                ->withErrors(['general' => 'Debe registrar al menos una empresa destino o un cliente de auditoría.'])
                ->withInput();
        }
        $origen = $request->id_empresa_origen;
        // ================= DESTINOS =================
        foreach ($request->destinos ?? [] as $dest) {

            $existente = Contrato::where('id_empresa_origen', $origen)
                ->where('id_empresa_destino', $dest['id_empresa_destino'])
                ->where('estado_actual', 1)
                ->first();

            if ($existente) {
                // 1️⃣ Solo cambió fecha → actualizar
                if (
                    $existente->fecha_inicio !== $dest['fecha_inicio'] ||
                    $existente->fecha_fin !== $dest['fecha_fin'] ||
                    $existente->firmado != ($dest['firmado'] ?? 0)
                ) {
                    $existente->update([
                        'fecha_inicio' => $dest['fecha_inicio'],
                        'fecha_fin' => $dest['fecha_fin'],
                        'firmado' => $dest['firmado'] ?? 0,
                    ]);
                }

                // Nada más que hacer
                continue;
            }

            // 2️⃣ No existe activo → crear nuevo
            Contrato::create([
                'id_empresa_origen' => $origen,
                'id_empresa_destino' => $dest['id_empresa_destino'],
                'id_cliente_auditoria_destino' => null,
                'fecha_inicio' => $dest['fecha_inicio'],
                'fecha_fin' => $dest['fecha_fin'],
                'estado_actual' => 1,
                'fecha_cancelacion' => null,
                'firmado' => $dest['firmado'] ?? 0,
            ]);
        }

        // ================= CLIENTES =================
        foreach ($request->clientes ?? [] as $cli) {

            $existente = Contrato::where('id_empresa_origen', $origen)
                ->where('id_cliente_auditoria_destino', $cli['id_cliente_auditoria_destino'])
                ->where('estado_actual', 1)
                ->first();

            if ($existente) {
                // 1️⃣ Solo cambió fecha → actualizar
                if (
                    $existente->fecha_inicio !== $cli['fecha_inicio'] ||
                    $existente->fecha_fin !== $cli['fecha_fin'] ||
                    $existente->firmado != ($cli['firmado'] ?? 0)
                ) {
                    $existente->update([
                        'fecha_inicio' => $cli['fecha_inicio'],
                        'fecha_fin' => $cli['fecha_fin'],
                        'firmado' => $cli['firmado'] ?? 0,
                    ]);
                }

                continue;
            }

            // 2️⃣ No existe activo → crear nuevo
            Contrato::create([
                'id_empresa_origen' => $origen,
                'id_empresa_destino' => null,
                'id_cliente_auditoria_destino' => $cli['id_cliente_auditoria_destino'],
                'fecha_inicio' => $cli['fecha_inicio'],
                'fecha_fin' => $cli['fecha_fin'],
                'estado_actual' => 1,
                'fecha_cancelacion' => null,
                'firmado' => $cli['firmado'] ?? 0,
            ]);
        }


        return redirect()->route('contratos.index')
            ->with('success', 'Relaciones creadas correctamente.');
    }


    public function create()
    {
        // Origen → solo A1 activos o suspendidos
        $empresasOrigen = EmpresasClientes::where('estado', 'A1')
            ->whereIn('estado_actual', [1, 2])
            ->orderBy('razon_social')
            ->get();
        // Destino → solo C1 activos o suspendidos
        $empresasDestino = EmpresasClientes::where('estado', 'C1')
            ->whereIn('estado_actual', [1, 2])
            ->orderBy('razon_social')
            ->get();
        $clientes = ClienteAuditoria::where('estado_actual', 1)
            ->orderBy('razon_social')
            ->get();
        $relaciones = Contrato::where('estado_actual', 1)
            ->select(
                'id_empresa_origen',
                'id_empresa_destino',
                'id_cliente_auditoria_destino'
            )
            ->get();
        return view('contratos.create', compact(
            'empresasOrigen',
            'empresasDestino',
            'clientes',
            'relaciones'
        ));
    }

    public function edit($id_origen)
    {
        $origen = EmpresasClientes::where('id_empresas_clientes', $id_origen)
            ->where('estado', 'A1')
            ->whereIn('estado_actual', [1, 2])
            ->firstOrFail();
        $empresasOrigen = EmpresasClientes::where('estado', 'A1')
            ->whereIn('estado_actual', [1, 2])
            ->orderBy('razon_social')
            ->get();
        $empresasDestino = EmpresasClientes::where('estado', 'C1')
            ->whereIn('estado_actual', [1, 2])
            ->orderBy('razon_social')
            ->get();
        $clientes = ClienteAuditoria::where('estado_actual', 1)
            ->orderBy('razon_social')
            ->get();
        // DESTINOS COMPLETOS (con fechas)
        $destinos = Contrato::where('id_empresa_origen', $id_origen)
            ->whereNotNull('id_empresa_destino')
            ->where('estado_actual', 1)
            ->get();
        // CLIENTES COMPLETOS (con fechas)
        $clientesAuditoria = Contrato::where('id_empresa_origen', $id_origen)
            ->whereNotNull('id_cliente_auditoria_destino')
            ->where('estado_actual', 1)
            ->get();
        return view('contratos.edit', compact(
            'origen',
            'empresasOrigen',
            'empresasDestino',
            'clientes',
            'destinos',
            'clientesAuditoria'
        ));
    }

    public function update(Request $request, $id_origen)
    {
        // Filtrar bloques vacíos
        $request->merge([
            'destinos' => collect($request->destinos ?? [])
                ->filter(fn($d) => !empty($d['id_empresa_destino']))
                ->values()
                ->all(),
            'clientes' => collect($request->clientes ?? [])
                ->filter(fn($c) => !empty($c['id_cliente_auditoria_destino']))
                ->values()
                ->all(),
        ]);
        $validated = $request->validate([
            'id_empresa_origen' => 'required|exists:empresas_clientes,id_empresas_clientes',
            // Empresas destino
            'destinos' => 'nullable|array',
            'destinos.*.id_empresa_destino' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'destinos.*.fecha_inicio' => 'required|date',
            'destinos.*.fecha_fin' => 'required|date|after_or_equal:destinos.*.fecha_inicio',
            'destinos.*.firmado' => 'nullable',
            // Clientes auditoría
            'clientes' => 'nullable|array',
            'clientes.*.id_cliente_auditoria_destino' => 'required|exists:clientes_auditoria,id_cliente',
            'clientes.*.fecha_inicio' => 'required|date',
            'clientes.*.fecha_fin' => 'required|date|after_or_equal:clientes.*.fecha_inicio',
            'clientes.*.firmado' => 'nullable',
        ]);
        // Regla: al menos uno debe venir
        if (empty($request->destinos) && empty($request->clientes)) {
            return back()
                ->withErrors(['general' => 'Debe registrar al menos una empresa destino o un cliente de auditoría.'])
                ->withInput();
        }
        /* ========= DESTINOS ========= */
        $destinosNuevos = collect($request->destinos ?? [])
            ->pluck('id_empresa_destino')
            ->toArray();
        $destinosActuales = Contrato::where('id_empresa_origen', $id_origen)
            ->whereNotNull('id_empresa_destino')
            ->where('estado_actual', 1)
            ->pluck('id_empresa_destino')
            ->toArray();
        // Desactivar quitados
        $destinosQuitados = array_diff($destinosActuales, $destinosNuevos);
        if (!empty($destinosQuitados)) {
            Contrato::where('id_empresa_origen', $id_origen)
                ->whereIn('id_empresa_destino', $destinosQuitados)
                ->update([
                    'estado_actual' => 0,
                    'fecha_cancelacion' => now(),
                ]);
        }
        // Crear o actualizar
        foreach ($request->destinos ?? [] as $dest) {

            $existente = Contrato::where('id_empresa_origen', $id_origen)
                ->where('id_empresa_destino', $dest['id_empresa_destino'])
                ->where('estado_actual', 1)
                ->first();

            if ($existente) {
                // 1️⃣ Solo cambió fecha → actualizar
                if (
                    $existente->fecha_inicio !== $dest['fecha_inicio'] ||
                    $existente->fecha_fin !== $dest['fecha_fin'] ||
                    $existente->firmado != ($dest['firmado'] ?? 0)
                ) {
                    $existente->update([
                        'fecha_inicio' => $dest['fecha_inicio'],
                        'fecha_fin' => $dest['fecha_fin'],
                        'firmado' => $dest['firmado'] ?? 0,
                    ]);
                }

                // Nada más que hacer
                continue;
            }

            // 2️⃣ No existe activo → crear nuevo
            Contrato::create([
                'id_empresa_origen' => $id_origen,
                'id_empresa_destino' => $dest['id_empresa_destino'],
                'id_cliente_auditoria_destino' => null,
                'fecha_inicio' => $dest['fecha_inicio'],
                'fecha_fin' => $dest['fecha_fin'],
                'estado_actual' => 1,
                'fecha_cancelacion' => null,
                'firmado' => $dest['firmado'] ?? 0,
            ]);
        }

        /* ========= CLIENTES ========= */
        $clientesNuevos = collect($request->clientes ?? [])
            ->pluck('id_cliente_auditoria_destino')
            ->toArray();
        $clientesActuales = Contrato::where('id_empresa_origen', $id_origen)
            ->whereNotNull('id_cliente_auditoria_destino')
            ->where('estado_actual', 1)
            ->pluck('id_cliente_auditoria_destino')
            ->toArray();
        // Desactivar quitados
        $clientesQuitados = array_diff($clientesActuales, $clientesNuevos);
        if (!empty($clientesQuitados)) {
            Contrato::where('id_empresa_origen', $id_origen)
                ->whereIn('id_cliente_auditoria_destino', $clientesQuitados)
                ->update([
                    'estado_actual' => 0,
                    'fecha_cancelacion' => now(),
                ]);
        }
        // Crear o actualizar
        foreach ($request->clientes ?? [] as $cli) {

            $existente = Contrato::where('id_empresa_origen', $id_origen)
                ->where('id_cliente_auditoria_destino', $cli['id_cliente_auditoria_destino'])
                ->where('estado_actual', 1)
                ->first();

            if ($existente) {
                // Actualizar si cambió fecha o el estado de firmado
                if (
                    $existente->fecha_inicio !== $cli['fecha_inicio'] ||
                    $existente->fecha_fin !== $cli['fecha_fin'] ||
                    $existente->firmado != ($cli['firmado'] ?? 0)
                ) {
                    $existente->update([
                        'fecha_inicio' => $cli['fecha_inicio'],
                        'fecha_fin' => $cli['fecha_fin'],
                        'firmado' => $cli['firmado'] ?? 0,
                    ]);
                }
                continue;
            }
            // 2️⃣ No existe activo → crear nuevo
            Contrato::create([
                'id_empresa_origen' => $id_origen,
                'id_empresa_destino' => null,
                'id_cliente_auditoria_destino' => $cli['id_cliente_auditoria_destino'],
                'fecha_inicio' => $cli['fecha_inicio'],
                'fecha_fin' => $cli['fecha_fin'],
                'estado_actual' => 1,
                'fecha_cancelacion' => null,
                'firmado' => $cli['firmado'] ?? 0,
            ]);
        }


        return redirect()->route('contratos.index')
            ->with('success', 'Relaciones actualizadas correctamente.');
    }

    public function desactivar($id_origen)
    {
        // Desactivar todas las relaciones activas de ese origen
        Contrato::where('id_empresa_origen', $id_origen)
            ->where('estado_actual', 1)
            ->update([
                'estado_actual' => 0,
                'fecha_cancelacion' => now(),
            ]);
        return redirect()->route('contratos.index')
            ->with('success', 'Todas las relaciones fueron desactivadas correctamente.');
    }

    public function listar()
    {
        $empresas_clientes = EmpresasClientes::where('estado', 'A1')->get();
        // Agregar conteos
        foreach ($empresas_clientes as $empresa) {
            $empresa->total_activos = Contrato::where('id_empresa_origen', $empresa->id_empresas_clientes)
                ->where('estado_actual', 1)
                ->count();

            $empresa->total_inactivos = Contrato::where('id_empresa_origen', $empresa->id_empresas_clientes)
                ->where('estado_actual', 0)
                ->count();

            $empresa->total_contratos = Contrato::where('id_empresa_origen', $empresa->id_empresas_clientes)
                ->count();
        }
        return view('contratos.listar', compact('empresas_clientes'));
    }

    public function listarindividual($origen)
    {
        $empresa = EmpresasClientes::where('id_empresas_clientes', $origen)
            ->where('estado', 'A1')
            ->firstOrFail();

        // CONTRATOS ACTIVOS
        $contratosActivos = Contrato::with(['empresaDestino', 'clienteAuditoriaDestino'])
            ->where('id_empresa_origen', $origen)
            ->where('estado_actual', 1)
            ->get();

        // CONTRATOS INACTIVOS
        $contratosInactivos = Contrato::with(['empresaDestino', 'clienteAuditoriaDestino'])
            ->where('id_empresa_origen', $origen)
            ->where('estado_actual', 0)
            ->get();

        return view('contratos.listarindividual', compact(
            'empresa',
            'contratosActivos',
            'contratosInactivos'
        ));
    }

    public function inactivarIndividual($id)
    {
        $contrato = Contrato::findOrFail($id);

        $contrato->update([
            'estado_actual' => 0,
            'fecha_cancelacion' => now()
        ]);

        return response()->json([
            'success' => true
        ]);
    }

    public function desactivarConComentario(Request $request, $id)
    {
        $contrato = Contrato::findOrFail($id);
        $contrato->estado_actual = 0;
        $contrato->comentario = $request->input('comentario');
        $contrato->fecha_cancelacion = now()->toDateString();
        $contrato->save();

        return response()->json(['success' => true, 'message' => 'Contrato desactivado.']);
    }
    public function relacionContratos($type, $id)
    {
        $entity = null;
        $relaciones = [];
        if ($type === 'empresa') {
            $entity = EmpresasClientes::findOrFail($id);
            $relaciones = Contrato::with('empresaOrigen')
                ->where('id_empresa_destino', $id)
                ->where('estado_actual', 1)
                ->get();
        } elseif ($type === 'cliente') {
            $entity = ClienteAuditoria::findOrFail($id);
            $relaciones = Contrato::with('empresaOrigen')
                ->where('id_cliente_auditoria_destino', $id)
                ->where('estado_actual', 1)
                ->get();
        } else {
            abort(404);
        }
        return view('contratos.relacion', compact('entity', 'relaciones', 'type'));
    }

    // =========================================================================
    // FLUJO INVERSO: CONTRATOS POR DESTINO (Empresa Destino / Cliente Auditoría)
    // =========================================================================

    public function indexDestinos()
    {
        // Traer contratos activos que tengan destino (empresa o cliente)
        $contratos = Contrato::with(['empresaOrigen', 'empresaDestino', 'clienteAuditoriaDestino'])
            ->where(function ($q) {
                $q->whereNotNull('id_empresa_destino')
                  ->orWhereNotNull('id_cliente_auditoria_destino');
            })
            ->where('estado_actual', 1)
            ->get();
            
        $agrupado = [];
        
        foreach ($contratos as $contrato) {
            if (!$contrato->empresaOrigen) continue;

            $tipo = null;
            $destinoId = null;
            $destinoObj = null;

            if ($contrato->empresaDestino) {
                $tipo = 'empresa';
                $destinoId = $contrato->id_empresa_destino;
                $destinoObj = $contrato->empresaDestino;
            } elseif ($contrato->clienteAuditoriaDestino) {
                $tipo = 'cliente';
                $destinoId = $contrato->id_cliente_auditoria_destino;
                $destinoObj = $contrato->clienteAuditoriaDestino;
            } else {
                continue;
            }

            $key = $tipo . '_' . $destinoId;

            if (!isset($agrupado[$key])) {
                $agrupado[$key] = [
                    'tipo' => $tipo,
                    'destino' => $destinoObj,
                    'origenes' => [],
                    'origen_ids' => [],
                ];
            }

            $oId = $contrato->id_empresa_origen;
            if (!in_array($oId, $agrupado[$key]['origen_ids'])) {
                $obj = clone $contrato->empresaOrigen;
                $obj->fecha_inicio = $contrato->fecha_inicio;
                $obj->fecha_fin = $contrato->fecha_fin;
                $obj->id_contrato = $contrato->id_contratos;
                $obj->firmado = $contrato->firmado;
                $agrupado[$key]['origenes'][] = $obj;
                $agrupado[$key]['origen_ids'][] = $oId;
            }
        }

        return view('contratos.index_destinos', compact('agrupado'));
    }
    
    public function createDestinos($tipo)
    {
        $destinos = [];
        
        if ($tipo === 'empresa') {
            $destinos = EmpresasClientes::where('estado', 'C1')
                ->whereIn('estado_actual', [1, 2])
                ->orderBy('razon_social')
                ->get();
        } elseif ($tipo === 'cliente') {
            $destinos = ClienteAuditoria::where('estado_actual', 1)
                ->orderBy('razon_social')
                ->get();
        } else {
            abort(404);
        }
            
        // Origen → solo A1 activos o suspendidos
        $empresasOrigen = EmpresasClientes::where('estado', 'A1')
            ->whereIn('estado_actual', [1, 2])
            ->orderBy('razon_social')
            ->get();
            
        $relaciones = Contrato::where('estado_actual', 1)
            ->where(function ($q) use ($tipo) {
                if ($tipo === 'empresa') {
                    $q->whereNotNull('id_empresa_destino');
                } else {
                    $q->whereNotNull('id_cliente_auditoria_destino');
                }
            })
            ->select('id_empresa_origen', 'id_empresa_destino', 'id_cliente_auditoria_destino')
            ->get();
            
        return view('contratos.create_destinos', compact('destinos', 'empresasOrigen', 'relaciones', 'tipo'));
    }

    public function storeDestinos(Request $request)
    {
        $tipo = $request->input('tipo');
        
        // Filtrar bloques vacíos
        $request->merge([
            'origenes' => collect($request->origenes ?? [])
                ->filter(fn($o) => !empty($o['id_empresa_origen']))
                ->values()
                ->all(),
        ]);
        
        $rules = [
            'tipo' => 'required|in:empresa,cliente',
            'origenes' => 'required|array|min:1',
            'origenes.*.id_empresa_origen' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'origenes.*.fecha_inicio' => 'required|date',
            'origenes.*.fecha_fin' => 'required|date|after_or_equal:origenes.*.fecha_inicio',
            'origenes.*.firmado' => 'nullable',
        ];

        if ($tipo === 'empresa') {
            $rules['id_destino'] = 'required|exists:empresas_clientes,id_empresas_clientes';
        } else {
            $rules['id_destino'] = 'required|exists:clientes_auditoria,id_cliente';
        }

        $validated = $request->validate($rules, [
            'origenes.required' => 'Debe registrar al menos una empresa origen (ferretería).',
            'origenes.min' => 'Debe registrar al menos una empresa origen (ferretería).',
        ]);
        
        $destinoId = $request->id_destino;
        
        foreach ($request->origenes as $origen) {
            $query = Contrato::where('id_empresa_origen', $origen['id_empresa_origen'])
                ->where('estado_actual', 1);

            if ($tipo === 'empresa') {
                $query->where('id_empresa_destino', $destinoId);
            } else {
                $query->where('id_cliente_auditoria_destino', $destinoId);
            }

            $existente = $query->first();

            if ($existente) {
                // Solo cambió fecha o firmado → actualizar
                if ($existente->fecha_inicio !== $origen['fecha_inicio'] ||
                    $existente->fecha_fin !== $origen['fecha_fin'] ||
                    $existente->firmado != ($origen['firmado'] ?? 0)) {
                    $existente->update([
                        'fecha_inicio' => $origen['fecha_inicio'],
                        'fecha_fin' => $origen['fecha_fin'],
                        'firmado' => $origen['firmado'] ?? 0,
                    ]);
                }
                continue;
            }

            // No existe activo → crear nuevo
            Contrato::create([
                'id_empresa_origen' => $origen['id_empresa_origen'],
                'id_empresa_destino' => $tipo === 'empresa' ? $destinoId : null,
                'id_cliente_auditoria_destino' => $tipo === 'cliente' ? $destinoId : null,
                'fecha_inicio' => $origen['fecha_inicio'],
                'fecha_fin' => $origen['fecha_fin'],
                'firmado' => $origen['firmado'] ?? 0,
                'estado_actual' => 1,
                'fecha_cancelacion' => null,
            ]);
        }

        return redirect()->route('contratos.index_destinos')
            ->with('success', 'Contratos creados correctamente.');
    }

    public function editDestinos($tipo, $id)
    {
        $destinoObj = null;
        $destinos = [];
        
        if ($tipo === 'empresa') {
            $destinoObj = EmpresasClientes::where('id_empresas_clientes', $id)
                ->whereIn('estado_actual', [1, 2])
                ->firstOrFail();
            $destinos = EmpresasClientes::where('estado', 'C1')
                ->whereIn('estado_actual', [1, 2])
                ->orderBy('razon_social')
                ->get();
        } elseif ($tipo === 'cliente') {
            $destinoObj = ClienteAuditoria::where('id_cliente', $id)
                ->where('estado_actual', 1)
                ->firstOrFail();
            $destinos = ClienteAuditoria::where('estado_actual', 1)
                ->orderBy('razon_social')
                ->get();
        } else {
            abort(404);
        }
            
        $empresasOrigen = EmpresasClientes::where('estado', 'A1')
            ->whereIn('estado_actual', [1, 2])
            ->orderBy('razon_social')
            ->get();
            
        // Orígenes actuales
        $query = Contrato::where('estado_actual', 1);
        if ($tipo === 'empresa') {
            $query->where('id_empresa_destino', $id);
        } else {
            $query->where('id_cliente_auditoria_destino', $id);
        }
        $origenes_actuales = $query->get();
            
        return view('contratos.edit_destinos', compact(
            'destinoObj',
            'destinos',
            'empresasOrigen',
            'origenes_actuales',
            'tipo',
            'id'
        ));
    }

    public function updateDestinos(Request $request, $tipo, $id)
    {
        // Filtrar bloques vacíos
        $request->merge([
            'origenes' => collect($request->origenes ?? [])
                ->filter(fn($o) => !empty($o['id_empresa_origen']))
                ->values()
                ->all(),
        ]);
        
        $rules = [
            'origenes' => 'required|array|min:1',
            'origenes.*.id_empresa_origen' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'origenes.*.fecha_inicio' => 'required|date',
            'origenes.*.fecha_fin' => 'required|date|after_or_equal:origenes.*.fecha_inicio',
            'origenes.*.firmado' => 'nullable',
        ];

        $validated = $request->validate($rules, [
            'origenes.required' => 'Debe registrar al menos una empresa origen.',
            'origenes.min' => 'Debe registrar al menos una empresa origen.',
        ]);
        
        $origenesNuevos = collect($request->origenes)->pluck('id_empresa_origen')->toArray();
        
        $queryActuales = Contrato::where('estado_actual', 1);
        if ($tipo === 'empresa') {
            $queryActuales->where('id_empresa_destino', $id);
        } else {
            $queryActuales->where('id_cliente_auditoria_destino', $id);
        }
        
        $origenesActuales = $queryActuales->pluck('id_empresa_origen')->toArray();
            
        // Desactivar quitados
        $origenesQuitados = array_diff($origenesActuales, $origenesNuevos);
        if (!empty($origenesQuitados)) {
            $queryUpdate = Contrato::whereIn('id_empresa_origen', $origenesQuitados)
                ->where('estado_actual', 1);
            if ($tipo === 'empresa') {
                $queryUpdate->where('id_empresa_destino', $id);
            } else {
                $queryUpdate->where('id_cliente_auditoria_destino', $id);
            }
            $queryUpdate->update([
                'estado_actual' => 0,
                'fecha_cancelacion' => now(),
            ]);
        }
        
        // Crear o actualizar
        foreach ($request->origenes as $origen) {
            $queryExistente = Contrato::where('id_empresa_origen', $origen['id_empresa_origen'])
                ->where('estado_actual', 1);
                
            if ($tipo === 'empresa') {
                $queryExistente->where('id_empresa_destino', $id);
            } else {
                $queryExistente->where('id_cliente_auditoria_destino', $id);
            }
            
            $existente = $queryExistente->first();

            if ($existente) {
                if ($existente->fecha_inicio !== $origen['fecha_inicio'] ||
                    $existente->fecha_fin !== $origen['fecha_fin'] ||
                    $existente->firmado != ($origen['firmado'] ?? 0)) {
                    $existente->update([
                        'fecha_inicio' => $origen['fecha_inicio'],
                        'fecha_fin' => $origen['fecha_fin'],
                        'firmado' => $origen['firmado'] ?? 0,
                    ]);
                }
                continue;
            }

            Contrato::create([
                'id_empresa_origen' => $origen['id_empresa_origen'],
                'id_empresa_destino' => $tipo === 'empresa' ? $id : null,
                'id_cliente_auditoria_destino' => $tipo === 'cliente' ? $id : null,
                'fecha_inicio' => $origen['fecha_inicio'],
                'fecha_fin' => $origen['fecha_fin'],
                'firmado' => $origen['firmado'] ?? 0,
                'estado_actual' => 1,
                'fecha_cancelacion' => null,
            ]);
        }

        return redirect()->route('contratos.index_destinos')
            ->with('success', 'Contratos actualizados correctamente.');
    }

    public function desactivarDestinos($tipo, $id)
    {
        $query = Contrato::where('estado_actual', 1);
        if ($tipo === 'empresa') {
            $query->where('id_empresa_destino', $id);
        } else {
            $query->where('id_cliente_auditoria_destino', $id);
        }
        
        $query->update([
            'estado_actual' => 0,
            'fecha_cancelacion' => now(),
        ]);
            
        return redirect()->route('contratos.index_destinos')
            ->with('success', 'Todos los contratos del destino fueron desactivados correctamente.');
    }

    public function getExpiringContractsAjax(Request $request)
    {
        $period = $request->input('period', 'mes'); // 'mes' or 'semana'
        $today = now()->startOfDay();
        
        if ($period === 'semana') {
            $limitDate = $today->copy()->addDays(7);
        } else {
            $limitDate = $today->copy()->endOfMonth();
        }

        $contratos = Contrato::with(['empresaOrigen', 'empresaDestino', 'clienteAuditoriaDestino'])
            ->where('estado_actual', 1)
            ->whereBetween('fecha_fin', [$today->toDateString(), $limitDate->toDateString()])
            ->orderBy('fecha_fin', 'asc')
            ->get();

        $data = $contratos->map(function ($contrato) use ($today) {
            $fechaFin = \Carbon\Carbon::parse($contrato->fecha_fin)->startOfDay();
            $diasRestantes = $today->diffInDays($fechaFin, false);
            
            $razonSocialDestino = $contrato->empresaDestino 
                ? $contrato->empresaDestino->razon_social 
                : ($contrato->clienteAuditoriaDestino ? $contrato->clienteAuditoriaDestino->razon_social : 'N/A');

            return [
                'id' => $contrato->id_contratos,
                'origen' => $contrato->empresaOrigen->razon_social ?? 'N/A',
                'destino' => $razonSocialDestino,
                'fecha_fin' => $fechaFin->format('d/m/Y'),
                'fecha_inicio' => \Carbon\Carbon::parse($contrato->fecha_inicio)->format('d/m/Y'),
                'raw_fecha_fin' => $contrato->fecha_fin,
                'raw_fecha_inicio' => $contrato->fecha_inicio,
                'dias_restantes' => (int) $diasRestantes,
            ];

        });

        return response()->json($data);
    }

    public function toggleFirmado($id)
    {
        $contrato = Contrato::findOrFail($id);
        $contrato->firmado = !$contrato->firmado;
        $contrato->save();

        return response()->json([
            'success' => true,
            'firmado' => $contrato->firmado,
            'message' => 'Estado de firma actualizado.'
        ]);
    }
}


