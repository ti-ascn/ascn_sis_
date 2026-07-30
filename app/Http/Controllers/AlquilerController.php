<?php

namespace App\Http\Controllers;

use App\Models\Alquiler;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;


class AlquilerController extends Controller
{

    public function index()
    {
        $alquileres = Alquiler::with(['direccion.empresa'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('id_direccion')
            ->map(function ($contratos) {
                $activo = $contratos->firstWhere('estado', 'activo');
                return $activo ?? $contratos->first();
            })
            ->values();

        // RESUMEN POR EMPRESA
        $resumenEmpresas = DB::table('empresas_clientes as e')
            ->join('direcciones as d', 'd.id_empresas_clientes', '=', 'e.id_empresas_clientes')
            ->join('alquiler as a', 'a.id_direccion', '=', 'd.id_direccion')
            ->join('pago_alquiler as p', 'p.id_alquiler', '=', 'a.id_alquiler')
            ->select(
                'e.id_empresas_clientes',
                'e.razon_social',
                DB::raw('SUM(p.monto_pago) as total_pagado'),
                DB::raw('SUM(p.monto_sunat) as total_sunat')
            )
            ->groupBy('e.id_empresas_clientes', 'e.razon_social')
            ->orderBy('e.razon_social')
            ->get();

        return view('alquiler.index', compact('alquileres', 'resumenEmpresas'));
    }

    public function show($id)
    {
        $alquiler = Alquiler::with([
            'direccion',
            'pagos' => function ($q) {
                $q->orderBy('anio')->orderBy('mes');
            }
        ])->findOrFail($id);

        // Obtener todos los contratos de esta dirección (ordenados por fecha de creación descendente)
        $contratosHistoricos = Alquiler::where('id_direccion', $alquiler->id_direccion)
            ->withCount('pagos')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calcular información adicional para cada contrato
        foreach ($contratosHistoricos as $contrato) {
            $inicio = Carbon::parse($contrato->inicio_contrato)->startOfMonth();
            $fin = Carbon::parse($contrato->fin_contrato)->startOfMonth();
            $periodo = CarbonPeriod::create($inicio, '1 month', $fin);
            $totalMeses = iterator_count($periodo);

            $contrato->total_meses = $totalMeses;
            $contrato->porcentaje_pagado = $totalMeses > 0 ? round(($contrato->pagos_count / $totalMeses) * 100, 0) : 0;
            $contrato->completado = $contrato->pagos_count >= $totalMeses;
        }

        // Periodo del contrato actual por meses
        $inicio = Carbon::parse($alquiler->inicio_contrato)->startOfMonth();
        $fin = Carbon::parse($alquiler->fin_contrato)->startOfMonth();

        $periodo = CarbonPeriod::create($inicio, '1 month', $fin);

        // Meses ya pagados
        $pagados = $alquiler->pagos
            ->map(fn($p) => $p->anio . '-' . str_pad($p->mes, 2, '0', STR_PAD_LEFT))
            ->toArray();

        $mesesDisponibles = [];
        $todosPagados = true;

        foreach ($periodo as $fecha) {
            $key = $fecha->format('Y-m');
            if (!in_array($key, $pagados)) {
                $todosPagados = false;
                $mesesDisponibles[] = [
                    'mes' => $fecha->month,
                    'anio' => $fecha->year,
                    'label' => ucfirst($fecha->translatedFormat('F Y')),
                ];
            }
        }

        // Verificar si se puede crear nuevo contrato
        $puedeCrearNuevoContrato = false;
        $razon = '';

        if ($alquiler->estado === 'inactivo') {
            $puedeCrearNuevoContrato = true;
            $razon = 'Contrato desistido';
        } elseif ($todosPagados && $alquiler->estado === 'activo') {
            // Verificar si la fecha fin ya pasó
            $hoy = now();
            $finContrato = Carbon::parse($alquiler->fin_contrato);

            if ($hoy->greaterThanOrEqualTo($finContrato)) {
                $puedeCrearNuevoContrato = true;
                $razon = 'Contrato completado';
            }
        }

        return view('alquiler.show', compact(
            'alquiler',
            'mesesDisponibles',
            'todosPagados',
            'puedeCrearNuevoContrato',
            'razon',
            'contratosHistoricos'
        ));
    }

    public function destroy($id)
    {
        // Solo usuarios administradores pueden eliminar
        if (auth()->user()->tipo_usuario != 1) {
            abort(403, 'No autorizado.');
        }

        $alquiler = Alquiler::findOrFail($id);

        // Verificar que no tenga pagos asociados
        if ($alquiler->pagos()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un contrato que tiene pagos registrados.');
        }

        $alquiler->delete();

        return redirect()->route('alquiler.index')->with('success', 'Contrato eliminado correctamente');
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_direccion' => 'required|exists:direcciones,id_direccion',
            'nombre_arrendador' => 'required|string',
            'dni_arrendador' => 'required|string|max:20',
            'inicio_contrato' => 'required|date',
            'fin_contrato' => 'required|date|after:inicio_contrato',
            'pago_mensual' => 'required|numeric|min:0',
            'observacion' => 'nullable|string',
        ]);

        // Seguridad extra
        $existe = Alquiler::where('id_direccion', $request->id_direccion)
            ->where('estado', 'activo')
            ->exists();

        if ($existe) {
            return back()->with('error', 'La dirección ya tiene un alquiler activo.');
        }

        $alquiler = Alquiler::create([
            'id_direccion' => $request->id_direccion,
            'nombre_arrendador' => $request->nombre_arrendador,
            'dni_arrendador' => $request->dni_arrendador,
            'inicio_contrato' => $request->inicio_contrato,
            'fin_contrato' => $request->fin_contrato,
            'pago_mensual' => $request->pago_mensual,
            'observacion' => $request->observacion,
            'estado' => 'activo',
        ]);

        return redirect()->route('alquiler.show', $alquiler->id_alquiler)
            ->with('success', 'Alquiler registrado correctamente.');
    }


    public function desistir($id)
    {
        $alquiler = Alquiler::findOrFail($id);

        $alquiler->update([
            'estado' => 'inactivo',
            'fecha_desistimiento' => now()
        ]);

        return back()->with('success', 'Alquiler desactivado');
    }

    public function edit($id)
    {
        $alquiler = Alquiler::with('direccion')->findOrFail($id);
        return view('alquiler.partials.form', compact('alquiler'));
    }

    public function update(Request $request, $id)
    {
        $alquiler = Alquiler::findOrFail($id);

        $alquiler->update($request->only([
            'nombre_arrendador',
            'dni_arrendador',
            'inicio_contrato',
            'fin_contrato',
            'pago_mensual',
            'observacion'
        ]));

        return back()->with('success', 'Alquiler actualizado correctamente');
    }

    public function toggleEstado($id)
    {
        $alquiler = Alquiler::findOrFail($id);

        if ($alquiler->estado === 'activo') {
            $alquiler->update([
                'estado' => 'inactivo',
            ]);
        } else {
            // Verificar que no exista otro activo en esa dirección
            $existe = Alquiler::where('id_direccion', $alquiler->id_direccion)
                ->where('estado', 'activo')
                ->where('id_alquiler', '!=', $alquiler->id_alquiler)
                ->exists();

            if ($existe) {
                return back()->with('error', 'Ya existe un contrato activo para esta dirección.');
            }

            $alquiler->update([
                'estado' => 'activo',
                'fecha_desistimiento' => null,
            ]);
        }

        return back()->with('success', 'Estado del alquiler actualizado.');
    }



}

