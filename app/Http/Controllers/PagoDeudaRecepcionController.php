<?php

namespace App\Http\Controllers;

use App\Models\PagoDeudaRecepcion;
use Illuminate\Http\Request;

class PagoDeudaRecepcionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_deuda' => 'required|exists:deuda_recepcion,id_deuda_recepcion',
            'fecha_pago' => 'required|date',
            'monto' => 'required|numeric|min:0',
            'metodo_pago' => 'required|string',
            'nro_operacion' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'tipo_vista' => 'nullable|string|in:contable,otros,ambas', // Agregar esta línea
        ]);

        $deuda = \App\Models\DeudaRecepcion::with('cotizacion')->findOrFail($data['id_deuda']);

        // Crear el pago
        \App\Models\PagoDeudaRecepcion::create([
            'id_deuda_recepcion' => $deuda->id_deuda_recepcion,
            'monto_pagado' => $data['monto'],
            'fecha_pago' => $data['fecha_pago'],
            'metodo_pago' => $data['metodo_pago'],
            'nro_pago' => $data['nro_operacion'] ?? '',
            'observacion' => $data['observaciones'],
        ]);

        // Actualizar monto pendiente
        $totalPagado = $deuda->pagos()->sum('monto_pagado');
        $deuda->monto_pendiente = $deuda->monto_deuda - $totalPagado;

        // Actualizar estado
        if ($deuda->monto_pendiente <= 0) {
            $deuda->estado = 1;
            $deuda->monto_pendiente = 0;
        } else {
            $deuda->estado = 2;
        }

        $deuda->save();

        if ($request->expectsJson() || $request->ajax()) {
            session()->flash('success', 'Pago registrado correctamente.');
            return response()->json([
                'success' => true,
                'message' => 'Pago registrado correctamente.',
                'monto_pendiente' => $deuda->monto_pendiente
            ]);
        }

        return redirect()->route('deudas_recepcion.show', [
            'id' => $deuda->cotizacion->id_empresa_cliente,
            'tipo' => $data['tipo_vista'] ?? $deuda->cotizacion->tipo_cotizacion
        ])->with('success', 'Pago registrado correctamente.');
    }


    /**
     * Display the specified resource.
     */
    public function show(PagoDeudaRecepcion $pagoDeudaRecepcion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PagoDeudaRecepcion $pagoDeudaRecepcion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $pago = PagoDeudaRecepcion::findOrFail($id);
        $deuda = $pago->deudaRecepcion;

        $data = $request->validate([
            'fecha_pago' => 'required|date',
            'monto' => 'required|numeric|min:0',
            'metodo_pago' => 'required|string',
            'nro_operacion' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ]);

        // Calcular total pagado sin incluir el pago actual
        $totalPagadoSinActual = $deuda->pagos()->where('id_pago_deuda_recepcion', '!=', $id)->sum('monto_pagado');
        $nuevoTotalPagado = $totalPagadoSinActual + $data['monto'];

        // Validar que el total no exceda la deuda
        if ($nuevoTotalPagado > $deuda->monto_deuda) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Monto de pago no permitido'], 422);
            }
            return back()->with('error', 'Monto de pago no permitido');
        }

        $pago->update([
            'monto_pagado' => $data['monto'],
            'fecha_pago' => $data['fecha_pago'],
            'metodo_pago' => $data['metodo_pago'],
            'nro_pago' => $data['nro_operacion'] ?? '',
            'observacion' => $data['observaciones'],
        ]);

        // Recalcular monto pendiente y estado de la deuda
        $totalPagado = $deuda->pagos()->sum('monto_pagado');
        $deuda->monto_pendiente = $deuda->monto_deuda - $totalPagado;

        if ($deuda->monto_pendiente <= 0) {
            $deuda->estado = 1;
            $deuda->monto_pendiente = 0;
        } elseif ($totalPagado > 0) {
            $deuda->estado = 2;
        } else {
            $deuda->estado = 0;
        }

        $deuda->save();

        if ($request->expectsJson() || $request->ajax()) {
            session()->flash('success', 'Pago actualizado correctamente.');
            return response()->json(['success' => true, 'message' => 'Pago actualizado correctamente.']);
        }
        return back()->with('success', 'Pago actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PagoDeudaRecepcion $pagoDeudaRecepcion)
    {
        //
    }
}
