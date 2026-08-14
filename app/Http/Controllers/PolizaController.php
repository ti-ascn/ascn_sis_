<?php

namespace App\Http\Controllers;

use App\Models\EmpresasClientes;
use App\Models\HistorialPoliza;
use App\Models\Poliza;
use App\Models\PolizaSctr;
use App\Models\PolizaVida;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PolizaController extends Controller
{
    public function index(Request $request)
    {
        $empresas = EmpresasClientes::select(
            'id_empresas_clientes',
            'razon_social',
            'ruc',
            'planilla',
            'estado_actual'
        )
            ->where('planilla', 1)
            ->whereIn('estado_actual', [1, 2])
            ->orderBy('razon_social')
            ->with(['polizas.vida', 'polizas.sctr'])
            ->get();

        return view('polizas.index', compact('empresas'));
    }


    public function create(Request $request)
    {
        $poliza = Poliza::with('empresa')->findOrFail($request->id_poliza);
        $tipos = explode(',', $request->tipos);

        return view('polizas.create', compact('poliza', 'tipos'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'id_empresa_cliente' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'broker' => 'nullable|string|max:100',
            'tipos' => 'required|array|min:1',
        ]);

        $empresaId = $request->id_empresa_cliente;
        $tipos = $request->tipos;

        // Validar existencia previa
        $polizaExistente = Poliza::where('id_empresa_cliente', $empresaId)
            ->where('estado', 1)
            ->with(['vida', 'sctr'])
            ->first();

        if ($polizaExistente) {
            if ($polizaExistente->vida && in_array('vida', $tipos)) {
                return back()->with('error', 'Esta empresa ya tiene una póliza de Vida Ley activa.');
            }
            if ($polizaExistente->sctr && in_array('sctr', $tipos)) {
                return back()->with('error', 'Esta empresa ya tiene una póliza de SCTR activa.');
            }
        }

        // Crear o reutilizar la póliza base
        $poliza = $polizaExistente ?: Poliza::create([
            'id_empresa_cliente' => $empresaId,
            'broker' => $request->broker,
            'id_usuario' => auth()->id(),
            'estado' => 1,
        ]);

        // Redirigir al create con los tipos permitidos
        return redirect()->route('polizas.create', [
            'id_poliza' => $poliza->id_poliza,
            'tipos' => implode(',', $tipos),
        ])->with('success', 'Póliza creada, ahora completa los datos.');
    }


    public function storeDetalle(Request $request)
    {
        $tipos = explode(',', $request->tipos);
        $poliza = Poliza::findOrFail($request->id_poliza);
        $usuario = auth()->id();

        // Crear VIDA
        if (in_array('vida', $tipos)) {
            // Evita duplicados si el formulario se envía más de una vez.
            $vida = PolizaVida::firstOrCreate([
                'id_poliza' => $poliza->id_poliza,
            ], [
                'id_poliza' => $poliza->id_poliza,
                'aseguradora' => $request->vida_aseguradora,
                'broker' => $request->vida_broker,
                'nro_poliza' => $request->vida_nro_poliza,
                'fecha_inicio' => $request->vida_fecha_inicio,
                'fecha_fin' => $request->vida_fecha_fin,
                'estado' => $request->vida_estado,
                'observacion' => $request->vida_observacion,
                'id_usuario' => $usuario,
            ]);

            if ($vida->wasRecentlyCreated) {
                HistorialPoliza::create([
                    'id_poliza' => $poliza->id_poliza,
                    'tipo_poliza' => 'VIDA',
                    'cambio' => 'CREACION',
                    'detalle' => 'Póliza Vida registrada',
                    'usuario_responsable' => $usuario,
                ]);
            }
        }

        // Crear SCTR
        if (in_array('sctr', $tipos)) {
            // Evita duplicados si el formulario se envía más de una vez.
            $sctr = PolizaSctr::firstOrCreate([
                'id_poliza' => $poliza->id_poliza,
            ], [
                'id_poliza' => $poliza->id_poliza,
                'aseguradora' => $request->sctr_aseguradora,
                'broker' => $request->sctr_broker,
                'fecha_inicio' => $request->sctr_fecha_inicio,
                'fecha_fin' => $request->sctr_fecha_fin,
                'estado' => $request->sctr_estado,
                'observacion' => $request->sctr_observacion,
                'id_usuario' => $usuario,
            ]);

            if ($sctr->wasRecentlyCreated) {
                HistorialPoliza::create([
                    'id_poliza' => $poliza->id_poliza,
                    'tipo_poliza' => 'SCTR',
                    'cambio' => 'CREACION',
                'detalle' => 'Póliza SCTR registrada',
                'usuario_responsable' => $usuario,
                ]);
            }
        }

        return redirect()->route('polizas.index')->with('success', 'Póliza registrada correctamente.');
    }

    public function edit($id_poliza, Request $request)
    {
        $tipo = $request->query('tipo'); // 'vida' o 'sctr'
        $poliza = Poliza::with(['empresa', 'vida', 'sctr', 'historial.usuario'])->findOrFail($id_poliza);
        $empresas = EmpresasClientes::all();

        return view('polizas.edit', compact('poliza', 'empresas', 'tipo'));
    }

    public function update(Request $request, $id_poliza)
    {
        $poliza = Poliza::with(['vida', 'sctr'])->findOrFail($id_poliza);
        $usuario = auth()->id();
        $tipo = $request->input('tipo');

        $request->validate([
            'id_empresa_cliente' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'broker' => 'nullable|string|max:100',
            'estado_general' => 'required|in:0,1',
            'id_poliza_vida' => 'required_if:tipo,vida|nullable|integer',
            'id_poliza_sctr' => 'required_if:tipo,sctr|nullable|integer',
        ]);

        // =========================
        // CABECERA POLIZA
        // =========================
        $poliza->update([
            'id_empresa_cliente' => $request->id_empresa_cliente,
            'broker' => $request->broker,
            'estado' => $request->estado_general,
            'id_usuario' => $usuario,
        ]);

        // =========================
        // VIDA LEY
        // =========================
        if ($tipo === 'vida') {
            // La edición siempre actualiza el registro de Vida que abrió el formulario.
            $vida = PolizaVida::where('id_poliza', $poliza->id_poliza)
                ->findOrFail($request->id_poliza_vida);

            // Valores anteriores
            $anterior = [
                'aseguradora' => $vida->aseguradora,
                'broker' => $vida->broker,
                'nro_poliza' => $vida->nro_poliza,
                'estado' => $vida->estado,
                'fecha_inicio' => $vida->fecha_inicio,
                'fecha_fin' => $vida->fecha_fin,
                'observacion' => $vida->observacion,
            ];

            // Actualizar con nuevos valores
            $vida->update([
                'aseguradora' => $request->vida_aseguradora,
                'broker' => $request->vida_broker,
                'nro_poliza' => $request->vida_nro_poliza,
                'fecha_inicio' => $request->vida_fecha_inicio,
                'fecha_fin' => $request->vida_fecha_fin,
                'estado' => $request->vida_estado,
                'observacion' => $request->vida_observacion,
                'id_usuario' => $usuario,
            ]);

            // Detectar y registrar cambios
            $cambios = [];
            foreach ($anterior as $campo => $valorAnterior) {
                $valorNuevo = $vida->$campo;
                if ($valorAnterior != $valorNuevo) {
                    $nombreCampo = match ($campo) {
                        'aseguradora' => 'aseguradora',
                        'broker' => 'broker',
                        'nro_poliza' => 'número de póliza',
                        'estado' => 'estado',
                        'fecha_inicio' => 'fecha de inicio',
                        'fecha_fin' => 'fecha de fin',
                        'observacion' => 'observación',
                        default => $campo,
                    };
                    $cambios[] = "se cambió <strong>{$nombreCampo}</strong> de \"{$valorAnterior}\" a \"{$valorNuevo}\"";
                }
            }

            if (!empty($cambios)) {
                HistorialPoliza::create([
                    'id_poliza' => $poliza->id_poliza,
                    'tipo_poliza' => 'VIDA',
                    'cambio' => 'EDICION',
                    'detalle' => ucfirst(implode(', ', $cambios)) . '.',
                    'usuario_responsable' => $usuario,
                    'fecha_registro' => now(),
                ]);
            }
        }

        // =========================
        // SCTR
        // =========================
        if ($tipo === 'sctr') {
            // La edición siempre actualiza el registro SCTR que abrió el formulario.
            $sctr = PolizaSctr::where('id_poliza', $poliza->id_poliza)
                ->findOrFail($request->id_poliza_sctr);

            // Valores anteriores
            $anterior = [
                'aseguradora' => $sctr->aseguradora,
                'broker' => $sctr->broker,
                'estado' => $sctr->estado,
                'fecha_inicio' => $sctr->fecha_inicio,
                'fecha_fin' => $sctr->fecha_fin,
                'observacion' => $sctr->observacion,
            ];

            // Actualizar con nuevos valores
            $sctr->update([
                'aseguradora' => $request->sctr_aseguradora,
                'broker' => $request->sctr_broker,
                'fecha_inicio' => $request->sctr_fecha_inicio,
                'fecha_fin' => $request->sctr_fecha_fin,
                'estado' => $request->sctr_estado,
                'observacion' => $request->sctr_observacion,
                'id_usuario' => $usuario,
            ]);

            // Detectar y registrar cambios
            $cambios = [];
            foreach ($anterior as $campo => $valorAnterior) {
                $valorNuevo = $sctr->$campo;
                if ($valorAnterior != $valorNuevo) {
                    $nombreCampo = match ($campo) {
                        'aseguradora' => 'aseguradora',
                        'broker' => 'broker',
                        'estado' => 'estado',
                        'fecha_inicio' => 'fecha de inicio',
                        'fecha_fin' => 'fecha de fin',
                        'observacion' => 'observación',
                        default => $campo,
                    };
                    $cambios[] = "se cambió <strong>{$nombreCampo}</strong> de \"{$valorAnterior}\" a \"{$valorNuevo}\"";
                }
            }

            if (!empty($cambios)) {
                HistorialPoliza::create([
                    'id_poliza' => $poliza->id_poliza,
                    'tipo_poliza' => 'SCTR',
                    'cambio' => 'EDICION',
                    'detalle' => ucfirst(implode(', ', $cambios)) . '.',
                    'usuario_responsable' => $usuario,
                    'fecha_registro' => now(),
                ]);
            }
        }

        // =========================
        // REDIRECCIONES
        // =========================
        return match ($tipo) {
            'vida' => redirect()->route('polizas.vida.index')
                ->with('success', 'Póliza Vida Ley actualizada correctamente.'),
            'sctr' => redirect()->route('polizas.sctr.index')
                ->with('success', 'Póliza SCTR actualizada correctamente.'),
            default => redirect()->route('polizas.index')
                ->with('success', 'Póliza actualizada correctamente.'),
        };
    }

    public function destroy($id_poliza)
    {
        $poliza = Poliza::findOrFail($id_poliza);
        $poliza->delete();
        return redirect()->route('polizas.index')->with('success', 'Póliza eliminada.');
    }

    public function detalle($id_poliza)
    {
        $poliza = Poliza::with(['empresa', 'vida', 'sctr'])
            ->findOrFail($id_poliza);

        $detalles = [
            'empresa' => [
                'razon_social' => $poliza->empresa->razon_social,
                'ruc' => $poliza->empresa->ruc,
            ],
            'broker' => $poliza->broker,
            'estado' => $poliza->estado ? 'Activa' : 'Inactiva',
            'vida' => null,
            'sctr' => null,
        ];

        // Si tiene póliza de VIDA LEY
        if ($poliza->vida) {
            $fechaHoy = \Carbon\Carbon::today();
            $fechaFin = \Carbon\Carbon::parse($poliza->vida->fecha_fin)->startOfDay();
            $diasRestantes = $fechaHoy->diffInDays($fechaFin, false);

            $detalles['vida'] = [
                'id_poliza' => $poliza->id_poliza,
                'aseguradora' => $poliza->vida->aseguradora ?? '-',
                'broker' => $poliza->vida->broker ?? '-',
                'nro_poliza' => $poliza->vida->nro_poliza ?? '-',
                'fecha_inicio' => $poliza->vida->fecha_inicio
                    ? \Carbon\Carbon::parse($poliza->vida->fecha_inicio)->format('d/m/Y')
                    : '-',
                'fecha_fin' => $poliza->vida->fecha_fin
                    ? \Carbon\Carbon::parse($poliza->vida->fecha_fin)->format('d/m/Y')
                    : '-',
                'estado' => $poliza->vida->estado ?? 'COTIZAR',
                'dias_restantes' => $diasRestantes,
                'observacion' => $poliza->vida->observacion ?? '-',
                'usuario' => $poliza->vida->usuario->name ?? '-',
            ];
        }

        // Si tiene póliza SCTR
        if ($poliza->sctr) {
            $fechaHoy = \Carbon\Carbon::today();
            $fechaFin = \Carbon\Carbon::parse($poliza->sctr->fecha_fin)->startOfDay();
            $diasRestantes = $fechaHoy->diffInDays($fechaFin, false);

            $detalles['sctr'] = [
                'id_poliza' => $poliza->id_poliza,
                'aseguradora' => $poliza->sctr->aseguradora ?? '-',
                'broker' => $poliza->sctr->broker ?? '-',
                'fecha_inicio' => $poliza->sctr->fecha_inicio
                    ? \Carbon\Carbon::parse($poliza->sctr->fecha_inicio)->format('d/m/Y')
                    : '-',
                'fecha_fin' => $poliza->sctr->fecha_fin
                    ? \Carbon\Carbon::parse($poliza->sctr->fecha_fin)->format('d/m/Y')
                    : '-',
                'estado' => $poliza->sctr->estado ?? 'COTIZAR',
                'dias_restantes' => $diasRestantes,
                'observacion' => $poliza->sctr->observacion ?? '-',
                'usuario' => $poliza->sctr->usuario->name ?? '-',
            ];
        }

        return response()->json($detalles);
    }

    public function toggle($id_poliza)
    {
        $poliza = Poliza::findOrFail($id_poliza);
        $poliza->estado = !$poliza->estado;
        $poliza->save();

        return back()->with('success', 'Estado de póliza actualizado.');
    }

    public function indexVida()
    {
        $polizas = \App\Models\PolizaVida::with(['poliza.empresa'])
            ->whereHas('poliza', fn($q) => $q->where('estado', 1))
            ->orderByDesc('fecha_fin')
            ->get();

        return view('polizas.index-vida', compact('polizas'));
    }

    public function indexSctr()
    {
        $polizas = \App\Models\PolizaSctr::with(['poliza.empresa'])
            ->whereHas('poliza', fn($q) => $q->where('estado', 1))
            ->orderByDesc('fecha_fin')
            ->get();

        return view('polizas.index-sctr', compact('polizas'));
    }

}
