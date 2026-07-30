<?php

namespace App\Http\Controllers;

use App\Models\PagoAlquiler;
use Illuminate\Http\Request;

class PagoAlquilerController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'id_alquiler' => 'required|exists:alquiler,id_alquiler',
            'mes' => 'required|integer|min:1|max:12',
            'anio' => 'required|integer',
            'fecha_pago' => 'required|date',
            'monto_pago' => 'required|numeric|min:0',
            'observacion' => 'nullable|string',
        ]);

        // 🔒 BLOQUEAR DUPLICADO POR MES
        $existe = PagoAlquiler::where('id_alquiler', $request->id_alquiler)
            ->where('mes', $request->mes)
            ->where('anio', $request->anio)
            ->exists();

        if ($existe) {
            return back()->with('error', 'Este mes ya tiene un pago registrado.');
        }

        $sunat = round($request->monto_pago * 0.05, 2);

        $pago = PagoAlquiler::create([
            'id_alquiler' => $request->id_alquiler,
            'id_usuario' => auth()->id(),
            'mes' => $request->mes,
            'anio' => $request->anio,
            'fecha_pago' => $request->fecha_pago,
            'monto_pago' => $request->monto_pago,
            'porcentaje_sunat' => 5,
            'monto_sunat' => $sunat,
            'observacion' => $request->observacion,
        ]);

        // Revisar si ya se pagaron todos los meses
        $alquiler = $pago->alquiler;

        // Total de meses del contrato
        $inicio = \Carbon\Carbon::parse($alquiler->inicio_contrato)->startOfMonth();
        $fin = \Carbon\Carbon::parse($alquiler->fin_contrato)->startOfMonth();
        $periodo = \Carbon\CarbonPeriod::create($inicio, '1 month', $fin);
        $totalMeses = iterator_count($periodo);

        // Total de pagos registrados
        $totalPagos = $alquiler->pagos()->count();

        // Si ya se pagó todo → inactivar
        if ($totalPagos >= $totalMeses && $alquiler->estado === 'activo') {
            $alquiler->update([
                'estado' => 'inactivo',
            ]);
        }

        return back()->with('success', 'Pago registrado correctamente.');
    }

    public function destroy($id)
    {
        // Seguridad: solo tipo_usuario = 1
        if (auth()->user()->tipo_usuario != 1) {
            abort(403, 'No autorizado.');
        }

        $pago = PagoAlquiler::findOrFail($id);
        $pago->delete();

        return back()->with('success', 'Pago eliminado correctamente.');
    }


}

