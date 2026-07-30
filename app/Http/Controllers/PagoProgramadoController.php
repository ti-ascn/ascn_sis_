<?php

namespace App\Http\Controllers;

use App\Models\PagosProgramados;
use App\Models\PagosGenerados;
use App\Models\TiposPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PagoProgramadoController extends Controller
{
    public function index()
    {
        $pagos = PagosProgramados::all();

        foreach ($pagos as $pago) {
            $fechaInicio = Carbon::parse($pago->fecha_inicio);
            $dia = $fechaInicio->day;
            $mesNombre = $fechaInicio->translatedFormat('F'); // Abril, Mayo, etc.
            $diaSemana = $fechaInicio->translatedFormat('l'); // Lunes, Martes, etc.
            $fechaFormateada = $fechaInicio->format('d/m/Y');

            switch ($pago->frecuencia) {
                case 'una_vez':
                    $pago->frecuencia_detalle = "Una vez: {$fechaFormateada}";
                    break;
                case 'diario':
                    $pago->frecuencia_detalle = "Diario: todos los días";
                    break;
                case 'semanal':
                    $pago->frecuencia_detalle = "Semanal: cada {$diaSemana}";
                    break;
                case 'mensual':
                    $pago->frecuencia_detalle = "Mensual: cada {$dia}";
                    break;
                case 'anual':
                    $pago->frecuencia_detalle = "Anual: cada {$dia} de {$mesNombre}";
                    break;
                default:
                    $pago->frecuencia_detalle = ucfirst($pago->frecuencia);
            }
        }

        return view('pagos_programados.index', compact('pagos'));
    }

    public function create()
    {
        $tiposPago = TiposPago::all();
        return view('pagos_programados.create', compact('tiposPago'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255',
            'monto' => 'required|numeric',
            'id_tipo_pago' => 'required|exists:tipos_pagos,id_tipo_pago',
            'frecuencia' => 'required|in:una_vez,diario,semanal,mensual,anual',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        $activo = $request->frecuencia === 'una_vez' ? false : true;

        $pagoProgramado = PagosProgramados::create([
            'descripcion' => $request->descripcion,
            'monto' => $request->monto,
            'id_tipo_pago' => $request->id_tipo_pago,
            'frecuencia' => $request->frecuencia,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'id_usuario' => Auth::id(),
            'activo' => $activo,
        ]);

        // Si la frecuencia es "una vez", generar el pago de inmediato
        if ($pagoProgramado->frecuencia === 'una_vez') {
            $pagoGenerado = new PagosGenerados();
            $pagoGenerado->id_pago_programado = $pagoProgramado->id_pago_programado;
            $pagoGenerado->monto = $pagoProgramado->monto;
            $pagoGenerado->fecha = $pagoProgramado->fecha_inicio;
            $pagoGenerado->pagado = false;
            $pagoGenerado->save();
        }

        return redirect()->route('pagos_programados.index')->with('success', 'Pago programado registrado.');
    }

    public function edit($id)
    {
        $pagoProgramado = PagosProgramados::where('id_pago_programado', $id)->firstOrFail();
        $tiposPago = TiposPago::all();

        return view('pagos_programados.edit', compact('pagoProgramado', 'tiposPago'));
    }

    public function update(Request $request, $id)
    {
        $pagoProgramado = PagosProgramados::where('id_pago_programado', $id)->firstOrFail();

        $request->validate([
            'descripcion' => 'required|string|max:255',
            'monto' => 'required|numeric',
            'id_tipo_pago' => 'required|exists:tipos_pagos,id_tipo_pago',
            'frecuencia' => 'required|in:una_vez,diario,semanal,mensual,anual',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        $activo = $request->frecuencia === 'una_vez' ? false : true;

        $pagoProgramado->update([
            'descripcion' => $request->descripcion,
            'monto' => $request->monto,
            'id_tipo_pago' => $request->id_tipo_pago,
            'frecuencia' => $request->frecuencia,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'activo' => $activo,
        ]);

        // Si cambió a frecuencia "una vez" y no existe un pago generado, crearlo
        if ($pagoProgramado->frecuencia === 'una_vez' && !$pagoProgramado->pagosGenerados()->exists()) {
            $pagoGenerado = new PagosGenerados();
            $pagoGenerado->id_pago_programado = $pagoProgramado->id_pago_programado;
            $pagoGenerado->monto = $pagoProgramado->monto;
            $pagoGenerado->fecha = $pagoProgramado->fecha_inicio;
            $pagoGenerado->pagado = false;
            $pagoGenerado->save();
        }

        return redirect()->route('pagos_programados.index')
            ->with('success', 'Pago programado actualizado correctamente.');
    }

    public function destroy($id)
    {
        $pagoProgramado = PagosProgramados::where('id_pago_programado', $id)
            ->where('id_usuario', Auth::id())
            ->firstOrFail();

        // Eliminar pagos generados asociados primero
        $pagoProgramado->pagosGenerados()->delete();

        // Luego eliminar el pago programado
        $pagoProgramado->delete();

        return redirect()->route('pagos_programados.index')
            ->with('success', 'Pago programado eliminado correctamente.');
    }

    public function desactivar($id)
    {
        $pago = PagosProgramados::findOrFail($id);
        $pago->activo = false;
        $pago->save();

        return redirect()->route('pagos_programados.index')->with('success', 'Pago programado desactivado correctamente.');
    }

    public function reactivar($id)
    {
        $pago = PagosProgramados::findOrFail($id);
        $pago->activo = true;
        $pago->save();

        return redirect()->route('pagos_programados.index')
            ->with('success', 'Pago programado reactivado correctamente.');
    }

    public function generarPagoManual(Request $request, $id)
    {
        $request->validate([
            'fecha' => 'required|date',
            'monto' => 'required|numeric|min:0',  // Validar que el monto sea numérico y no negativo
        ]);

        $pagoProgramado = PagosProgramados::findOrFail($id);

        // Verificar si ya existe un pago generado para esa fecha
        $existe = PagosGenerados::where('id_pago_programado', $id)
            ->whereDate('fecha', $request->fecha)
            ->exists();

        if ($existe) {
            return redirect()->back()->with('error', 'Ya existe un pago generado para esa fecha.');
        }

        $nuevoPago = new PagosGenerados();
        $nuevoPago->id_pago_programado = $pagoProgramado->id_pago_programado;
        $nuevoPago->monto = $request->monto ?: $pagoProgramado->monto; // Usar el monto modificado o el predeterminado
        $nuevoPago->fecha = $request->fecha;
        $nuevoPago->pagado = false;
        $nuevoPago->save();

        return redirect()->back()->with('success', 'Pago generado manualmente para la fecha seleccionada.');
    }



}
