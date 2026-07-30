<?php

namespace App\Http\Controllers;

use App\Models\ClienteAuditoria;
use App\Models\EmpresasClientes;
use App\Models\Factura;
use App\Models\Guia;
use App\Models\Inventario;
use App\Models\PrecioDolar;
use App\Models\RelacionFacturacion;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GuiasExport;
use Carbon\Carbon;

class GuiaController extends Controller
{
    public function index(Request $request)
    {
        $mes = $request->input('mes', now()->format('m'));
        $anio = $request->input('anio', now()->format('Y'));

        /* ======================================================
         | 1. CARGAR GUÍAS DEL MES
         ====================================================== */

        $queryBase = Guia::with([
            'empresa.direcciones' => fn($q) => $q->where('estado', 1),
            'cliente',
            'usuario'
        ])
            ->whereMonth('recepcion', $mes)
            ->whereYear('recepcion', $anio);

        // GUÍAS VISIBLES EN LA TABLA (incluyendo automáticas)
        $guias = (clone $queryBase)
            ->orderByDesc('recepcion')
            ->get();

        // GUÍAS AUTOMÁTICAS (vacío para compatibilidad con vistas)
        $guiasAutomaticas = collect();

        /* ======================================================
         | 2. ARMAR RESUMEN BASE POR RECEPTOR (EMPRESA / CLIENTE)
         ====================================================== */

        $resumenFacturacion = [];

        foreach ($guias as $guia) {

            if ($guia->empresa) {
                $tipo = 'empresa';
                $idReceptor = $guia->empresa->id_empresas_clientes;
                $razonSocial = $guia->empresa->razon_social;
                $ruc = $guia->empresa->ruc;
            } elseif ($guia->cliente) {
                $tipo = 'cliente';
                $idReceptor = $guia->cliente->id_cliente;
                $razonSocial = $guia->cliente->razon_social;
                $ruc = $guia->cliente->ruc;
            } else {
                continue; // guía inválida
            }

            $key = $tipo . '_' . $idReceptor;

            if (!isset($resumenFacturacion[$key])) {
                $simbolo = $guia->moneda === 'USD' ? '$' : 'S/';
                $resumenFacturacion[$key] = [
                    'tipo' => $tipo,
                    'moneda' => $guia->moneda,      // USD | PEN
                    'simbolo' => $simbolo,
                    'razon_social' => $razonSocial,
                    'ruc' => $ruc,
                    'monto_total_guias' => 0,
                    'monto_facturado' => 0,
                    'monto_facturado_bruto' => 0,
                    'monto_abonado' => 0,
                ];
            }

            $resumenFacturacion[$key]['monto_total_guias'] += (float) $guia->monto;
        }

        /* ======================================================
         | 3. SUMAR FACTURAS DEL MES (FIX DEFINITIVO 🔥)
         ====================================================== */

        $facturasDelMes = Factura::with('abonos')
            ->whereIn('estado', ['Pendiente', 'Facturada'])
            ->whereMonth('fecha_emision', $mes)
            ->whereYear('fecha_emision', $anio)
            ->get();

        $montosSunatPorReceptor = [];

        foreach ($facturasDelMes as $factura) {
            /** @var \App\Models\Factura $factura */

            if ($factura->id_empresa_cliente) {
                $key = 'empresa_' . $factura->id_empresa_cliente;
            } elseif ($factura->id_cliente) {
                $key = 'cliente_' . $factura->id_cliente;
            } else {
                continue;
            }

            if (!isset($resumenFacturacion[$key])) {
                continue;
            }

            $monto = $factura->monto_facturable ?? $factura->total_neto;

            // Buscar la moneda de la guía desde el resumen (más confiable que id_guia)
            $monedaGuia = strtoupper(trim($resumenFacturacion[$key]['moneda'] ?? ''));
            $monedaFactura = strtoupper(trim($factura->tipo_moneda));

            $rateGuia = ((float) $factura->precio_dolar > 0) ? (float) $factura->precio_dolar : 1;
            $rateSunat = ((float) $factura->precio_dolar_factura > 0) ? (float) $factura->precio_dolar_factura : $rateGuia;

            if ($monedaGuia === 'USD') {
                if ($monedaFactura === 'USD') {
                    // USD -> USD (Standard guide rate)
                    $montoFacturado = $factura->total_neto / $rateGuia;
                    $montoFacturadoBruto = $factura->total_bruto / $rateGuia;
                } else {
                    // PEN -> USD (Using SUNAT rate / Fallback)
                    $montoFacturado = $factura->total_neto / $rateSunat;
                    $montoFacturadoBruto = $factura->total_bruto / $rateSunat;
                }
            } else { // monedaGuia === 'PEN'
                if ($monedaFactura === 'USD') {
                    // USD -> PEN (Converting from USD back to PEN via SUNAT)
                    $montoFacturado = ($factura->total_neto / $rateGuia) * $rateSunat;
                    $montoFacturadoBruto = ($factura->total_bruto / $rateGuia) * $rateSunat;
                } else {
                    // PEN -> PEN (No conversion)
                    $montoFacturado = $factura->total_neto;
                    $montoFacturadoBruto = $factura->total_bruto;
                }
            }

            $resumenFacturacion[$key]['monto_facturado'] += round((float) $montoFacturado, 2);
            $resumenFacturacion[$key]['monto_facturado_bruto'] += round((float) $montoFacturadoBruto, 2);
            $resumenFacturacion[$key]['monto_abonado'] += (float) $factura->calcularTotalAbonadoEnMonedaFactura();

            /* ===============================
            | 3.5 MONTO SUNAT (SOLO PARENTESIS)
            =============================== */
            if ($monedaFactura === 'USD' && $monedaGuia === 'USD') {

                $rateGuia = ($factura->precio_dolar > 0)
                    ? $factura->precio_dolar
                    : 1;

                $rateSunat = ($factura->precio_dolar_factura > 0)
                    ? $factura->precio_dolar_factura
                    : 1;

                // USD → base guía → PEN SUNAT
                $montoSunat = ($factura->total_bruto / $rateGuia) * $rateSunat;

                $montosSunatPorReceptor[$key] =
                    ($montosSunatPorReceptor[$key] ?? 0) + $montoSunat;
            }

        }

        /* ======================================================
         | 3.6 RE-SYNC bancarización de guías del mes
         ====================================================== */
        $facturasPorRuc = $facturasDelMes->groupBy('ruc_receptor');

        foreach ($resumenFacturacion as $key => $item) {
            $ruc = $item['ruc'];
            $facturasDelRuc = $facturasPorRuc->get($ruc, collect());

            $total = $facturasDelRuc->count();
            $bancarizadas = $facturasDelRuc->filter(
                fn($f) => in_array($f->estado_abono, ['bancarizado', 'bancarizado_cliente'])
            )->count();

            $nuevaBancarizacion = ($total > 0 && $total === $bancarizadas) ? 'si' : '-';

            Guia::where(function ($q) use ($ruc) {
                $q->whereHas('empresa', fn($q2) => $q2->where('ruc', $ruc))
                    ->orWhereHas('cliente', fn($q2) => $q2->where('ruc', $ruc));
            })
                ->whereYear('recepcion', $anio)
                ->whereMonth('recepcion', $mes)
                ->where('bancarizacion', '!=', $nuevaBancarizacion)
                ->update(['bancarizacion' => $nuevaBancarizacion]);

            foreach ($guias as $guia) {
                $guiaRuc = $guia->empresa?->ruc ?? $guia->cliente?->ruc;
                if ($guiaRuc === $ruc) {
                    $guia->bancarizacion = $nuevaBancarizacion;
                }
            }
        }

        /* ======================================================
         | 4. INVENTARIOS (SEGÚN RELACIÓN)
         ====================================================== */

        $idReceptor = $request->input('id_receptor');
        $tipoReceptor = $request->input('tipo');

        if (!$idReceptor) {

            $inventarios = Inventario::with('empresa')->get();

        } elseif ($tipoReceptor === 'cliente') {

            // Si es cliente, buscar la empresa con el mismo RUC
            $cliente = ClienteAuditoria::find($idReceptor);
            if ($cliente && $cliente->ruc) {
                $empresaConMismoRuc = EmpresasClientes::where('ruc', $cliente->ruc)->first();
                if ($empresaConMismoRuc) {
                    // Buscar en CONTRATOS primero
                    $origenes = \App\Models\Contrato::where('id_empresa_destino', $empresaConMismoRuc->id_empresas_clientes)
                        ->where('estado_actual', 1)
                        ->pluck('id_empresa_origen')
                        ->toArray();

                    // Si no hay contratos, buscar en RelacionFacturacion
                    if (count($origenes) === 0) {
                        $origenes = RelacionFacturacion::where('id_empresa_destino', $empresaConMismoRuc->id_empresas_clientes)
                            ->pluck('id_empresa_origen')
                            ->toArray();
                    }

                    $inventarios = Inventario::whereIn('id_empresas_cliente', $origenes)
                        ->with('empresa')
                        ->get();
                } else {
                    $inventarios = Inventario::with('empresa')->get();
                }
            } else {
                $inventarios = Inventario::with('empresa')->get();
            }

        } else {

            // Buscar en CONTRATOS primero
            $origenes = \App\Models\Contrato::where('id_empresa_destino', $idReceptor)
                ->where('estado_actual', 1)
                ->pluck('id_empresa_origen')
                ->toArray();

            // Si no hay contratos, buscar en RelacionFacturacion
            if (count($origenes) === 0) {
                $origenes = RelacionFacturacion::where('id_empresa_destino', $idReceptor)
                    ->pluck('id_empresa_origen')
                    ->toArray();
            }

            $inventarios = Inventario::whereIn('id_empresas_cliente', $origenes)
                ->with('empresa')
                ->get();
        }

        /* ======================================================
         | 5. TOTAL GENERAL
         ====================================================== */

        $total = $guias->sum('monto');

        $empresas_export = EmpresasClientes::orderBy('razon_social')->get();
        $clientes_export = ClienteAuditoria::orderBy('razon_social')->get();

        return view('guia.index', compact(
            'guias',
            'guiasAutomaticas',
            'mes',
            'anio',
            'total',
            'inventarios',
            'resumenFacturacion',
            'montosSunatPorReceptor',
            'empresas_export',
            'clientes_export'
        ));
    }

