<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmpresasClientes;
use App\Models\ClienteAuditoria;
use App\Models\FormatoCotizaciones;

class FormatoCotizacionesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $formatos = FormatoCotizaciones::with(['empresa', 'cliente'])->get();
        return view('formato_cotizaciones.index', compact('formatos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $empresas = EmpresasClientes::orderBy('razon_social')->get();
        $clientes = ClienteAuditoria::orderBy('razon_social')->get();
        $empresas_clientes = EmpresasClientes::where('estado', 'A1')->get();
        $paresExistentes = FormatoCotizaciones::select('id_empresas_clientes_emisor', 'id_empresas_clientes', 'id_cliente')->get()->map(function ($f) {
            return [
                'emisor' => $f->id_empresas_clientes_emisor,
                'receptor' => $f->id_empresas_clientes ? 'empresa_' . $f->id_empresas_clientes : ($f->id_cliente ? 'cliente_' . $f->id_cliente : null),
            ];
        })->filter(fn($p) => $p['receptor'] !== null)->values();

        return view('formato_cotizaciones.create', compact('empresas', 'clientes', 'empresas_clientes', 'paresExistentes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(['id_empresa' => 'required']);

        [$tipo, $id] = explode('_', $request->id_empresa, 2);


        $selects = [
            ['tipo' => $request->select_grande_coti, 'valor' => $request->input_grande_coti_value],
            ['tipo' => $request->select_grande_coti2, 'valor' => $request->input_grande_coti2_value],
        ];

        $letra = null;
        $contador_actual = 0;
        $contador_padding = 0;

        foreach ($selects as $s) {
            if ($s['tipo'] === 'LETRA') {
                $letra = strtoupper(trim($s['valor'] ?? '')) ?: null;
            }
            if ($s['tipo'] === 'DIGITO') {
                $rawValor = $s['valor'] ?? '0';
                $contador_actual = (int) $rawValor;
                $contador_padding = max(1, strlen($rawValor));
            }
        }

        $partes = [];
        if ($request->select_grande_coti !== 'NADA') {
            $partes[] = $request->select_grande_coti === 'DIGITO'
                ? '{N}'
                : strtoupper(trim($request->input_grande_coti_value ?? ''));
        }
        if ($request->select_pequeno_coti !== 'NADA') {
            $partes[] = $request->select_pequeno_coti;
        }
        if ($request->select_grande_coti2 !== 'NADA') {
            $partes[] = $request->select_grande_coti2 === 'DIGITO'
                ? '{N}'
                : strtoupper(trim($request->input_grande_coti2_value ?? ''));
        }

        $formatoFinal = implode('', $partes);

        $regexPattern = '/^' . str_replace('\{N\}', '(\d+)', preg_quote($formatoFinal, '/')) . '$/';
        $likePattern = str_replace('{N}', '%', $formatoFinal);

        $facturasQuery = \App\Models\Factura::whereNotNull('nro_cotizacion')
            ->where('nro_cotizacion', 'LIKE', $likePattern);

        if ($tipo === 'empresa') {
            $facturasQuery->where('id_empresa_cliente', $id);
        } elseif ($tipo === 'cliente') {
            $facturasQuery->where('id_cliente', $id);
        }

        $maxContador = null;
        foreach ($facturasQuery->pluck('nro_cotizacion') as $correlativo) {
            if (preg_match($regexPattern, $correlativo, $matches)) {
                $numero = (int) $matches[1];
                if ($maxContador === null || $numero > $maxContador) {
                    $maxContador = $numero;
                }
            }
        }

        if ($maxContador !== null) {
            $contador_actual = max($contador_actual, $maxContador);
        }

        FormatoCotizaciones::create([
            'id_empresas_clientes' => $tipo === 'empresa' ? $id : null,
            'id_empresas_clientes_emisor' => $request->id_empresa_emisor ?: null,
            'id_cliente' => $tipo === 'cliente' ? $id : null,
            'letra_coti' => $letra,
            'signo_coti' => $request->select_pequeno_coti !== 'NADA' ? $request->select_pequeno_coti : null,
            'contador_actual_coti' => $contador_actual,
            'contador_padding_coti' => $contador_padding,
            'formato_final_coti' => $formatoFinal,
        ]);

        return redirect()->route('formato_cotizaciones.index')->with('success', 'Formato de cotización configurado correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $formato = FormatoCotizaciones::findOrFail($id);
        $empresas = EmpresasClientes::orderBy('razon_social')->get();

        $template = $formato->formato_final_coti;
        $signo1 = $formato->signo_coti;

        $temp = $template;

        if ($signo1 && str_contains($temp, $signo1)) {
            [$pos1_raw, $pos2_raw] = explode($signo1, $temp, 2);
        } else {
            if (str_starts_with($temp, '{N}')) {
                $pos1_raw = '{N}';
                $pos2_raw = substr($temp, 3);
            } elseif (str_ends_with($temp, '{N}')) {
                $pos1_raw = substr($temp, 0, -3);
                $pos2_raw = '{N}';
            } else {
                $pos1_raw = $temp;
                $pos2_raw = '';
            }
        }

        $determinarTipo = function ($valor) use ($formato) {
            if ($valor === '{N}')
                return ['tipo' => 'DIGITO', 'valor' => str_pad($formato->contador_actual_coti, $formato->contador_padding_coti ?: 1, '0', STR_PAD_LEFT)];
            if ($valor)
                return ['tipo' => 'LETRA', 'valor' => $valor];
            return ['tipo' => 'NADA', 'valor' => ''];
        };

        $pos1 = $determinarTipo($pos1_raw);
        $pos2 = $determinarTipo($pos2_raw);

        // Emisores ya usados para este mismo receptor (excluyendo el registro actual)
        $receptorKey = $formato->id_empresas_clientes
            ? 'empresa_' . $formato->id_empresas_clientes
            : ($formato->id_cliente ? 'cliente_' . $formato->id_cliente : null);

        $emisoresOcupados = FormatoCotizaciones::where('id_formato_cotizaciones', '!=', $id)
            ->when($formato->id_empresas_clientes, fn($q) => $q->where('id_empresas_clientes', $formato->id_empresas_clientes))
            ->when($formato->id_cliente, fn($q) => $q->where('id_cliente', $formato->id_cliente))
            ->pluck('id_empresas_clientes_emisor')
            ->filter()
            ->values();

        return view('formato_cotizaciones.edit', compact('formato', 'pos1', 'pos2', 'signo1', 'empresas', 'receptorKey', 'emisoresOcupados'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $formato = FormatoCotizaciones::findOrFail($id);
        $formato->id_empresas_clientes_emisor = $request->id_empresa_emisor ?: null;
        $formato->save();

        $selects = [
            ['tipo' => $request->select_grande_coti, 'valor' => $request->input_grande_coti_value],
            ['tipo' => $request->select_grande_coti2, 'valor' => $request->input_grande_coti2_value],
        ];

        $letra = null;
        $contador_actual = 0;
        $contador_padding = 0;

        foreach ($selects as $s) {
            if ($s['tipo'] === 'LETRA') {
                $letra = $s['valor'];
            }
            if ($s['tipo'] === 'DIGITO') {
                $contador_actual = (int) ($s['valor'] ?: 0);
                $contador_padding = strlen($s['valor'] ?? '');
            }
        }

        $partes = [];
        if ($request->select_grande_coti !== 'NADA')
            $partes[] = $request->select_grande_coti === 'DIGITO' ? '{N}' : $request->input_grande_coti_value;
        if ($request->select_pequeno_coti !== 'NADA')
            $partes[] = $request->select_pequeno_coti;
        if ($request->select_grande_coti2 !== 'NADA')
            $partes[] = $request->select_grande_coti2 === 'DIGITO' ? '{N}' : $request->input_grande_coti2_value;

        $formatoFinal = implode('', $partes);

        $tipo = $formato->id_empresas_clientes ? 'empresa' : 'cliente';
        $target_id = $formato->id_empresas_clientes ?: $formato->id_cliente;

        $regexPattern = '/^' . str_replace('\{N\}', '(\d+)', preg_quote($formatoFinal, '/')) . '$/';
        $likePattern = str_replace('{N}', '%', $formatoFinal);

        $facturasQuery = \App\Models\Factura::whereNotNull('nro_cotizacion')
            ->where('nro_cotizacion', 'LIKE', $likePattern);

        if ($tipo === 'empresa') {
            $facturasQuery->where('id_empresa_cliente', $target_id);
        } elseif ($tipo === 'cliente') {
            $facturasQuery->where('id_cliente', $target_id);
        }

        $maxContador = null;
        foreach ($facturasQuery->pluck('nro_cotizacion') as $correlativo) {
            if (preg_match($regexPattern, $correlativo, $matches)) {
                $numero = (int) $matches[1];
                if ($maxContador === null || $numero > $maxContador) {
                    $maxContador = $numero;
                }
            }
        }

        if ($maxContador !== null) {
            $contador_actual = max($contador_actual, $maxContador);
        }

        $formato->letra_coti = $letra;
        $formato->signo_coti = $request->select_pequeno_coti !== 'NADA' ? $request->select_pequeno_coti : null;
        $formato->contador_actual_coti = $contador_actual;
        $formato->contador_padding_coti = $contador_padding;
        $formato->formato_final_coti = $formatoFinal;
        $formato->save();

        return redirect()->route('formato_cotizaciones.index')->with('success', 'Formato de cotización actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Obtener formato vía AJAX (para usar en otros módulos).
     */
    public function getFormatoAjax(Request $request)
    {
        $id_empresa_input = $request->id_empresa;
        $id_emisor = $request->id_empresa_emisor;

        if (!$id_empresa_input)
            return response()->json(['success' => false]);

        [$tipo, $id] = explode('_', $id_empresa_input, 2);

        $query = FormatoCotizaciones::query();

        if ($id_emisor) {
            $query->where('id_empresas_clientes_emisor', $id_emisor);
        }

        if ($tipo === 'empresa') {
            $query->where('id_empresas_clientes', $id);
        } else {
            $query->where('id_cliente', $id);
        }

        $formato = $query->latest('id_formato_cotizaciones')->first();

        if ($formato) {
            return response()->json([
                'success' => true,
                'formato' => $formato->generarCadenaFormateada($formato->contador_actual_coti + 1)
            ]);
        }

        return response()->json(['success' => false]);
    }

    public function getUltimasDiezAjax(Request $request)
    {
        $id_empresa_input = $request->id_empresa;
        $id_emisor = $request->id_empresa_emisor;
        $limite = $request->limite ?? 10;

        if (!$id_empresa_input)
            return response()->json(['success' => false, 'data' => []]);

        [$tipo, $id] = explode('_', $id_empresa_input, 2);

        $facturasQuery = \App\Models\Factura::with(['empresa', 'cliente'])
            ->whereNotNull('nro_cotizacion')
            ->where('nro_cotizacion', '!=', '');

        if ($id_emisor) {
            $empresaEmisor = EmpresasClientes::find($id_emisor);
            if ($empresaEmisor) {
                $facturasQuery->where('ruc_emisor', $empresaEmisor->ruc);
            }
        }

        if ($tipo === 'empresa') {
            $facturasQuery->where('id_empresa_cliente', $id);
        } else {
            $facturasQuery->where('id_cliente', $id);
        }

        $total_records = $facturasQuery->count();

        $facturasQuery = $facturasQuery->latest('id_factura');
        
        if ($limite !== 'todos') {
            $facturasQuery->take((int)$limite);
        }

        $facturas = $facturasQuery->get()->map(function ($f) {
                return [
                    'id_factura' => $f->id_factura,
                    'fecha' => $f->fecha_emision ? date('d/m/Y', strtotime($f->fecha_emision)) : ($f->created_at ? $f->created_at->format('d/m/Y') : ''),
                    'numero' => $f->nro_cotizacion,
                    'receptor' => $f->empresa ? $f->empresa->razon_social : ($f->cliente ? $f->cliente->razon_social : ''),
                    'total_neto' => $f->total_neto,
                    'tipo_moneda' => $f->tipo_moneda,
                    'precio_dolar' => $f->precio_dolar,
                    'precio_dolar_factura' => $f->precio_dolar_factura,
                    'url_detalle' => route('factura.detalle', $f->id_factura)
                ];
            });

        return response()->json(['success' => true, 'data' => $facturas, 'total_records' => $total_records]);
    }
}
