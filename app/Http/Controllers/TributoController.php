<?php

namespace App\Http\Controllers;

use App\Models\Tributo;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Deuda;
use App\Models\EmpresasClientes;

class TributoController extends Controller
{
    public function index()
    {
        $tributos = Tributo::all();
        return view('tributos.index', compact('tributos'));
    }

    public function create()
    {
        return view('tributos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:50|unique:tributos,codigo',
            'tipo' => 'required|in:contabilidad,recursos',
            'estado' => 'required|boolean',
        ]);

        // Guardamos el tributo
        Tributo::create($validated);

        return redirect()->route('tributos.index')->with('success', 'Tributo creado con éxito.');
    }

    public function edit($id_tributo)
    {
        $tributo = Tributo::findOrFail($id_tributo);
        return view('tributos.edit', compact('tributo'));
    }

    public function update(Request $request, $id_tributo)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => "required|string|max:50|unique:tributos,codigo,$id_tributo,id_tributo",
            'tipo' => 'required|in:contabilidad,recursos',
            'estado' => 'required|boolean',
        ]);

        $tributo = Tributo::findOrFail($id_tributo);
        $tributo->update($validated);

        return redirect()->route('tributos.index')->with('success', 'Tributo actualizado con éxito.');
    }

    public function destroy($id_tributo)
    {
        $tributo = Tributo::findOrFail($id_tributo);
        $tributo->delete();
        return redirect()->route('tributos.index')->with('success', 'Tributo eliminado con éxito.');
    }

    public function generar(Request $r)
    {
        $empresa = EmpresasClientes::findOrFail($r->id_empresa_cliente);
        $anio = $r->input('anio', now()->year);
        $periodoHasta = (int) $r->mes;
        $tipo = $r->tipo;
        $estado = $r->estado;
        $hoy = Carbon::today();

        // Rango de fechas para la consulta:
        // Inicio: 1 de Febrero del año seleccionado (Enero vence en Diciembre del año anterior, no se incluye)
        // Fin: Fin del mes siguiente al periodo seleccionado (Ej: Periodo Enero -> Vence Febrero)
        $fechaInicio = Carbon::createFromDate($anio, 2, 1)->startOfMonth();
        $fechaFin = Carbon::createFromDate($anio, $periodoHasta, 1)->addMonth()->endOfMonth();

        $deudas = Deuda::with(['tributo', 'pagos' => fn($q) => $q->orderBy('fecha_pago', 'asc')])
            ->where('id_empresa_cliente', $empresa->id_empresas_clientes)
            ->whereBetween('fecha_vencimiento', [$fechaInicio->format('Y-m-d'), $fechaFin->format('Y-m-d')])
            ->get()
            ->filter(function ($d) use ($tipo, $estado) {
                // filtra por tipo de tributo
                if ($tipo !== 'ambas' && (!$d->tributo || $d->tributo->tipo !== $tipo)) {
                    return false;
                }

                // filtra por estado de deuda
                if ($estado === 'pendientes' && $d->estado != 0) { // solo pendientes
                    return false;
                }
                if ($estado === 'pagados' && $d->estado != 1) { // solo pagados
                    return false;
                }

                return true;
            })
            ->map(function ($d) use ($hoy) {
                $sumPrincipalPagado = (int) $d->pagos->sum('monto');
                $sumInteresPagado = (int) $d->pagos->sum('interes');
                $pagadoTotal = (int) $d->pagos->sum('monto_total');

                // 1) Principal pendiente
                $principalPendiente = max(0, (int) $d->monto_deuda - $sumPrincipalPagado);

                // 2) Fechas para interés
                $desde = $d->fecha_vencimiento
                    ? Carbon::parse($d->fecha_vencimiento)->addDay()->startOfDay()
                    : null;

                // si hay principal pendiente -> corta en hoy; si no -> corta en fecha del último pago
                $ultimoPago = $d->pagos->last();
                $hasta = $principalPendiente > 0
                    ? $hoy->copy()->startOfDay()
                    : ($ultimoPago ? Carbon::parse($ultimoPago->fecha_pago)->startOfDay() : $hoy->copy()->startOfDay());

                // 3) Interés devengado sobre la DEUDA ORIGINAL
                $interesDevengado = 0;

                if ($d->estado == 1) {
                    // Si la deuda ya está pagada, el interés devengado es lo efectivamente pagado
                    $interesDevengado = (int) $sumInteresPagado;
                } else {
                    if ($desde) {
                        $dias = $hasta->lt($desde) ? 0 : $desde->diffInDays($hasta) + 1;
                        $interesDevengado = (int) round(((int) $d->monto_deuda) * 0.0003 * $dias, 0, PHP_ROUND_HALF_UP);
                    }
                }

                // 4) Interés pendiente (lo que falta de ese devengado)
                $interesPendiente = max(0, $interesDevengado - $sumInteresPagado);

                // 5) Montos para el PDF
                $d->interes_total = $interesDevengado;                           // total devengado a la fecha de corte
                $d->monto_pagado = $pagadoTotal;                                // historial
                $d->monto_pendiente = $principalPendiente + $interesPendiente;     // saldo real hoy
                $d->periodo = $d->fecha_vencimiento
                    ? Carbon::parse($d->fecha_vencimiento)->subMonth()->locale('es')->translatedFormat('F Y')
                    : null;
                $d->detalle = $ultimoPago ? Carbon::parse($ultimoPago->fecha_pago)->format('d/m/Y') : '';

                return $d;
            });

        $deudas = $deudas->sortBy(function ($d) {
            return Carbon::parse($d->fecha_vencimiento);
        });

        $periodoTexto = strtoupper(Carbon::create()->month($periodoHasta)->locale('es')->monthName) . ' ' . $anio;

        $pdf = Pdf::loadView('pdf.tributos', [
            'empresa' => $empresa,
            'deudas' => $deudas,
            'periodoHasta' => $periodoTexto,
            'fechaHoy' => $hoy->locale('es')->translatedFormat('d \\de F \\de Y'),
        ])->setPaper('A4', 'portrait');

        $periodoNombre = strtoupper(Carbon::create()->month($periodoHasta)->locale('es')->monthName);
        $anioPeriodo = $anio;
        $timestamp = now()->format('Ymd_His'); // para diferenciar cada archivo

        $nombreArchivo = "TRIBUTOS__{$empresa->razon_social}_TIPO_{$tipo}_ESTADO_{$estado}_HASTA_PERIODO_{$periodoNombre}_{$anioPeriodo}_{$timestamp}.pdf";

        return $pdf->stream($nombreArchivo);
    }
}
