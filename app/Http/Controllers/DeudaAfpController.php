<?php

namespace App\Http\Controllers;

use App\Models\DeudaAfp;
use App\Models\EmpresasClientes;
use App\Models\TipoAfp;
use App\Models\Feriado;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Auth;

class DeudaAfpController extends Controller
{
    public function index(Request $request)
    {
        $anio = (int) $request->input('anio', now()->year);

        $empresas = EmpresasClientes::where('planilla', 1)
            ->whereIn('estado_actual', [1, 2])
            ->orderBy('razon_social')
            ->get();

        $deudas = DeudaAfp::with(['empresa', 'afp'])
            ->whereYear('periodo', $anio)
            ->get();

        $agregado = $deudas->groupBy([
            'id_empresa_cliente',
            function ($item) {
                return Carbon::parse($item->periodo)->month;
            }
        ])->map(function ($meses) {
            return collect($meses)->map(function ($deudasMes) {
                $hayPendientes = $deudasMes->contains(fn($d) => $d->estado === 'pendiente');

                if ($hayPendientes) {
                    // Solo sumar las deudas pendientes
                    $total = $deudasMes->where('estado', 'pendiente')->sum('monto_deuda');
                    $estado = 'Pendiente';
                } else {
                    // Si todas están pagadas, sumar el total completo
                    $total = $deudasMes->sum('total');
                    $estado = 'Pagado';
                }

                return ['total_mes' => $total, 'estado' => $estado];
            });
        });

        $filas = [];
        foreach ($empresas as $e) {
            $fila = [
                'id' => $e->id_empresas_clientes,
                'razon' => $e->razon_social,
                'ruc' => $e->ruc,
                'estado' => $e->estado,
                'estado_recursos' => $e->estado_recursos,
                'ud' => substr($e->ruc, -1),
                'meses' => []
            ];

            for ($m = 1; $m <= 12; $m++) {
                $datosMes = $agregado[$e->id_empresas_clientes][$m] ?? null;
                $fila['meses'][$m] = [
                    'total' => $datosMes['total_mes'] ?? 0,
                    'estado' => $datosMes['estado'] ?? '—',
                ];
            }

            $filas[] = $fila;
        }

        $afps = TipoAfp::where('estado', 1)->orderBy('nombre')->get();

        // 👇 nuevo cálculo: vencimiento basado en el mes actual
        $vctoActual = DeudaAfp::calcularVencimiento(now());

        return view('deuda_afp.index', compact('anio', 'filas', 'afps', 'vctoActual'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_empresa_cliente' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'id_afp' => 'required|exists:tipo_afp,id_afp',
            'periodo' => 'required|date', // ahora acepta Y-m-d
            'monto_deuda' => 'required|numeric|min:0',
        ]);

        $fechaPeriodo = Carbon::parse($validated['periodo']);
        $fechaVencimiento = DeudaAfp::calcularVencimiento($fechaPeriodo);

        DeudaAfp::create([
            'id_empresa_cliente' => $validated['id_empresa_cliente'],
            'id_afp' => $validated['id_afp'],
            'periodo' => $fechaPeriodo->toDateString(),
            'monto_deuda' => $validated['monto_deuda'],
            'fecha_vencimiento' => $fechaVencimiento->toDateString(),
            'estado' => 'pendiente',
            'id_usuario_creador' => Auth::id(),
            'total' => $validated['monto_deuda']
        ]);

        return back()->with('success', 'Deuda AFP registrada correctamente.');
    }



    public function destroy($id)
    {
        $deuda = DeudaAfp::findOrFail($id);
        $deuda->delete();
        return back()->with('success', 'Deuda eliminada correctamente.');
    }

    public function show($empresaId, $anio, $mes)
    {
        $empresa = \App\Models\EmpresasClientes::findOrFail($empresaId);

        $fechaPeriodo = Carbon::create($anio, $mes, 1);
        $vencimientoMes = DeudaAfp::calcularVencimiento($fechaPeriodo);

        $rows = DeudaAfp::with(['afp', 'empresa', 'creador', 'pagador'])
            ->where('id_empresa_cliente', $empresaId)
            ->whereRaw("DATE_FORMAT(periodo, '%Y-%m') = ?", ["$anio-" . str_pad($mes, 2, '0', STR_PAD_LEFT)])
            ->orderBy('id_deuda_afp', 'desc')
            ->get()
            ->map(function ($d) {
                $vencRaw = $d->fecha_vencimiento ? Carbon::parse($d->fecha_vencimiento) : null;
                $pagoRaw = $d->fecha_pago ? Carbon::parse($d->fecha_pago) : null;

                return [
                    'id_deuda' => $d->id_deuda_afp,
                    'afp' => $d->afp->nombre,
                    'id_afp' => $d->id_afp,
                    'monto_deuda' => $d->monto_deuda,
                    'interes' => $d->intereses ?? 0,
                    'total' => $d->total,

                    // para mostrar
                    'fecha_venc' => $vencRaw ? $vencRaw->format('d/m/Y') : '—',
                    'fecha_pago' => $pagoRaw ? $pagoRaw->format('d/m/Y') : '—',

                    // para inputs
                    'fecha_venc_ymd' => $vencRaw ? $vencRaw->format('Y-m-d') : '',
                    'fecha_pago_ymd' => $pagoRaw ? $pagoRaw->format('Y-m-d') : '',

                    'observaciones' => $d->observaciones,
                    'estado' => ucfirst($d->estado),
                    'autor_deuda' => optional($d->creador)->name ?? '—',
                    'pagador' => optional($d->pagador)->name ?? '—',
                ];
            });


        $totales = [
            'deuda' => $rows->sum('monto_deuda'),
            'interes' => $rows->sum('interes'),
            'total' => $rows->sum('total'),
        ];

        $afps = \App\Models\TipoAfp::where('estado', 1)->orderBy('nombre')->get();

        return view('deuda_afp.detalle', compact(
            'empresa',
            'anio',
            'mes',
            'rows',
            'vencimientoMes',
            'totales',
            'afps'
        ));
    }



