<?php

namespace App\Http\Controllers;

use App\Models\Deuda;
use App\Models\PagoDeuda;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoDeudaController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_deuda' => 'required|exists:deudas,id_deuda',
            'fecha_pago' => 'required|date',
            'fecha_vencimiento' => 'nullable|date',
            'monto' => 'required|integer|min:0',
            'interes' => 'nullable|numeric|min:0',
            'incluir_interes' => 'nullable|in:0,1',
            'observaciones' => 'nullable|string',
        ]);

        $incluirInteres = (int) ($data['incluir_interes'] ?? 0);

        return DB::transaction(function () use ($data, $incluirInteres) {

            $deuda = \App\Models\Deuda::with('pagos')->lockForUpdate()->findOrFail($data['id_deuda']);

            if (!empty($data['fecha_vencimiento'])) {
                $deuda->fecha_vencimiento = $data['fecha_vencimiento'];
            }

            $principalPagado = (int) $deuda->pagos()->sum('monto');
            $principalPend = max(0, (int) $deuda->monto_deuda - $principalPagado);

            $fechaVcto = \Carbon\Carbon::parse($deuda->fecha_vencimiento)->startOfDay();
            $fechaPago = \Carbon\Carbon::parse($data['fecha_pago'])->startOfDay();

            // === A) Solo interés (principal ya 0) ===
            if ($principalPend === 0) {
                $interesPendBD = (int) $deuda->interes_pendiente;
                if ($interesPendBD <= 0) {
                    return back()->with('error', 'No hay interés pendiente por pagar en esta deuda.');
                }

                \App\Models\PagoDeuda::create([
                    'id_deuda' => $deuda->id_deuda,
                    'id_usuario' => auth()->id(),
                    'monto' => 0,
                    'interes' => $interesPendBD,
                    'monto_total' => $interesPendBD,
                    'fecha_pago' => $data['fecha_pago'],
                ]);

                $deuda->interes_pendiente = 0;
                $deuda->estado = 1; // ya no queda nada
                $deuda->save();

                return back()->with('success', 'Interés pendiente pagado correctamente.');
            }

            // === B) Hay principal pendiente ===
            $montoAPagar = min($principalPend, (int) $data['monto']);

            $dias = 0;
            if ($fechaPago->gt($fechaVcto)) {
                $desde = $fechaVcto->copy()->addDay()->startOfDay();
                $dias = $desde->diffInDays($fechaPago) + 1; // como en el front
            }

            // === Cálculo de interés sobre el MONTO ORIGINAL (no el saldo) ===
            $interesRed = 0;
            if ($fechaPago->gt($fechaVcto)) {
                $desde = $fechaVcto->copy()->addDay()->startOfDay();
                $dias = $desde->diffInDays($fechaPago) + 1;
                $interesRaw = $deuda->monto_deuda * 0.0003 * $dias;
                $interesRed = (int) round($interesRaw, 0, PHP_ROUND_HALF_UP);
            }

            // === Permitir ajuste manual del interés (si el usuario lo cambió) ===
            $interesManual = isset($data['interes']) ? (float) $data['interes'] : null;

            if (!is_null($interesManual)) {
                if ($interesManual == 0 && $incluirInteres === 0) {
                    // No se cobra interés, pero no se borra el cálculo real
                    // Mantén el interés calculado para que quede pendiente
                } elseif (abs($interesManual - $interesRed) <= 5) {
                    $interesRed = $interesManual;
                } else {
                    return back()->with(
                        'error',
                        "El ajuste manual del interés no puede superar ±5 soles del cálculo automático (S/. {$interesRed})."
                    );
                }
            }


            // === Evitar duplicar interés ya pagado ===
            $interesPagadoHistorico = (int) $deuda->pagos()->sum('interes');
            $interesPendiente = max(0, $interesRed - $interesPagadoHistorico);

            // === Decidir si este pago incluye el interés ===
            $interesDelPago = 0;
            if ($incluirInteres === 1) {
                $interesDelPago = $interesPendiente;
                $deuda->interes_pendiente = 0;
            } else {
                $deuda->interes_pendiente = $interesPendiente;
            }

            \App\Models\PagoDeuda::create([
                'id_deuda' => $deuda->id_deuda,
                'id_usuario' => auth()->id(),
                'monto' => $montoAPagar,
                'interes' => $interesDelPago,
                'monto_total' => $montoAPagar + $interesDelPago,
                'fecha_pago' => $data['fecha_pago'],
            ]);

            $sumPrincipal = (int) $deuda->pagos()->sum('monto');
            $principalPost = max(0, (int) $deuda->monto_deuda - $sumPrincipal);

            $deuda->estado = ($principalPost === 0 && (int) $deuda->interes_pendiente === 0) ? 1 : 0;
            if ($principalPost === 0) {
                $deuda->pago_puntual = (int) ($fechaPago->lessThanOrEqualTo($fechaVcto));
            }
            $deuda->interes_calculado = $interesRed;

            // aqui actualizamos observaciones también
            if (!empty($data['observaciones'])) {
                $deuda->observaciones = $data['observaciones'];
            }

            // y recién aquí guardamos una sola vez
            $deuda->save();

            return back()->with('success', 'Pago registrado correctamente.');
        });
    }


}
