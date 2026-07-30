<?php

namespace App\Http\Controllers;

use App\Models\DeudaRecepcion;
use App\Models\EmpresasClientes;
use App\Models\CotizacionRecepcion;
use Illuminate\Http\Request;
use PDF;

class DeudaRecepcionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $tipo = 'contable')
    {
        // Determinar tipos de cotización a filtrar
        $tiposCotizacion = match ($tipo) {
            'contable' => ['contable'],
            'otros' => ['otros'],
            'ambas' => ['contable', 'otros'],
            default => ['contable'],
        };

        // Empresas con su última cotización y deudas filtradas por tipo
        $empresas = EmpresasClientes::select(
            'id_empresas_clientes',
            'razon_social',
            'ruc',
            'estado_actual',
            'estado_servicio_recepcion'
        )->with([
                    'cotizaciones' => function ($query) use ($tiposCotizacion) {
                        $query->whereIn('tipo_cotizacion', $tiposCotizacion)
                            ->latest('created_at')->limit(1);
                    },
                    'cotizaciones.deudas' => function ($query) {
                        $query->with('pagos');
                    },
                    // Cargar todas las cotizaciones para calcular totales
                    'todasCotizaciones' => function ($query) use ($tiposCotizacion) {
                        $query->whereIn('tipo_cotizacion', $tiposCotizacion)
                            ->with([
                                'deudas' => function ($subQuery) {
                                    $subQuery->with('pagos');
                                }
                            ]);
                    }
                ])
            ->whereIn('estado_actual', [1, 2, 0])
            ->where('estado', ['C1'])
            ->orderBy('razon_social')
            ->get();

        // Obtener fechas de referencia
        $mesPasado = now()->startOfMonth()->subMonths(1)->format('Y-m');
        $mesActual = now()->startOfMonth()->format('Y-m');

        // Calcular totales para cada empresa
        foreach ($empresas as $empresa) {
            $saldoPendienteMesPasado = 0;
            $pagoPendienteMesActual = 0;
            $totalDeuda = 0;
            $totalDeudaPendienteEmpresa = 0;

            // Usar todas las cotizaciones para calcular totales
            if ($empresa->todasCotizaciones->isNotEmpty()) {
                foreach ($empresa->todasCotizaciones as $cotizacion) {
                    foreach ($cotizacion->deudas as $deuda) {
                        $totalDeuda += $deuda->monto_deuda;
                        $totalDeudaPendienteEmpresa += $deuda->monto_pendiente;

                        // Extraer año-mes de la fecha de declaración
                        $mesDeclaracionDeuda = date('Y-m', strtotime($deuda->mes_declaracion));

                        // Saldo pendiente: deudas desde el inicio hasta el mes pasado (inclusive), y todas las 'anual'
                        if ($mesDeclaracionDeuda <= $mesPasado || $deuda->tipo_declaracion === 'anual') {
                            $saldoPendienteMesPasado += $deuda->monto_pendiente;
                        }

                        // Pago pendiente: solo deudas del mes actual que sean 'mensual'
                        if ($mesDeclaracionDeuda === $mesActual && $deuda->tipo_declaracion !== 'anual') {
                            $pagoPendienteMesActual += $deuda->monto_pendiente;
                        }
                    }
                }
            }

            $empresa->total_saldo_pendiente = $saldoPendienteMesPasado;
            $empresa->total_pago_pendiente = $pagoPendienteMesActual;
            $empresa->total_deuda = $totalDeuda;
            $empresa->total_deuda_pendiente = $totalDeudaPendienteEmpresa;
        }

        // Todas las empresas activas para el modal
        $todasEmpresas = EmpresasClientes::select(
            'id_empresas_clientes',
            'razon_social',
            'ruc'
        )->whereIn('estado_actual', [1, 2])
            ->orderBy('razon_social')
            ->get();

        // Calcular total de deuda pendiente
        $totalDeudaPendiente = $empresas->sum(function ($empresa) {
            return $empresa->total_deuda_pendiente;
        });

        return view('deudas_recepcion.index', compact('empresas', 'todasEmpresas', 'tipo', 'totalDeudaPendiente'));
    }

    /**
     * Display lista cotizaciones view.
     */
    public function lista()
    {
        $cotizaciones = CotizacionRecepcion::with([
            'empresaCliente:id_empresas_clientes,ruc,razon_social',
            'deudas'
        ])
            ->get()
            ->map(function ($cotizacion) {
                $totalDeudas = $cotizacion->deudas->count();
                $montoPendiente = $cotizacion->deudas->sum('monto_pendiente');

                return [
                    'id_cotizacion' => $cotizacion->id_cotizacion,
                    'ruc' => $cotizacion->empresaCliente->ruc ?? 'N/A',
                    'razon_social' => $cotizacion->empresaCliente->razon_social ?? 'N/A',
                    'nro_cotizacion' => $cotizacion->nro_cotizacion,
                    'tipo_cotizacion' => ucfirst($cotizacion->tipo_cotizacion),
                    'total_deudas' => $totalDeudas,
                    'monto_pendiente' => $montoPendiente
                ];
            });

        return view('deudas_recepcion.lista', compact('cotizaciones'));
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
        $request->validate([
            'id_cotizacion' => 'required|exists:cotizacion_recepcion,id_cotizacion',
            'tipo_declaracion' => 'required|in:mensual,anual',
            'mes_declaracion' => 'required_if:tipo_declaracion,mensual|nullable|string',
            'anio_declaracion' => 'required_if:tipo_declaracion,anual|nullable|integer|min:2000|max:2100',
            'nro_factura' => 'nullable|string|max:255',
            'descripcion_servicio' => 'nullable|string|max:255',
            'monto_deuda' => 'nullable|numeric|min:0',
            'archivo_pdf' => 'nullable|mimes:pdf|max:10240',
            'nro_trabajadores' => 'nullable|integer|min:0',
            'precio_trabajador' => 'nullable|numeric|min:0',
            'monto_contabilidad' => 'nullable|numeric|min:0',
            'sin_movimiento' => 'nullable|boolean'
        ]);

        if ($request->tipo_declaracion === 'anual') {
            $anio = $request->anio_declaracion;

            // 👉 Año siguiente para ordenar
            $mesDeclaracion = ($anio + 1) . '-04-01';

            // Opcional (puedes mantenerlo así)
            $fechaFactura = ($anio + 1) . '-04-15';
        } else {
            $mesDeclaracion = $request->mes_declaracion . '-01';
            $fechaFactura = date('Y-m-d', strtotime($request->mes_declaracion . '-15 +1 month'));
        }

        $cotizacion = CotizacionRecepcion::find($request->id_cotizacion);
        $allowMultiple = false;

        if ($cotizacion) {
            // Permitir múltiples deudas por mes para el id_empresa_cliente = 138 o si es tipo 'otros'
            // Array para que a futuro solo se agreguen más IDs si se requiere
            $allowedEmpresasMultiplesDeudas = [138];

            if (in_array(strtolower($cotizacion->tipo_cotizacion), ['otros', 'otro'])) {
                $allowMultiple = true;
            } elseif (in_array($cotizacion->id_empresa_cliente, $allowedEmpresasMultiplesDeudas)) {
                $allowMultiple = true;
            }
        }

        if (!$allowMultiple) {
            $existe = DeudaRecepcion::where('id_cotizacion', $request->id_cotizacion)
                ->where('mes_declaracion', $mesDeclaracion)
                ->exists();
                
            if ($existe) {
                return back()->with('error', 'Ya existe una deuda registrada para este mes y año en esta cotización.');
            }
        }

        $nro_trabajadores = $request->input('nro_trabajadores', 0);
        $precio_trabajador = $request->input('precio_trabajador', 0);
        $monto_contabilidad = $request->input('monto_contabilidad', 0);
        $sin_movimiento = $request->has('sin_movimiento') ? 1 : 0;
        
        $monto_recursos = $nro_trabajadores * $precio_trabajador;
        $monto_deuda = $monto_recursos + $monto_contabilidad;

        $archivo_pdf = null;
        if ($request->hasFile('archivo_pdf')) {
            $file = $request->file('archivo_pdf');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('facturas_deudas_recepcion'), $filename);
            $archivo_pdf = 'facturas_deudas_recepcion/' . $filename;
        }

        $id_usuario = auth()->id();
        $id_usuario_recursos = null;
        $ult_actualizacion_recursos = null;
        $id_usuario_contabilidad = null;
        $ult_actualizacion_contabilidad = null;

        // Determine Recursos Humanos fields
        if ($request->has('nro_trabajadores') || $request->has('precio_trabajador')) {
            $nroTrab = $request->input('nro_trabajadores');
            $precioTrab = $request->input('precio_trabajador');
            
            // Si no se recibe dato (es null o string vacío), lo forzamos a 0
            $nroTrabFinal = ($nroTrab === null || $nroTrab === '') ? 0 : $nroTrab;
            $precioTrabFinal = ($precioTrab === null || $precioTrab === '') ? 0 : $precioTrab;

            if ($nroTrabFinal == 0 && $precioTrabFinal == 0) {
                $id_usuario_recursos = null;
                $ult_actualizacion_recursos = null;
                $nro_trabajadores = 0;
                $precio_trabajador = 0;
            } else {
                $id_usuario_recursos = $id_usuario;
                $ult_actualizacion_recursos = now();
                $nro_trabajadores = $nroTrabFinal;
                $precio_trabajador = $precioTrabFinal;
            }
        }

        // Determine Contabilidad fields
        $sin_movimiento = $request->has('sin_movimiento') ? 1 : 0;
        if ($request->has('monto_contabilidad') || $request->has('sin_movimiento')) {
            $montoCont = $request->input('monto_contabilidad');
            if (empty($montoCont) && !$sin_movimiento) {
                $id_usuario_contabilidad = null;
                $ult_actualizacion_contabilidad = null;
                $monto_contabilidad = 0;
            } else {
                $id_usuario_contabilidad = $id_usuario;
                $ult_actualizacion_contabilidad = now();
                $monto_contabilidad = $montoCont ?? 0;
            }
        }

        DeudaRecepcion::create([
            'id_cotizacion' => $request->id_cotizacion,
            'mes_declaracion' => $mesDeclaracion,
            'tipo_declaracion' => $request->tipo_declaracion,
            'anio_declaracion' => $request->tipo_declaracion === 'anual' ? $request->anio_declaracion : null,
            'nro_factura' => $request->nro_factura,
            'fecha_factura' => $fechaFactura,
            'descripcion_servicio' => $request->descripcion_servicio,
            'monto_deuda' => $monto_deuda,
            'monto_pendiente' => $monto_deuda,
            'estado' => 0,
            'id_usuario' => $id_usuario,
            'archivo_pdf' => $archivo_pdf,
            'monto_recursos' => $monto_recursos,
            'nro_trabajadores' => $nro_trabajadores,
            'precio_trabajador' => $precio_trabajador,
            'monto_contabilidad' => $monto_contabilidad,
            'sin_movimiento' => $sin_movimiento,
            'ult_actualizacion_recursos' => $ult_actualizacion_recursos,
            'id_usuario_recursos' => $id_usuario_recursos,
            'ult_actualizacion_contabilidad' => $ult_actualizacion_contabilidad,
            'id_usuario_contabilidad' => $id_usuario_contabilidad
        ]);

        return back()->with('success', 'Deuda de recepción creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id, Request $request)
    {
        $tipo = $request->get('tipo', 'contable');

        // Determinar tipos de cotización a filtrar
        $tiposCotizacion = match ($tipo) {
            'contable' => ['contable'],
            'otros' => ['otros'],
            'ambas' => ['contable', 'otros'],
            default => ['contable'],
        };

        $empresa = EmpresasClientes::with([
            'todasCotizaciones' => function ($query) use ($tiposCotizacion) {
                $query->whereIn('tipo_cotizacion', $tiposCotizacion)
                    ->with([
                        'deudas' => function ($subQuery) {
                            $subQuery
                                ->orderBy('mes_declaracion', 'asc')
                                ->with([
                                    'pagos' => function ($pagoQuery) {
                                        $pagoQuery->orderBy('id_pago_deuda_recepcion', 'desc');
                                    }
                                ]);
                        }
                    ]);
            }
        ])->findOrFail($id);

        // Calcular total de deuda pendiente
        $totalDeudaPendiente = 0;

        foreach ($empresa->todasCotizaciones as $cotizacion) {
            foreach ($cotizacion->deudas as $deuda) {
                $totalDeudaPendiente += $deuda->monto_pendiente;
            }
        }

        return view('deudas_recepcion.detalles', compact('empresa', 'tipo', 'totalDeudaPendiente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DeudaRecepcion $deudaRecepcion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $deuda = DeudaRecepcion::findOrFail($id);

        $request->validate([
            'tipo_declaracion' => 'required|in:mensual,anual',
            'mes_declaracion' => 'required_if:tipo_declaracion,mensual|nullable|string',
            'anio_declaracion' => 'required_if:tipo_declaracion,anual|nullable|integer|min:2000|max:2100',
            'nro_factura' => 'nullable|string|max:255',
            'descripcion_servicio' => 'nullable|string|max:255',
            'monto_deuda' => 'nullable|numeric|min:0',
            'archivo_pdf' => 'nullable|mimes:pdf|max:10240',
            'nro_trabajadores' => 'nullable|integer|min:0',
            'precio_trabajador' => 'nullable|numeric|min:0',
            'monto_contabilidad' => 'nullable|numeric|min:0',
            'sin_movimiento' => 'nullable|boolean'
        ]);

        // Validar que el nuevo monto no sea menor al total de pagos realizados
        $totalPagado = $deuda->pagos()->sum('monto_pagado');
        if ($request->monto_deuda < $totalPagado) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Monto de deuda no permitido'], 422);
            }
            return back()->with('error', 'Monto de deuda no permitido');
        }

        // Calcular fechas según tipo
        if ($request->tipo_declaracion === 'anual') {
            $anio = $request->anio_declaracion;

            $mesDeclaracion = ($anio + 1) . '-04-01';
            $fechaFactura = ($anio + 1) . '-04-15';
        } else {
            $mesDeclaracion = $request->mes_declaracion . '-01';
            $fechaFactura = date('Y-m-d', strtotime($request->mes_declaracion . '-15 +1 month'));
        }

        $cotizacion = CotizacionRecepcion::find($deuda->id_cotizacion);
        $allowMultiple = false;

        if ($cotizacion) {
            // Permitir múltiples deudas por mes para el id_empresa_cliente = 138 o si es tipo 'otros'
            // Array para que a futuro solo se agreguen más IDs si se requiere
            $allowedEmpresasMultiplesDeudas = [138];

            if (in_array(strtolower($cotizacion->tipo_cotizacion), ['otros', 'otro'])) {
                $allowMultiple = true;
            } elseif (in_array($cotizacion->id_empresa_cliente, $allowedEmpresasMultiplesDeudas)) {
                $allowMultiple = true;
            }
        }

        if (!$allowMultiple) {
            $existe = DeudaRecepcion::where('id_cotizacion', $deuda->id_cotizacion)
                ->where('mes_declaracion', $mesDeclaracion)
                ->where('id_deuda_recepcion', '!=', $id)
                ->exists();
                
            if ($existe) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['error' => 'Ya existe una deuda registrada para este mes y año en esta cotización.'], 422);
                }
                return back()->with('error', 'Ya existe una deuda registrada para este mes y año en esta cotización.');
            }
        }

        $nro_trabajadores = $request->input('nro_trabajadores', $deuda->nro_trabajadores);
        $precio_trabajador = $request->input('precio_trabajador', $deuda->precio_trabajador);
        $monto_contabilidad = $request->input('monto_contabilidad', $deuda->monto_contabilidad);
        $sin_movimiento = $request->has('sin_movimiento') ? 1 : 0;
        
        $monto_recursos = $nro_trabajadores * $precio_trabajador;
        $monto_deuda_calculado = $monto_recursos + $monto_contabilidad;

        $archivo_pdf = $deuda->archivo_pdf;
        if ($request->hasFile('archivo_pdf')) {
            // Eliminar pdf anterior si existe
            if ($archivo_pdf && file_exists(public_path($archivo_pdf))) {
                unlink(public_path($archivo_pdf));
            }
            $file = $request->file('archivo_pdf');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('facturas_deudas_recepcion'), $filename);
            $archivo_pdf = 'facturas_deudas_recepcion/' . $filename;
        }

                $id_usuario = auth()->id();
        $id_usuario_recursos = $deuda->id_usuario_recursos;
        $ult_actualizacion_recursos = $deuda->ult_actualizacion_recursos;
        $id_usuario_contabilidad = $deuda->id_usuario_contabilidad;
        $ult_actualizacion_contabilidad = $deuda->ult_actualizacion_contabilidad;

        // Determine Recursos Humanos fields
        if ($request->has('nro_trabajadores') || $request->has('precio_trabajador')) {
            $nroTrab = $request->input('nro_trabajadores');
            $precioTrab = $request->input('precio_trabajador');
            
            // Si no se recibe dato (es null o string vacío), lo forzamos a 0
            $nroTrabFinal = ($nroTrab === null || $nroTrab === '') ? 0 : $nroTrab;
            $precioTrabFinal = ($precioTrab === null || $precioTrab === '') ? 0 : $precioTrab;

            if ($nroTrabFinal == 0 && $precioTrabFinal == 0) {
                $id_usuario_recursos = null;
                $ult_actualizacion_recursos = null;
                $nro_trabajadores = 0;
                $precio_trabajador = 0;
            } else {
                $id_usuario_recursos = $id_usuario;
                $ult_actualizacion_recursos = now();
                $nro_trabajadores = $nroTrabFinal;
                $precio_trabajador = $precioTrabFinal;
            }
        }

        // Determine Contabilidad fields
        $sin_movimiento = $request->has('sin_movimiento') ? 1 : 0;
        if ($request->has('monto_contabilidad') || $request->has('sin_movimiento')) {
            $montoCont = $request->input('monto_contabilidad');
            if (empty($montoCont) && !$sin_movimiento) {
                $id_usuario_contabilidad = null;
                $ult_actualizacion_contabilidad = null;
                $monto_contabilidad = 0;
            } else {
                $id_usuario_contabilidad = $id_usuario;
                $ult_actualizacion_contabilidad = now();
                $monto_contabilidad = $montoCont ?? 0;
            }
        }

        $diferenciaMonto = $monto_deuda_calculado - $deuda->monto_deuda;
        $nuevoMontoPendiente = $deuda->monto_pendiente + $diferenciaMonto;

        $nuevoEstado = 0;
        if ($nuevoMontoPendiente <= 0) {
            $nuevoEstado = 1;
        } elseif ($nuevoMontoPendiente < $monto_deuda_calculado) {
            $nuevoEstado = 2;
        }

        $deuda->update([
            'mes_declaracion' => $mesDeclaracion,
            'tipo_declaracion' => $request->tipo_declaracion,
            'anio_declaracion' => $request->tipo_declaracion === 'anual' ? $request->anio_declaracion : null,
            'nro_factura' => $request->nro_factura,
            'fecha_factura' => $fechaFactura,
            'descripcion_servicio' => $request->descripcion_servicio,
            'monto_deuda' => $monto_deuda_calculado,
            'monto_pendiente' => $nuevoMontoPendiente,
            'estado' => $nuevoEstado,
            'archivo_pdf' => $archivo_pdf,
            'monto_recursos' => $monto_recursos,
            'nro_trabajadores' => $nro_trabajadores,
            'precio_trabajador' => $precio_trabajador,
            'monto_contabilidad' => $monto_contabilidad,
            'sin_movimiento' => $sin_movimiento,
            'ult_actualizacion_recursos' => $ult_actualizacion_recursos,
            'id_usuario_recursos' => $id_usuario_recursos,
            'ult_actualizacion_contabilidad' => $ult_actualizacion_contabilidad,
            'id_usuario_contabilidad' => $id_usuario_contabilidad
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Deuda actualizada correctamente.']);
        }
        return back()->with('success', 'Deuda actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $deuda = DeudaRecepcion::findOrFail($id);

        // Eliminar todos los pagos asociados
        $deuda->pagos()->delete();

        // Eliminar la deuda
        $deuda->delete();

        return back()->with('success', 'Deuda y sus pagos eliminados correctamente.');
    }

    public function pagos($id)
    {
        $deuda = DeudaRecepcion::with('pagos')->findOrFail($id);

        $pagos = $deuda->pagos->map(function ($pago) {
            return [
                'id' => $pago->id_pago_deuda_recepcion,
                'fecha' => \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y'),
                'fecha_raw' => \Carbon\Carbon::parse($pago->fecha_pago)->format('Y-m-d'),
                'monto' => number_format($pago->monto_pagado, 2, '.', ','),
                'monto_raw' => $pago->monto_pagado,
                'metodo' => $pago->metodo_pago ?? 'N/A',
                'nro_operacion' => $pago->nro_pago ?? 'N/A',
                'observacion' => $pago->observacion ?? '—'
            ];
        });

        return response()->json([
            'pagos' => $pagos
        ]);
    }

    public function toggleEstadoServicio($id)
    {
        $empresa = EmpresasClientes::findOrFail($id);

        $empresa->estado_servicio_recepcion = $empresa->estado_servicio_recepcion == 1 ? 0 : 1;
        $empresa->save();

        $ultimaCotizacion = $empresa->cotizaciones()
            ->latest('created_at')
            ->first();

        return response()->json([
            'success' => true,
            'nuevo_estado' => $empresa->estado_servicio_recepcion,
            'estado_actual' => $empresa->estado_actual,
            'razon_social' => $empresa->razon_social,
            'ultima_cotizacion' => $ultimaCotizacion ? [
                'id' => $ultimaCotizacion->id_cotizacion,
                'nro' => $ultimaCotizacion->nro_cotizacion,
                'tipo' => $ultimaCotizacion->tipo_cotizacion,
            ] : null,
        ]);
    }

    public function generarPDF(Request $request, $id)
    {
        $request->validate([
            'status_pago' => 'required|in:pagado,pendiente-parcial,ambos',
            'tipo_cotizacion' => 'required|in:contable,otros,ambos',
            'ordenar_por' => 'required|in:mes_declaracion,nro_cotizacion,anual',
            'fecha_desde' => 'nullable|string',
            'fecha_hasta' => 'nullable|string',
            'anio_declaracion' => 'nullable|integer'
        ]);

        $empresa = EmpresasClientes::with([
            'todasCotizaciones' => function ($query) use ($request) {
                if ($request->tipo_cotizacion !== 'ambos') {
                    $query->where('tipo_cotizacion', $request->tipo_cotizacion);
                }
                if ($request->ordenar_por === 'nro_cotizacion') {
                    $query->orderBy('nro_cotizacion');
                }
                $query->with([
                    'deudas' => function ($subQuery) use ($request) {

                        // MODO ANUAL
                        if ($request->ordenar_por === 'anual') {
                            $subQuery->where('tipo_declaracion', 'anual');

                            // Filtrar por año específico
                            if ($request->filled('anio_declaracion')) {
                                $subQuery->where('anio_declaracion', $request->anio_declaracion);
                            }
                        }

                        if ($request->status_pago === 'pagado') {
                            $subQuery->where('estado', 1);
                        } elseif ($request->status_pago === 'pendiente-parcial') {
                            $subQuery->whereIn('estado', [0, 2]);
                        }

                        // Filtro por rango fecha_desde / fecha_hasta
                        if ($request->ordenar_por === 'mes_declaracion') {
                            if ($request->fecha_desde) {
                                $subQuery->where('mes_declaracion', '>=', $request->fecha_desde . '-01');
                            }
                            if ($request->fecha_hasta) {
                                // Último día del mes hasta
                                $hasta = \Carbon\Carbon::parse($request->fecha_hasta . '-01')->endOfMonth()->format('Y-m-d');
                                $subQuery->where('mes_declaracion', '<=', $hasta);
                            }
                        }

                        if ($request->ordenar_por === 'anual') {
                            $subQuery->orderBy('anio_declaracion', 'desc');
                        } else {
                            $subQuery->orderBy('mes_declaracion', 'desc');
                        }

                        $subQuery->with([
                            'pagos' => function ($pagoQuery) {
                                $pagoQuery->orderBy('fecha_pago', 'desc');
                            }
                        ]);
                    }
                ]);
            }
        ])->findOrFail($id);

        // Reagrupar si se ordena por mes
        if ($request->ordenar_por === 'mes_declaracion') {
            $todasDeudas = collect();
            foreach ($empresa->todasCotizaciones as $cotizacion) {
                foreach ($cotizacion->deudas as $deuda) {
                    $deuda->cotizacion = $cotizacion;
                    $todasDeudas->push($deuda);
                }
            }
            $empresa->deudasOrdenadas = $todasDeudas->sortByDesc('mes_declaracion');
        }

        $fechaHoy = \Carbon\Carbon::now()->format('d/m/Y');
        $statusTexto = match ($request->status_pago) {
            'pagado' => 'PAGADO',
            'pendiente-parcial' => 'PENDIENTE/PARCIAL',
            'ambos' => 'TODOS'
        };
        $tipoTexto = match ($request->tipo_cotizacion) {
            'contable' => 'CONTABLE',
            'otros' => 'OTROS',
            'ambos' => 'AMBOS'
        };
        $ordenTexto = match ($request->ordenar_por) {
            'mes_declaracion' => 'MES DECLARACIÓN',
            'anual' => 'DECLARACIONES ANUALES',
            'nro_cotizacion' => 'N° COTIZACIÓN'
        };

        // Texto del rango para mostrar en PDF
        $rangoTexto = '';
        if ($request->fecha_desde || $request->fecha_hasta) {
            $desde = $request->fecha_desde
                ? ucfirst(\Carbon\Carbon::parse($request->fecha_desde . '-01')->isoFormat('MMMM YYYY'))
                : 'Inicio';
            $hasta = $request->fecha_hasta
                ? ucfirst(\Carbon\Carbon::parse($request->fecha_hasta . '-01')->isoFormat('MMMM YYYY'))
                : 'Fin';
            $rangoTexto = "$desde → $hasta";
        }

        $pdf = \PDF::loadView('pdf.cotizacion_recepcion', compact(
            'empresa',
            'fechaHoy',
            'statusTexto',
            'tipoTexto',
            'ordenTexto',
            'rangoTexto',
            'request'
        ));

        $prefix = 'deudas_recepcion';
        if ($request->status_pago === 'pendiente-parcial') {
            $prefix = 'REPORTE_DEUDA';
        } elseif ($request->status_pago === 'pagado') {
            $prefix = 'CONFORMIDAD_DE_PAGO';
        }
        $nombreArchivo = $prefix . '_' . str_replace(' ', '_', $empresa->razon_social) . '_' . date('Y-m-d') . '.pdf';

        if ($request->query('download') || $request->input('download')) {
            return $pdf->download($nombreArchivo);
        }
        return $pdf->stream($nombreArchivo);
    }
    public function cotipendientes()
    {
        $deudasPendientesRaw = DeudaRecepcion::where('monto_pendiente', '>', 0)
            ->with(['cotizacion.empresaCliente'])
            ->get();

        $deudasPendientes = $deudasPendientesRaw->groupBy('cotizacion.id_empresa_cliente')
            ->map(function ($group) {
                $first = $group->first();
                $empresa = $first->cotizacion->empresaCliente;

                $montoContable = $group->where('cotizacion.tipo_cotizacion', 'contable')->sum('monto_pendiente');
                $montoOtros = $group->where('cotizacion.tipo_cotizacion', 'otros')->sum('monto_pendiente');

                return (object) [
                    'empresa' => $empresa,
                    'monto_total_contable' => $montoContable,
                    'monto_total_otros' => $montoOtros,
                    'monto_total' => $montoContable + $montoOtros,
                ];
            })
            ->sortBy('empresa.razon_social')
            ->values();

        $totalContable = $deudasPendientes->sum('monto_total_contable');
        $totalOtros = $deudasPendientes->sum('monto_total_otros');
        $totalDeuda = $totalContable + $totalOtros;

        return view('deudas_recepcion.cotipendientes', compact('deudasPendientes', 'totalContable', 'totalOtros', 'totalDeuda'));
    }

    public function getMontoContabilidadAnterior(Request $request)
    {
        $id_cotizacion = $request->query('id_cotizacion');
        $mes_actual = $request->query('mes'); // Formato YYYY-MM
        
        if (!$id_cotizacion || !$mes_actual) {
            return response()->json(['monto_contabilidad' => 0]);
        }
        
        $mes_anterior = \Carbon\Carbon::parse($mes_actual . '-01')->subMonth()->format('Y-m-d');
        
        $deuda_anterior = DeudaRecepcion::where('id_cotizacion', $id_cotizacion)
                            ->where('mes_declaracion', $mes_anterior)
                            ->orderBy('id_deuda_recepcion', 'desc')
                            ->first();
                            
        if ($deuda_anterior) {
            return response()->json([
                'monto_contabilidad' => $deuda_anterior->monto_contabilidad ?? 0,
                'encontrado' => true
            ]);
        }
        
        return response()->json([
            'monto_contabilidad' => 0,
            'encontrado' => false
        ]);
    }
}