    public function inventariosRelacionados(Request $request)
    {
        try {

            $idReceptor = (int) $request->id_receptor;
            $tipoReceptor = $request->tipo;

            if (!$idReceptor || !in_array($tipoReceptor, ['cliente', 'empresa'])) {
                return response()->json([]);
            }

            $origenes = [];
            $contratosPorOrigen = [];

            // ============ BUSCAR EN CONTRATOS ============
            if ($tipoReceptor === 'cliente') {
                // Caso 1: Buscar contratos directos al cliente
                $contratosCliente = \App\Models\Contrato::where('id_cliente_auditoria_destino', $idReceptor)
                    ->where('estado_actual', 1)
                    ->orderBy('fecha_fin', 'desc')
                    ->orderBy('fecha_inicio', 'desc')
                    ->get();

                foreach ($contratosCliente as $c) {
                    if (!empty($c->id_empresa_origen)) {
                        if (!isset($contratosPorOrigen[$c->id_empresa_origen])) {
                            $contratosPorOrigen[$c->id_empresa_origen] = $c;
                        }
                        $origenes[] = $c->id_empresa_origen;
                    }
                }

                // Caso 2: Si el cliente tiene RUC, buscar también contratos a su empresa equivalente
                $cliente = ClienteAuditoria::find($idReceptor);
                if ($cliente && $cliente->ruc) {
                    $empresaEquivalente = EmpresasClientes::where('ruc', $cliente->ruc)->first();
                    if ($empresaEquivalente) {
                        $contratosEmpresa = \App\Models\Contrato::where('id_empresa_destino', $empresaEquivalente->id_empresas_clientes)
                            ->where('estado_actual', 1)
                            ->orderBy('fecha_fin', 'desc')
                            ->orderBy('fecha_inicio', 'desc')
                            ->get();

                        foreach ($contratosEmpresa as $c) {
                            if (!empty($c->id_empresa_origen)) {
                                if (!isset($contratosPorOrigen[$c->id_empresa_origen])) {
                                    $contratosPorOrigen[$c->id_empresa_origen] = $c;
                                }
                                $origenes[] = $c->id_empresa_origen;
                            }
                        }
                    }
                }

            } else {
                // Caso 3: Buscar contratos a la empresa
                $contratosEmpresa = \App\Models\Contrato::where('id_empresa_destino', $idReceptor)
                    ->where('estado_actual', 1)
                    ->orderBy('fecha_fin', 'desc')
                    ->orderBy('fecha_inicio', 'desc')
                    ->get();

                foreach ($contratosEmpresa as $c) {
                    if (!empty($c->id_empresa_origen)) {
                        if (!isset($contratosPorOrigen[$c->id_empresa_origen])) {
                            $contratosPorOrigen[$c->id_empresa_origen] = $c;
                        }
                        $origenes[] = $c->id_empresa_origen;
                    }
                }

                // Caso 4: Si la empresa tiene RUC, buscar también contratos al cliente equivalente
                $empresa = EmpresasClientes::find($idReceptor);
                if ($empresa && $empresa->ruc) {
                    $clienteEquivalente = ClienteAuditoria::where('ruc', $empresa->ruc)->first();
                    if ($clienteEquivalente) {
                        $contratosCliente = \App\Models\Contrato::where('id_cliente_auditoria_destino', $clienteEquivalente->id_cliente)
                            ->where('estado_actual', 1)
                            ->orderBy('fecha_fin', 'desc')
                            ->orderBy('fecha_inicio', 'desc')
                            ->get();

                        foreach ($contratosCliente as $c) {
                            if (!empty($c->id_empresa_origen)) {
                                if (!isset($contratosPorOrigen[$c->id_empresa_origen])) {
                                    $contratosPorOrigen[$c->id_empresa_origen] = $c;
                                }
                                $origenes[] = $c->id_empresa_origen;
                            }
                        }
                    }
                }

                // Caso 5: Buscar también en RelacionFacturacion
                $originesRelacion = \App\Models\RelacionFacturacion::where('id_empresa_destino', $idReceptor)
                    ->pluck('id_empresa_origen')
                    ->toArray();

                $origenes = array_merge($origenes, $originesRelacion);
            }

            // Eliminar duplicados
            $origenes = array_unique($origenes);

            // ============ OBTENER INVENTARIOS ============

            if (empty($origenes)) {
                // 🔥 NO hay contratos ni relaciones → TODOS los inventarios
                $inventarios = \App\Models\Inventario::with('empresa')->get();
            } else {
                // Hay contratos o relaciones → filtrar
                $inventarios = \App\Models\Inventario::whereIn('id_empresas_cliente', $origenes)
                    ->with('empresa')
                    ->get();
            }

            // Adjuntar fecha fin de contrato si existe
            foreach ($inventarios as $inv) {
                if (isset($contratosPorOrigen[$inv->id_empresas_cliente])) {
                    $inv->fecha_fin_contrato = $contratosPorOrigen[$inv->id_empresas_cliente]->fecha_fin;
                }
            }

            return response()->json($inventarios);

        } catch (\Throwable $e) {

            \Log::error('inventariosRelacionados ERROR', [
                'msg' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function createDesdeEmpresa($empresaId = null)
    {
        $empresas = EmpresasClientes::orderBy('razon_social')->get();
        return view('guia.create_empresa', compact('empresas', 'empresaId'));
    }

    public function createDesdeCliente($clienteId = null)
    {
        $clientes = ClienteAuditoria::orderBy('razon_social')->get();
        return view('guia.create_cliente', compact('clientes', 'clienteId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_cliente' => 'nullable|exists:clientes_auditoria,id_cliente',
            'id_empresas_clientes' => 'nullable|exists:empresas_clientes,id_empresas_clientes',
            'id_usuario' => 'required|exists:users,id',
            'monto' => 'nullable|numeric',
            'moneda' => 'nullable|string|in:PEN,USD',
            'tipo_cambio' => 'nullable|numeric|min:0.001',
            'recepcion' => 'required|date',
            'programado' => 'required|string|in:listo,pendiente',
            'emitido' => 'required|string|in:listo,pendiente',
            'entregado' => 'nullable|string',
            'grr' => 'required|string|in:si,no,-',
            'grt' => 'required|string|in:si,no,-',
            'bancarizacion' => 'required|string',
            'observaciones' => 'nullable|string',
        ]);

        if (!$request->filled('id_cliente') && !$request->filled('id_empresas_clientes')) {
            return back()->withErrors(['id_cliente' => 'Debe seleccionar un cliente o una empresa.'])->withInput();
        }

        if ($request->filled('id_cliente') && $request->filled('id_empresas_clientes')) {
            return back()->withErrors(['id_cliente' => 'Solo debe seleccionar un cliente o una empresa, no ambos.'])->withInput();
        }

        $validated['contingencia'] = $request->has('contingencia');

        // Si es USD, obtener el tipo de cambio actual
        if (($validated['moneda'] ?? 'PEN') === 'USD') {
            if (!isset($validated['tipo_cambio']) || empty($validated['tipo_cambio'])) {
                $precioDolar = PrecioDolar::first();
                $validated['tipo_cambio'] = $precioDolar?->precio ?? 3.80;
            }
        }

        Guia::create($validated);

        return redirect()->route('guias.index')->with('success', 'Guía registrada con éxito.');
    }

    public function edit($id)
    {
        $guia = Guia::findOrFail($id);
        $empresas = EmpresasClientes::orderBy('razon_social')->get();
        $clientes = ClienteAuditoria::orderBy('razon_social')->get();

        return view('guia.edit', compact('guia', 'empresas', 'clientes'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'id_empresas_clientes' => 'nullable|exists:empresas_clientes,id_empresas_clientes',
            'id_cliente' => 'nullable|exists:clientes_auditoria,id_cliente',
            'monto' => 'nullable|numeric',
            'moneda' => 'nullable|string|in:PEN,USD',
            'tipo_cambio' => 'nullable|numeric|min:0.001',
            'recepcion' => 'required|date',
            'programado' => 'required|string|in:listo,pendiente',
            'emitido' => 'required|string|in:listo,pendiente',
            'entregado' => 'nullable|string',
            'grr' => 'required|string|in:si,no,-',
            'grt' => 'required|string|in:si,no,-',
            'bancarizacion' => 'required|string',
            'observaciones' => 'nullable|string',
        ]);

        // Validación de que solo uno esté presente
        if (!$request->filled('id_empresas_clientes') && !$request->filled('id_cliente')) {
            return back()->withErrors(['id_empresas_clientes' => 'Debe asignarse a una empresa o un cliente.'])->withInput();
        }

        if ($request->filled('id_empresas_clientes') && $request->filled('id_cliente')) {
            return back()->withErrors(['id_empresas_clientes' => 'Solo uno puede estar asignado.'])->withInput();
        }

        $validated['contingencia'] = $request->has('contingencia');
        $validated['bancarizacion'] = '-';
        $validated['id_usuario'] = auth()->id();

        // Si es USD, obtener el tipo de cambio actual si no está especificado
        if (($validated['moneda'] ?? 'PEN') === 'USD') {
            if (!isset($validated['tipo_cambio']) || empty($validated['tipo_cambio'])) {
                $precioDolar = PrecioDolar::first();
                $validated['tipo_cambio'] = $precioDolar?->precio ?? 3.80;
            }
        }

        $guia = Guia::findOrFail($id);
        $guia->update($validated);

        return redirect()->route('guias.index')->with('success', 'Guía actualizada con éxito.');
    }


    public function destroy($id)
    {
        $guia = Guia::findOrFail($id);
        $guia->delete();

        return redirect()->route('guias.index')->with('success', 'Guía eliminada con éxito.');
    }

    public function bancasIndex(Request $request)
    {
        $mes = $request->input('mes', now()->format('m'));
        $anio = $request->input('anio', now()->format('Y'));

        $guias = Guia::with(['empresa', 'cliente'])
            ->whereMonth('recepcion', $mes)
            ->whereYear('recepcion', $anio)
            ->orderByDesc('recepcion')
            ->get();

        // Construir resumen por receptor (único por empresa/cliente)
        $resumen = [];
        foreach ($guias as $guia) {
            if ($guia->empresa) {
                $tipo     = 'empresa';
                $id       = $guia->empresa->id_empresas_clientes;
                $ruc      = $guia->empresa->ruc;
                $razon    = $guia->empresa->razon_social;
                $contacto = $guia->empresa->contacto;
            } elseif ($guia->cliente) {
                $tipo     = 'cliente';
                $id       = $guia->cliente->id_cliente;
                $ruc      = $guia->cliente->ruc;
                $razon    = $guia->cliente->razon_social;
                $contacto = $guia->cliente->contacto;
            } else {
                continue;
            }

            $key = $tipo . '_' . $id;
            if (!isset($resumen[$key])) {
                $resumen[$key] = [
                    'tipo'           => $tipo,
                    'id'             => $id,
                    'ruc'            => $ruc,
                    'razon_social'   => $razon,
                    'contacto'       => $contacto,
                    'moneda'         => $guia->moneda,
                    'simbolo'        => $guia->moneda === 'USD' ? '$' : 'S/',
                    'monto'          => 0,
                    'monto_facturado'=> 0,
                    'monto_abonado'  => 0,
                    'bancarizacion'  => $guia->bancarizacion,
                ];
            }
            $resumen[$key]['monto'] += (float) $guia->monto;
        }

        // Sumar facturas del mes para obtener facturado y abonado por receptor
        $facturasDelMes = Factura::with('abonos')
            ->whereIn('estado', ['Pendiente', 'Facturada'])
            ->whereMonth('fecha_emision', $mes)
            ->whereYear('fecha_emision', $anio)
            ->get();

        foreach ($facturasDelMes as $factura) {
            if ($factura->id_empresa_cliente) {
                $key = 'empresa_' . $factura->id_empresa_cliente;
            } elseif ($factura->id_cliente) {
                $key = 'cliente_' . $factura->id_cliente;
            } else {
                continue;
            }

            if (!isset($resumen[$key])) {
                continue;
            }

            $monedaGuia    = strtoupper(trim($resumen[$key]['moneda'] ?? ''));
            $monedaFactura = strtoupper(trim($factura->tipo_moneda));
            $rateGuia      = ((float) $factura->precio_dolar > 0) ? (float) $factura->precio_dolar : 1;
            $rateSunat     = ((float) $factura->precio_dolar_factura > 0) ? (float) $factura->precio_dolar_factura : $rateGuia;

            if ($monedaGuia === 'USD') {
                $montoFacturado = $monedaFactura === 'USD'
                    ? $factura->total_neto / $rateGuia
                    : $factura->total_neto / $rateSunat;
            } else {
                $montoFacturado = $monedaFactura === 'USD'
                    ? ($factura->total_neto / $rateGuia) * $rateSunat
                    : $factura->total_neto;
            }

            $resumen[$key]['monto_facturado'] += round((float) $montoFacturado, 2);
            $resumen[$key]['monto_abonado']   += (float) $factura->calcularTotalAbonadoEnMonedaFactura();
        }

        // Re-sync bancarización desde facturas del mes
        $facturasPorRuc = $facturasDelMes->groupBy('ruc_receptor');

        foreach ($resumen as $key => $item) {
            $facturasDelRuc = $facturasPorRuc->get($item['ruc'], collect());
            $total          = $facturasDelRuc->count();
            $bancarizadas   = $facturasDelRuc->filter(
                fn($f) => in_array($f->estado_abono, ['bancarizado', 'bancarizado_cliente'])
            )->count();
            $resumen[$key]['bancarizacion'] = ($total > 0 && $total === $bancarizadas) ? 'si' : '-';
        }

        $empresas_export = EmpresasClientes::orderBy('razon_social')->get();
        $clientes_export = ClienteAuditoria::orderBy('razon_social')->get();

        return view('guia.bancas_index', compact('resumen', 'mes', 'anio', 'empresas_export', 'clientes_export'));
    }

    public function all()
    {
        $guias = Guia::with(['empresa', 'cliente'])
            ->orderByDesc('recepcion')
            ->get();

        return view('guia.all', compact('guias'));
    }

    public function toggleProgramado(Request $request, $id)
    {
        $guia = Guia::findOrFail($id);
        $nuevoEstado = $guia->programado === 'listo' ? 'pendiente' : 'listo';
        $guia->programado = $nuevoEstado;
        $guia->id_usuario = auth()->id(); // opcional: trackea quién lo cambió
        $guia->save();

        return redirect()->back()->with('success', 'Estado "Programado" actualizado a: ' . ucfirst($nuevoEstado));
    }

    public function updateObservacion(Request $request, $id)
    {
        $request->validate([
            'observaciones' => 'nullable|string|max:1000',
        ]);
        $guia = Guia::findOrFail($id);
        $guia->observaciones = $request->observaciones;
        $guia->id_usuario = auth()->id();
        $guia->save();
        return response()->json(['success' => true]);
    }

    public function exportarExcel(Request $request)
    {
        $request->validate([
            'tipo_entidad' => 'required|in:empresa,cliente',
            'id_entidad' => 'required|integer',
            'desde' => 'required|date_format:Y-m',
            'hasta' => 'required|date_format:Y-m',
        ]);

        $desde = Carbon::createFromFormat('Y-m', $request->desde)->startOfMonth();
        $hasta = Carbon::createFromFormat('Y-m', $request->hasta)->endOfMonth();

        $entidadNombre = '';
        if ($request->tipo_entidad === 'empresa') {
            $entidad = EmpresasClientes::find($request->id_entidad);
            $entidadNombre = $entidad ? $entidad->razon_social : 'Empresa';
        } else {
            $entidad = ClienteAuditoria::find($request->id_entidad);
            $entidadNombre = $entidad ? $entidad->razon_social : 'Cliente';
        }

        $fileName = 'GUIAS_' . strtoupper(\Str::slug($entidadNombre)) . '_' . date('Ymd_His') . '.xlsx';


        return Excel::download(new GuiasExport($request->tipo_entidad, $request->id_entidad, $desde, $hasta), $fileName);
    }
}
