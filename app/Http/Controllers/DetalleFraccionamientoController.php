<?php

namespace App\Http\Controllers;

use App\Models\DetalleFraccionamiento;
use App\Models\Fraccionamiento;
use Illuminate\Http\Request;

class DetalleFraccionamientoController extends Controller
{
    public function create($id_fraccionamiento)
    {
        $fraccionamiento = Fraccionamiento::findOrFail($id_fraccionamiento);

        $cuotas = [];
        if ($fraccionamiento->cuota_acogimiento) {
            // Si tiene cuota de acogimiento, se agregan CA e IA
            $cuotas[] = 'CA';
            $cuotas[] = 'IA';
        }

        // Agregamos las cuotas normales
        for ($i = 1; $i <= $fraccionamiento->nro_cuotas; $i++) {
            $cuotas[] = (string) $i;
        }

        return view('detalle_fraccionamiento.create', compact('fraccionamiento', 'cuotas'));
    }


    public function store(Request $request, $id_fraccionamiento)
    {
        $fraccionamiento = Fraccionamiento::findOrFail($id_fraccionamiento);

        $data = $request->validate([
            'nombre_cuota.*' => 'nullable|string|max:10',
            'fecha_vencimiento.*' => 'nullable|date',
            'amortizacion.*' => 'nullable|numeric',
            'interes.*' => 'nullable|numeric',
            'total.*' => 'nullable|numeric',
            'estado.*' => 'required|string|in:pendiente,pagado',
        ]);

        foreach ($request->nombre_cuota as $i => $nombre) {
            DetalleFraccionamiento::create([
                'id_fraccionamiento' => $fraccionamiento->id_fraccionamiento,
                'nombre_cuota' => $nombre,
                'fecha_vencimiento' => $request->fecha_vencimiento[$i],
                'amortizacion' => $request->amortizacion[$i],
                'interes' => $request->interes[$i],
                'total' => $request->total[$i],
                'estado' => $request->estado[$i],
            ]);
        }

        return redirect()
            ->route('detalle_fraccionamiento.show', $fraccionamiento->id_fraccionamiento)
            ->with('success', 'Detalles de fraccionamiento registrados correctamente.');
    }

    public function edit($id_detalle_fraccionamiento)
    {
        $detalle = DetalleFraccionamiento::findOrFail($id_detalle_fraccionamiento);
        return view('detalle_fraccionamiento.edit', compact('detalle'));
    }

    public function update(Request $request, $id_detalle_fraccionamiento)
    {
        $data = $request->validate([
            'nombre_cuota' => 'nullable|string|max:10',
            'fecha_vencimiento' => 'nullable|date',
            'amortizacion' => 'nullable|numeric',
            'interes' => 'nullable|numeric',
            'total' => 'nullable|numeric',
            'estado' => 'required|string|in:pendiente,pagado',
        ]);

        $detalle = DetalleFraccionamiento::findOrFail($id_detalle_fraccionamiento);
        $detalle->update($data);

        return redirect()
            ->back()
            ->with('success', 'Detalle actualizado correctamente.');
    }

    public function destroy($id_detalle_fraccionamiento)
    {
        $detalle = DetalleFraccionamiento::findOrFail($id_detalle_fraccionamiento);
        $detalle->delete();
        return back()->with('success', 'Detalle eliminado correctamente.');
    }

    public function show($id_fraccionamiento)
    {
        $fraccionamiento = Fraccionamiento::with(['empresa', 'detalles'])->findOrFail($id_fraccionamiento);

        return view('detalle_fraccionamiento.show', compact('fraccionamiento'));
    }

    public function toggle(Request $request, $id)
    {
        $detalle = DetalleFraccionamiento::findOrFail($id);

        if ($detalle->estado === 'pendiente') {
            $request->validate([
                'fecha_pago' => 'required|date',
            ]);
            $detalle->estado = 'pagado';
            $detalle->fecha_pago = $request->fecha_pago;
        } else {
            $detalle->estado = 'pendiente';
            $detalle->fecha_pago = null;
        }

        $detalle->save();

        return back()->with('success', 'Estado y fecha de pago actualizados correctamente.');
    }



}