    public function registrarPago(Request $request)
    {
        $data = $request->validate([
            'id_deuda' => 'required|exists:deuda_afp,id_deuda_afp',
            'monto_total' => 'required|numeric|min:0',
            'interes' => 'nullable|numeric|min:0',
            'fecha_pago' => 'required|date',
        ]);

        $deuda = DeudaAfp::findOrFail($data['id_deuda']);

        // Calcular totales
        $interes = $data['interes'] ?? 0;
        $totalPagado = $data['monto_total'];

        $deuda->update([
            'intereses' => $interes,
            'total' => $totalPagado,
            'fecha_pago' => $data['fecha_pago'],
            'estado' => 'pagada',
            'id_usuario_pagador' => Auth::id(),
        ]);

        return back()->with('success', 'Pago AFP registrado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $deuda = DeudaAfp::findOrFail($id);

        $validated = $request->validate([
            'id_afp' => 'required|exists:tipo_afp,id_afp',
            'monto_deuda' => 'required|numeric|min:0',
            'monto_total' => 'required|numeric|min:0',
            'interes' => 'nullable|numeric|min:0',
            'fecha_pago' => 'nullable|date',
            'observaciones' => 'nullable|string|max:255',
        ]);

        $deudaMonto = (float) $validated['monto_deuda'];
        $total = (float) $validated['monto_total'];
        $interesIn = $validated['interes'] ?? null;
        $fechaPago = $validated['fecha_pago'] ?? null;

        // Regla 1: si NO hay fecha de pago pero SÍ mandan interes>0 o total>0 => marcar pago hoy
        $haySenalDePago = ($interesIn !== null && (float) $interesIn > 0) || $total > 0;
        if (!$fechaPago && $haySenalDePago) {
            $fechaPago = now()->toDateString();
        }

        // Regla 2: si hay (o se infiere) fecha de pago => exigir total >= deuda
        if ($fechaPago) {
            if ($total < $deudaMonto) {
                return back()
                    ->withErrors(['monto_total' => 'El total pagado no puede ser menor que la deuda.'])
                    ->withInput();
            }
        } else {
            // Sin fecha de pago: forzar total e interés a 0 (coherencia con la UI)
            $total = 0.0;
            $interesIn = 0.0;
        }

        // Recalcular interés de forma canónica
        $interes = max(0, ($fechaPago ? ($total - $deudaMonto) : 0));

        // Determinar estado e id_usuario_pagador
        $nuevoEstado = $deuda->estado;
        $idPagador = $deuda->id_usuario_pagador;

        if ($fechaPago && $total > 0) {
            $nuevoEstado = 'pagada';
            $idPagador = Auth::id();
        } elseif (!$fechaPago) {
            $nuevoEstado = 'pendiente';
            $idPagador = null;
        }

        $deuda->update([
            'id_afp' => $validated['id_afp'],
            'monto_deuda' => $deudaMonto,
            'intereses' => $interes,
            'total' => $total,
            'fecha_pago' => $fechaPago,                // puede ser hoy si se infirió pago
            'observaciones' => $validated['observaciones'] ?? null,
            'estado' => $nuevoEstado,
            'id_usuario_pagador' => $idPagador,
        ]);

        return back()->with('success', 'Deuda AFP actualizada correctamente.');
    }

    public function generarPDF(Request $request)
    {
        $empresaId = $request->input('id_empresa_cliente');
        $mesHasta = (int) $request->input('mes');
        $estado = $request->input('estado', 'ambos');

        if (!$empresaId) {
            return back()->with('error', 'Debe seleccionar una empresa.');
        }

        $empresa = \App\Models\EmpresasClientes::findOrFail($empresaId);

        $anioActual = now()->year;

        // Limite de período hasta
        $periodoHasta = \Carbon\Carbon::create($anioActual, $mesHasta, 1)->endOfMonth();

        $query = \App\Models\DeudaAfp::with(['empresa', 'afp'])
            ->where('id_empresa_cliente', $empresaId)
            ->whereDate('periodo', '<=', $periodoHasta);

        if ($estado === 'pendientes') {
            $query->where('estado', 'pendiente');
        } elseif ($estado === 'pagados') {
            $query->where('estado', 'pagada');
        }

        $deudas = $query->orderBy('periodo', 'asc')->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.afp', [
            'deudas' => $deudas,
            'empresa' => $empresa,
            'periodoHasta' => $periodoHasta->translatedFormat('F Y'),
            'fechaHoy' => now()->format('d/m/Y'),
            'estado' => ucfirst($estado),
        ])->setPaper('a4', 'portrait');

        $nombre = 'REPORTE_AFP_' . strtoupper(str_replace(' ', '_', $empresa->razon_social)) . '.pdf';
        return $pdf->stream($nombre);
    }
}
