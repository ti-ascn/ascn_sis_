<?php
namespace App\Http\Controllers;

use App\Models\Deuda;
use App\Models\EmpresasClientes;
use App\Models\PagoDeuda;
use App\Models\Tributo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\DeudasExport;

class DeudaController extends Controller
{
    public function index(Request $request, $tipo)
    {
        $anio = (int) $request->input('anio', now()->year);
        $data = $this->getDeudaFilaData($tipo, $anio);

        return view('deudas.index', array_merge($data, [
            'anio' => $anio,
            'tipo' => $tipo,
        ]));
    }

    public function export(Request $request, $tipo)
    {
        $anio = (int) $request->input('anio', now()->year);
        $data = $this->getDeudaFilaData($tipo, $anio);
        $fileName = 'DEUDAS_' . strtoupper($tipo) . '_' . $anio . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new DeudasExport($data['filas']), $fileName);
    }

    private function getDeudaFilaData($tipo, $anio)
    {
        // Empresas activas
        $empresasQuery = EmpresasClientes::select(
            'id_empresas_clientes',
            'estado',
            'estado_actual',
            'estado_recursos',
            'razon_social',
            'ruc',
            'planilla'
        )->whereIn('estado_actual', [1, 2]);

        if ($tipo === 'recursos') {
            $empresasQuery->where('planilla', 1);
        }

        $empresas = $empresasQuery->orderBy('razon_social')->get();

        $deudas = Deuda::with(['tributo', 'pagos'])
            ->where(function ($query) use ($anio) {
                $query->where(function ($q) use ($anio) {
                    $q->whereYear('fecha_vencimiento', $anio)
                        ->whereMonth('fecha_vencimiento', '!=', 1);
                })
                    ->orWhere(function ($q) use ($anio) {
                        $q->whereYear('fecha_vencimiento', $anio + 1)
                            ->whereMonth('fecha_vencimiento', 1);
                    });
            })
            ->whereHas('tributo', function ($q) use ($tipo) {
                $q->where('tipo', $tipo)->where('estado', 1);
            })
            ->get();

        $hoy = Carbon::now()->startOfDay();

        $agregado = collect($deudas)
            ->groupBy([
                'id_empresa_cliente',
                function ($item) {
                    return Carbon::parse($item->fecha_vencimiento)->subMonth()->month;
                }
            ])
            ->map(function ($meses, $empresaId) use ($hoy) {
                return collect($meses)->map(function ($deudasMes) use ($hoy) {
                    $totalPendiente = 0;
                    $totalPagado = 0;
                    $todasPagadas = true;

                    foreach ($deudasMes as $d) {
                        $principal = (float) $d->monto_deuda;
                        $pagadoPrincipal = (float) $d->pagos->sum('monto');
                        $interesPagado = (float) $d->pagos->sum('interes');

                        if ($d->estado == 1 || $pagadoPrincipal >= $principal) {
                            $totalPagado += ($principal + $interesPagado);
                            continue;
                        }

                        $todasPagadas = false;
                        $saldoPrincipal = max(0, $principal - $pagadoPrincipal);
                        $fechaVcto = Carbon::parse($d->fecha_vencimiento)->addDay()->startOfDay();
                        $dias = $hoy->lt($fechaVcto) ? 0 : $fechaVcto->diffInDays($hoy) + 1;
                        $interesPendiente = round($principal * 0.0003 * $dias, 0, PHP_ROUND_HALF_UP);
                        $totalPendiente += ($saldoPrincipal + $interesPendiente);
                    }

                    return [
                        'total_mes' => $todasPagadas ? $totalPagado : $totalPendiente,
                        'estado' => $todasPagadas ? 'Pagado' : 'Pendiente',
                    ];
                });
            });

        $filas = [];
        foreach ($empresas as $e) {
            $fila = [
                'id' => $e->id_empresas_clientes,
                'estado' => $e->estado,
                'estado_actual' => $e->estado_actual,
                'estado_recursos' => $e->estado_recursos,
                'ud' => substr($e->ruc, -1),
                'razon' => $e->razon_social,
                'ruc' => $e->ruc,
                'vencimiento_pdt' => null,
                'meses' => []
            ];

            // Vencimiento PDT logic
            $mesDeclaracionActual = now()->format('Y-m');
            $calPDT = \App\Models\CalendarioVencimiento::where('tipo', 'PDT')
                ->where('mes', $mesDeclaracionActual)
                ->where('estado', 1)
                ->first();

            $fechas = $calPDT ? json_decode($calPDT->fechas, true) : [];
            $dia = $fechas[$fila['ud']] ?? null;

            $fila['vencimiento_pdt'] = $dia
                ? Carbon::createFromDate(now()->year, now()->month, 1)
                    ->addMonth()
                    ->setDay((int) $dia)
                    ->format('Y-m-d')
                : null;

            for ($m = 1; $m <= 12; $m++) {
                $totalMes = 0;
                $estadoMes = '—';

                if (isset($agregado[$e->id_empresas_clientes][$m])) {
                    $datosMes = $agregado[$e->id_empresas_clientes][$m];
                    $totalMes = (int) $datosMes['total_mes'];
                    $estadoMes = $datosMes['estado'];
                }

                $fila['meses'][$m] = [
                    'total' => $totalMes,
                    'estado' => $estadoMes
                ];
            }

            $filas[] = $fila;
        }

        $tributos = Tributo::where('estado', 1)
            ->where('tipo', $tipo)
            ->orderBy('nombre')
            ->get(['id_tributo', 'nombre', 'codigo']);

        return [
            'filas' => $filas,
            'tributos' => $tributos,
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_empresa_cliente' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'id_tributo' => 'required|exists:tributos,id_tributo',
            'monto_deuda' => 'required|integer|min:0',
            'fecha_vencimiento' => 'required|date',
            'observaciones' => 'nullable|string',
        ]);

        $fecha = Carbon::parse($data['fecha_vencimiento']);
        $mes = $fecha->month;
        $anio = $fecha->year;

        // Evitar duplicados por (empresa, tributo, mes, año)
        $duplicada = Deuda::where('id_empresa_cliente', $data['id_empresa_cliente'])
            ->where('id_tributo', $data['id_tributo'])
            ->whereMonth('fecha_vencimiento', $mes)
            ->whereYear('fecha_vencimiento', $anio)
            ->exists();

        if ($duplicada) {
            return back()->with('error', 'Ya existe una deuda para ese tributo en el mismo mes/año para esta empresa.');
        }

        // Crear la deuda
        $deuda = Deuda::create([
            'id_empresa_cliente' => $data['id_empresa_cliente'],
            'id_usuario' => auth()->id(),
            'id_tributo' => $data['id_tributo'],
            'monto_deuda' => $data['monto_deuda'],
            'estado' => 0, // Por defecto pendiente
            'pago_puntual' => 0,
            'fecha_vencimiento' => $data['fecha_vencimiento'],
            'interes_calculado' => 0,
            'observaciones' => $data['observaciones'] ?? null,
        ]);

        // Si la deuda es 0, marcamos automáticamente como pagada
        if ((int) $data['monto_deuda'] === 0) {
            $deuda->estado = 2;
            $deuda->pago_puntual = 1;
            $deuda->save();

            // Crear registro automático en pagos
            \App\Models\PagoDeuda::create([
                'id_deuda' => $deuda->id_deuda,
                'id_usuario' => auth()->id(),
                'monto' => 0,
                'interes' => 0,
                'monto_total' => 0,
                'fecha_pago' => $data['fecha_vencimiento'],
            ]);
        }

        return back()->with('success', 'Deuda creada correctamente.');
    }


    public function detalle(Request $request, $tipo, $empresaId, $anio, $mes)
    {
        $empresa = EmpresasClientes::findOrFail($empresaId);

        // CORRECCIÓN: El "mes" en los parámetros es el PERIODO DE DECLARACIÓN
        // Pero debemos buscar deudas que VENCEN en el MES SIGUIENTE
        $mesVencimiento = Carbon::createFromDate((int) $anio, (int) $mes, 1)
            ->addMonth() // ← Las deudas vencen el mes siguiente
            ->format('Y-m');

        // Buscar vencimiento PDT para este periodo
        $mesDeclaracion = Carbon::createFromDate((int) $anio, (int) $mes, 1)->format('Y-m');
        $calPDT = \App\Models\CalendarioVencimiento::where('tipo', 'PDT')
            ->where('mes', $mesDeclaracion)
            ->where('estado', 1)
            ->first()
            ?? \App\Models\CalendarioVencimiento::where('tipo', 'PDT')
                ->where('estado', 1)
                ->orderBy('mes', 'desc')
                ->first();

        $fechas = $calPDT ? json_decode($calPDT->fechas, true) : [];
        $ud = substr($empresa->ruc, -1);
        $dia = $fechas[$ud] ?? null;

        // El vencimiento PDT es para el mes SIGUIENTE al periodo
        $vencimientoMes = $dia
            ? Carbon::createFromDate((int) $anio, (int) $mes, 1)
                ->addMonth() // ← Vencimiento del mes siguiente
                ->setDay((int) $dia)
            : null;

        // Tipos de tributos
        $tipos = match ($tipo) {
            'contabilidad' => ['contabilidad'],
            'recursos' => ['recursos'],
            'ambas' => ['contabilidad', 'recursos'],
            default => ['contabilidad'],
        };

        // CORRECCIÓN: Buscar deudas que VENCEN en el mes siguiente
        $deudas = Deuda::with(['tributo', 'pagos' => fn($q) => $q->orderBy('fecha_pago', 'asc')])
            ->where('id_empresa_cliente', $empresaId)
            ->whereYear('fecha_vencimiento', Carbon::parse($mesVencimiento . '-01')->year)
            ->whereMonth('fecha_vencimiento', Carbon::parse($mesVencimiento . '-01')->month)
            ->whereHas('tributo', fn($q) => $q->whereIn('tipo', $tipos)->where('estado', 1))
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        $rows = [];
        $totDeuda = $totInteresPagado = $totInteresPendiente = $totInteresTotal = $totPagado = $totPrincipalPend = 0;
        foreach ($deudas as $d) {
            $principal = (int) $d->monto_deuda;
            $fechaVcto = Carbon::parse($d->fecha_vencimiento);

            $sumPrincipalPagado = (int) $d->pagos->sum('monto');
            $sumInteresPagado = (int) $d->pagos->sum('interes');
            $pagadoTotal = (int) $d->pagos->sum('monto_total');

            $ultimoPago = $d->pagos->last();
            $fechaUltPago = $ultimoPago ? Carbon::parse($ultimoPago->fecha_pago) : null;

            // Obtener el autor (solo primer nombre)
            $autorPago = null;
            if ($ultimoPago && $ultimoPago->usuario) {
                $autorPago = explode(' ', trim($ultimoPago->usuario->name))[0];
            }

            $autorDeuda = $d->usuario ? explode(' ', trim($d->usuario->name))[0] : null;


            $principalPend = max(0, $principal - $sumPrincipalPagado);

            // Calcular interés pendiente
            $interesPendiente = 0;

            if ($principalPend > 0) {
                $hoy = Carbon::now()->startOfDay();
                $desde = $fechaVcto->copy()->addDay()->startOfDay();
                $dias = $hoy->lt($desde) ? 0 : $desde->diffInDays($hoy) + 1;
                $interesPendiente = (int) round($principal * 0.0003 * $dias, 0, PHP_ROUND_HALF_UP);
            } else {
                $interesPendiente = (int) $d->interes_pendiente;
            }

            $interesTotalDeuda = $sumInteresPagado + $interesPendiente;
            $completo = ($principalPend === 0 && $interesPendiente === 0);

            $estadoTexto = match ($d->estado) {
                1 => 'Pagado',
                2 => 'Sin movimiento',
                default => ($completo ? 'Pagado' : 'Pendiente'),
            };

            // Detectar si es una deuda de constancia rectificada
            $esRectificado = !empty($d->observaciones) &&
                preg_match('/Constancia.*Rectificad/i', $d->observaciones);
            $nombreTributo = $d->tributo?->nombre . ' (' . $d->tributo?->codigo . ')';
            if ($esRectificado) {
                $nombreTributo .= ' (recti)';
            }

            $rows[] = [
                'id_deuda' => $d->id_deuda,
                'tributo' => $nombreTributo,
                'monto_deuda' => $principal,
                'pagado_principal' => $sumPrincipalPagado,
                'interes_pagado' => $sumInteresPagado,
                'interes_restante' => $interesPendiente,
                'fecha_vcto' => $fechaVcto->format('d/m/Y'),
                'fecha_pago' => $fechaUltPago ? $fechaUltPago->format('d/m/Y') : '—',
                'pagado_total' => $pagadoTotal,
                'estado' => $estadoTexto,
                'pendiente_rojo' => !$completo && $d->estado != 2,
                'observaciones' => $d->observaciones,
                'autor_pago' => $autorPago ?? '—',
                'autor_deuda' => $autorDeuda ?? '—',
            ];

            $totDeuda += $principal;
            $totInteresPagado += $sumInteresPagado;
            $totInteresPendiente += $interesPendiente;
            $totInteresTotal += $interesTotalDeuda;
            $totPagado += $pagadoTotal;
            $totPrincipalPend += $principalPend;
        }

        // Buscar todos los tributos activos del tipo correspondiente
        $tributos = Tributo::where('estado', 1)
            ->whereIn('tipo', $tipos)
            ->orderBy('nombre')
            ->get(['id_tributo', 'nombre', 'codigo']);

        // Obtener los tributos ya registrados como deuda en ese mes
        $tributosConDeuda = Deuda::where('id_empresa_cliente', $empresaId)
            ->whereYear('fecha_vencimiento', Carbon::parse($mesVencimiento . '-01')->year)
            ->whereMonth('fecha_vencimiento', Carbon::parse($mesVencimiento . '-01')->month)
            ->pluck('id_tributo')
            ->toArray();

        // Filtrar para mostrar solo los tributos que aún no tienen deuda
        $tributos = $tributos->reject(fn($t) => in_array($t->id_tributo, $tributosConDeuda))->values();

        // Buscar la determinación mensual correspondiente al periodo
        $determinacion = \App\Models\DeterminacionMensual::where('id_empresas_clientes', $empresaId)
            ->whereYear('fecha', (int) $anio)
            ->whereMonth('fecha', (int) $mes)
            ->first();

        return view('deudas.detalle', [
            'empresa' => $empresa,
            'anio' => (int) $anio,
            'mes' => (int) $mes,
            'vencimientoMes' => $vencimientoMes,
            'tributos' => $tributos,
            'rows' => $rows,
            'tipo' => $tipo,
            'totales' => [
                'deuda' => $totDeuda,
                'interes' => $totInteresTotal,
                'pagado' => $totPagado,
                'saldo' => $totPrincipalPend + $totInteresPendiente,
                'interes_pagado' => $totInteresPagado,
                'interes_pendiente' => $totInteresPendiente,
            ],
            'determinacion' => $determinacion,
        ]);
    }

    public function destroy($id_deuda)
    {
        DB::transaction(function () use ($id_deuda) {
            // Borra primero los pagos relacionados
            PagoDeuda::where('id_deuda', $id_deuda)->delete();

            // Luego borra la deuda
            Deuda::where('id_deuda', $id_deuda)->delete();
        });

        return back()->with('success', 'Deuda y pagos eliminados correctamente.');
    }

    public function generarPDFDetalle($tipo, $empresaId, $anio, $mes)
    {
        $empresa = EmpresasClientes::findOrFail($empresaId);
        $hoy = Carbon::now()->locale('es')->translatedFormat('d \\de F \\de Y');

        // ← Ajuste: el mes de vencimiento es el mes siguiente al periodo
        $mesVencimiento = Carbon::createFromDate((int) $anio, (int) $mes, 1)->addMonth();

        $tipos = match ($tipo) {
            'contabilidad' => ['contabilidad'],
            'recursos' => ['recursos'],
            'ambas' => ['contabilidad', 'recursos'],
            default => ['contabilidad'],
        };

        $deudas = Deuda::with([
            'tributo',
            'pagos' => function ($q) {
                $q->orderBy('fecha_pago', 'asc');
            }
        ])
            ->where('id_empresa_cliente', $empresaId)
            ->whereYear('fecha_vencimiento', $mesVencimiento->year)
            ->whereMonth('fecha_vencimiento', $mesVencimiento->month)
            ->whereHas('tributo', fn($q) => $q->whereIn('tipo', $tipos)->where('estado', 1))
            ->get()
            ->map(function ($d) {
                $sumPrincipalPagado = (int) $d->pagos->sum('monto');
                $sumInteresPagado = (int) $d->pagos->sum('interes');
                $pagadoTotal = (int) $d->pagos->sum('monto_total');

                $principalPendiente = max(0, (int) $d->monto_deuda - $sumPrincipalPagado);
                $desde = $d->fecha_vencimiento ? Carbon::parse($d->fecha_vencimiento)->addDay() : null;
                $ultimoPago = $d->pagos->last();
                $hasta = $principalPendiente > 0 ? Carbon::now() : ($ultimoPago ? Carbon::parse($ultimoPago->fecha_pago) : Carbon::now());

                $interesDevengado = 0;
                if ($d->estado == 1) {
                    $interesDevengado = $sumInteresPagado;
                } elseif ($desde) {
                    $dias = $hasta->lt($desde) ? 0 : $desde->diffInDays($hasta) + 1;
                    $interesDevengado = (int) round($d->monto_deuda * 0.0003 * $dias, 0, PHP_ROUND_HALF_UP);
                }

                $interesPendiente = max(0, $interesDevengado - $sumInteresPagado);
                $d->interes_total = $interesDevengado;
                $d->monto_pagado = $pagadoTotal;
                $d->saldo_total = $principalPendiente + $interesPendiente;

                return $d;
            });

        $periodo = strtoupper(Carbon::createFromDate($anio, $mes, 1)->locale('es')->monthName) . " $anio";

        // Buscar vencimiento PDT igual que en la vista detalle
        $mesDeclaracion = Carbon::createFromDate((int) $anio, (int) $mes, 1)->format('Y-m');
        $calPDT = \App\Models\CalendarioVencimiento::where('tipo', 'PDT')
            ->where('mes', $mesDeclaracion)
            ->where('estado', 1)
            ->first();

        $fechas = $calPDT ? json_decode($calPDT->fechas, true) : [];
        $ud = substr($empresa->ruc, -1);
        $dia = $fechas[$ud] ?? null;

        $vencimientoMes = $dia
            ? Carbon::createFromDate((int) $anio, (int) $mes, 1)
                ->addMonth()
                ->setDay((int) $dia)
                ->format('d/m/Y')
            : '—';


        $pdf = Pdf::loadView('pdf.deudas_detalle', [
            'empresa' => $empresa,
            'deudas' => $deudas,
            'periodo' => $periodo,
            'fechaHoy' => $hoy,
            'vencimientoMes' => $vencimientoMes,
        ])->setPaper('A4', 'portrait');

        $nombreArchivo = "DEUDAS_{$empresa->ruc}_{$periodo}.pdf";
        return $pdf->stream($nombreArchivo);
    }

    public function ajaxPagos($id)
    {
        $deuda = \App\Models\Deuda::with(['pagos.usuario'])->findOrFail($id);

        return response()->json([
            'tributo' => $deuda->tributo?->nombre . ' (' . $deuda->tributo?->codigo . ')',
            'monto_deuda' => number_format($deuda->monto_deuda, 2, '.', ','),
            'fecha_venc' => \Carbon\Carbon::parse($deuda->fecha_vencimiento)->format('d/m/Y'),
            'pagos' => $deuda->pagos->map(function ($p) {
                return [
                    'fecha' => \Carbon\Carbon::parse($p->fecha_pago)->format('d/m/Y'),
                    'monto' => number_format($p->monto, 2, '.', ','),
                    'interes' => number_format($p->interes, 2, '.', ','),
                    'total' => number_format($p->monto_total, 2, '.', ','),
                    'usuario' => $p->usuario?->name ?? '—',
                ];
            }),
        ]);
    }

    public function updateTributo(Request $request, $id)
    {
        $request->validate([
            'id_tributo' => 'required|exists:tributos,id_tributo',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $deuda = Deuda::findOrFail($id);

        $deuda->update([
            'id_tributo' => $request->id_tributo,
            'observaciones' => $request->observaciones,
            'id_usuario' => auth()->id(),
        ]);

        return back()->with('success', 'Tributo y observaciones actualizados correctamente.');
    }

    public function buscarDeudasAjax(Request $request, $tipo)
    {
        $id_empresa = $request->query('id_empresa');
        $fecha_desde = $request->query('fecha_desde'); // 'YYYY-MM'
        $fecha_hasta = $request->query('fecha_hasta'); // 'YYYY-MM'
        $estado = $request->query('estado', 'ambas'); // 'ambas', 'pendientes', 'pagadas'

        $query = Deuda::with(['empresa', 'tributo', 'pagos'])
            ->whereHas('tributo', function ($q) use ($tipo) {
                $q->where('tipo', $tipo)->where('estado', 1);
            });

        if ($id_empresa) {
            $query->where('id_empresa_cliente', $id_empresa);
        }

        if ($fecha_desde) {
            // El período es 1 mes anterior al vencimiento. Por tanto:
            // período >= fecha_desde  =>  vencimiento - 1 mes >= fecha_desde  =>  vencimiento >= fecha_desde + 1 mes
            $vencimientoMin = Carbon::parse($fecha_desde . '-01')->addMonth()->startOfMonth()->format('Y-m-d');
            $query->where('fecha_vencimiento', '>=', $vencimientoMin);
        }

        if ($fecha_hasta) {
            // período <= fecha_hasta  =>  vencimiento - 1 mes <= fecha_hasta  =>  vencimiento <= fecha_hasta + 1 mes
            $vencimientoMax = Carbon::parse($fecha_hasta . '-01')->addMonth()->endOfMonth()->format('Y-m-d');
            $query->where('fecha_vencimiento', '<=', $vencimientoMax);
        }

        $documentos = $query->orderBy('fecha_vencimiento', 'desc')->get();

        $hoy = Carbon::now()->startOfDay();
        $rows = [];

        foreach ($documentos as $d) {
            $principal = (float) $d->monto_deuda;
            $pagadoPrincipal = (float) $d->pagos->sum('monto');
            $principalPend = max(0, $principal - $pagadoPrincipal);

            // Calcular interés pendiente
            $interesPendiente = 0;
            if ($principalPend > 0) {
                $fechaVcto = Carbon::parse($d->fecha_vencimiento);
                $desde = $fechaVcto->copy()->addDay()->startOfDay();
                $dias = $hoy->lt($desde) ? 0 : $desde->diffInDays($hoy) + 1;
                $interesPendiente = (int) round($principal * 0.0003 * $dias, 0, PHP_ROUND_HALF_UP);
            } else {
                $interesPendiente = (int) $d->interes_pendiente;
            }

            $completo = ($principalPend === 0 && $interesPendiente === 0);

            $estadoTexto = match ((int)$d->estado) {
                1 => 'Pagado',
                2 => 'Sin movimiento',
                default => ($completo ? 'Pagado' : 'Pendiente'),
            };

            // Filtrar por estado si se especificó
            if ($estado === 'pendientes' && $estadoTexto !== 'Pendiente') {
                continue;
            }
            if ($estado === 'pagadas' && $estadoTexto !== 'Pagado' && $estadoTexto !== 'Sin movimiento') {
                continue;
            }

            // Calcular período de declaración correspondiente (un mes antes del vencimiento)
            $fechaV = Carbon::parse($d->fecha_vencimiento);
            $periodoMes = $fechaV->copy()->subMonth();
            $periodoLabel = strtoupper($periodoMes->locale('es')->isoFormat('MMMM YYYY'));

            // Calcular última fecha del pago
            $ultimoPago = $d->pagos->sortByDesc('fecha_pago')->first();
            $ultimaFechaPago = $ultimoPago ? Carbon::parse($ultimoPago->fecha_pago)->format('d/m/Y') : '—';

            // Calcular plazo (Dentro o Fuera)
            $fechaVcto = Carbon::parse($d->fecha_vencimiento);
            if ($estadoTexto === 'Pagado' || $estadoTexto === 'Sin movimiento') {
                if ($ultimoPago) {
                    $fechaPago = Carbon::parse($ultimoPago->fecha_pago)->startOfDay();
                    $plazo = $fechaPago->lte($fechaVcto->startOfDay()) ? 'Dentro' : 'Fuera';
                } else {
                    $plazo = 'Dentro';
                }
            } else {
                $plazo = $hoy->lte($fechaVcto->startOfDay()) ? 'Dentro' : 'Fuera';
            }

            $rows[] = [
                'id_deuda' => $d->id_deuda,
                'empresa' => $d->empresa ? $d->empresa->razon_social : '—',
                'ruc' => $d->empresa ? $d->empresa->ruc : '—',
                'tributo' => $d->tributo ? $d->tributo->nombre . ' (' . $d->tributo->codigo . ')' : '—',
                'monto_deuda' => $principal,
                'pagado_principal' => $pagadoPrincipal,
                'interes_pendiente' => $interesPendiente,
                'saldo' => $principalPend + $interesPendiente,
                'fecha_vcto' => $fechaV->format('d/m/Y'),
                'fecha_pago_ult' => $ultimaFechaPago,
                'plazo' => $plazo,
                'periodo' => $periodoLabel,
                'estado' => $estadoTexto,
                'url_detalle' => route('deudas.detalle', [
                    'tipo' => $tipo,
                    'empresa' => $d->id_empresa_cliente,
                    'anio' => $periodoMes->year,
                    'mes' => $periodoMes->month,
                ])
            ];
        }

        return response()->json([
            'success' => true,
            'rows' => $rows
        ]);
    }
}