<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormatoSolicitudes;
use App\Models\EmpresasClientes;
use App\Models\ClienteAuditoria;

class FormatoNSolicitudController extends Controller
{
    public function index()
    {
        $formatos = FormatoSolicitudes::with(['empresa', 'cliente'])->get();
        return view('formato_n_solicitud.index', compact('formatos'));
    }

    public function create()
    {
        $empresas = EmpresasClientes::orderBy('razon_social')->get();
        $clientes = ClienteAuditoria::orderBy('razon_social')->get();
        return view('formato_n_solicitud.create', compact('empresas', 'clientes'));
    }

    public function store(Request $request)
    {
        $request->validate(['id_empresa' => 'required']);
        [$tipo, $id] = explode('_', $request->id_empresa, 2);
        
        $existe = FormatoSolicitudes::when($tipo === 'empresa', function ($q) use ($id) {
            $q->where('id_empresas_clientes', $id);
        })
            ->when($tipo === 'cliente', function ($q) use ($id) {
                $q->where('id_cliente', $id);
            })
            ->exists();
            
        if ($existe) {
            return redirect()->back()
                ->with('error', 'Ya existe un formato registrado para esta empresa o cliente.');
        }

        $selects = [
            ['tipo' => $request->select_grande_1, 'valor' => $request->input_grande_1_value],
            ['tipo' => $request->select_grande_2, 'valor' => $request->input_grande_2_value],
            ['tipo' => $request->select_grande_3, 'valor' => $request->input_grande_3_value],
        ];

        $letra = null;
        $contador_actual = 0;
        $contador_padding = 0;
        $anio = null;
        
        foreach ($selects as $s) {
            if ($s['tipo'] === 'LETRA') {
                $letra = $s['valor'];
            }
            if ($s['tipo'] === 'DIGITO') {
                $contador_actual = (int) ($s['valor'] ?: 0);
                $contador_padding = strlen($s['valor'] ?? '');
            }
            if ($s['tipo'] === 'AÑO') {
                $anio = $s['valor'];
            }
        }

        $partes = [];
        if ($request->select_grande_1 !== 'NADA')
            $partes[] = $request->select_grande_1 === 'DIGITO' ? '{N}' : $request->input_grande_1_value;
        if ($request->select_pequeno_1 !== 'NADA')
            $partes[] = $request->select_pequeno_1;
        if ($request->select_grande_2 !== 'NADA')
            $partes[] = $request->select_grande_2 === 'DIGITO' ? '{N}' : $request->input_grande_2_value;
        if ($request->select_pequeno_2 !== 'NADA')
            $partes[] = $request->select_pequeno_2;
        if ($request->select_grande_3 !== 'NADA')
            $partes[] = $request->select_grande_3 === 'DIGITO' ? '{N}' : $request->input_grande_3_value;
            
        $formatoFinal = implode('', $partes);

        $regexPattern = '/^' . str_replace('\{N\}', '(\d+)', preg_quote($formatoFinal, '/')) . '$/';
        $likePattern = str_replace('{N}', '%', $formatoFinal);

        $facturasQuery = \App\Models\Factura::whereNotIn('estado', ['Anulada'])
            ->whereNotNull('numero_solicitud')
            ->where('numero_solicitud', 'LIKE', $likePattern);

        if ($tipo === 'empresa') {
            $facturasQuery->where('id_empresa_cliente', $id);
        } elseif ($tipo === 'cliente') {
            $facturasQuery->where('id_cliente', $id);
        }

        $maxContador = null;
        foreach ($facturasQuery->pluck('numero_solicitud') as $correlativo) {
            if (preg_match($regexPattern, $correlativo, $matches)) {
                $numero = (int)$matches[1];
                if ($maxContador === null || $numero > $maxContador) {
                    $maxContador = $numero;
                }
            }
        }

        if ($maxContador !== null) {
            $contador_actual = max($contador_actual, $maxContador);
        }

        FormatoSolicitudes::create([
            'id_empresas_clientes' => $tipo === 'empresa' ? $id : null,
            'id_cliente' => $tipo === 'cliente' ? $id : null,
            'letra' => $letra,
            'signo1' => $request->select_pequeno_1 !== 'NADA' ? $request->select_pequeno_1 : null,
            'contador_actual' => $contador_actual,
            'contador_padding' => $contador_padding,
            'signo2' => $request->select_pequeno_2 !== 'NADA' ? $request->select_pequeno_2 : null,
            'anio' => $anio,
            'formato_final' => $formatoFinal,
        ]);

        return redirect()->route('formato_n_solicitud.index')->with('success', 'Formato configurado correctamente.');
    }

    public function edit($id)
    {
        $formato = FormatoSolicitudes::findOrFail($id);

        $template = $formato->formato_final;
        $signo1 = $formato->signo1;
        $signo2 = $formato->signo2;

        $temp = $template;

        if ($signo2 && str_contains($temp, $signo2)) {
            $idx = strrpos($temp, $signo2);
            $antes = substr($temp, 0, $idx);
            $pos3_raw = substr($temp, $idx + strlen($signo2));
            $temp = $antes;
        } else {
            $pos3_raw = '';
        }

        if ($signo1 && str_contains($temp, $signo1)) {
            [$pos1_raw, $pos2_raw] = explode($signo1, $temp, 2);
        } else {
            if (str_starts_with($temp, '{N}')) {
                $pos1_raw = '{N}';
                $pos2_raw = substr($temp, 3);
            } elseif ($formato->anio && str_ends_with($temp, (string) $formato->anio)) {
                $pos2_raw = (string) $formato->anio;
                $pos1_raw = substr($temp, 0, -strlen($formato->anio));
            } elseif (str_ends_with($temp, '{N}')) {
                $pos1_raw = substr($temp, 0, -3);
                $pos2_raw = '{N}';
            } else {
                $pos1_raw = '';
                $pos2_raw = '';

                $tieneN = str_contains($temp, '{N}');
                $tieneAnio = $formato->anio && str_contains($temp, (string) $formato->anio);
                $tieneLetra = $formato->letra && str_contains($temp, $formato->letra);

                if ($tieneN && $tieneAnio) {
                    if (str_starts_with($temp, '{N}')) {
                        $pos1_raw = '{N}';
                        $pos2_raw = (string) $formato->anio;
                    } else {
                        $pos1_raw = (string) $formato->anio;
                        $pos2_raw = '{N}';
                    }
                } elseif ($tieneLetra && $tieneAnio) {
                    if (str_starts_with($temp, $formato->letra)) {
                        $pos1_raw = $formato->letra;
                        $pos2_raw = (string) $formato->anio;
                    } else {
                        $pos1_raw = (string) $formato->anio;
                        $pos2_raw = $formato->letra;
                    }
                } elseif ($tieneLetra && $tieneN) {
                    if (str_starts_with($temp, '{N}')) {
                        $pos1_raw = '{N}';
                        $pos2_raw = $formato->letra;
                    } else {
                        $pos1_raw = $formato->letra;
                        $pos2_raw = '{N}';
                    }
                } elseif ($tieneN) {
                    $pos1_raw = '{N}';
                } elseif ($tieneAnio) {
                    $pos1_raw = (string) $formato->anio;
                } else {
                    $pos1_raw = $temp;
                }
            }
        }

        $determinarTipo = function ($valor) use ($formato) {
            if ($valor === '{N}')
                return ['tipo' => 'DIGITO', 'valor' => str_pad($formato->contador_actual, $formato->contador_padding ?: 1, '0', STR_PAD_LEFT)];
            if ($valor && $valor == $formato->anio)
                return ['tipo' => 'AÑO', 'valor' => $valor];
            if ($valor)
                return ['tipo' => 'LETRA', 'valor' => $valor];
            return ['tipo' => 'NADA', 'valor' => ''];
        };


        if ($pos3_raw === '') {
            if (str_ends_with($pos2_raw, '{N}')) {
                $pos3_raw = '{N}';
                $pos2_raw = substr($pos2_raw, 0, -3);
            } elseif (str_starts_with($pos2_raw, '{N}')) {
                $pos3_raw = substr($pos2_raw, 3);
                $pos2_raw = '{N}';
            } elseif ($formato->anio && str_ends_with($pos2_raw, (string) $formato->anio)) {
                $pos3_raw = (string) $formato->anio;
                $pos2_raw = substr($pos2_raw, 0, -strlen($formato->anio));
            } elseif ($formato->anio && str_starts_with($pos2_raw, (string) $formato->anio)) {
                $pos3_raw = substr($pos2_raw, strlen((string) $formato->anio));
                $pos2_raw = (string) $formato->anio;
            }
        }

        $pos1 = $determinarTipo($pos1_raw);
        $pos2 = $determinarTipo($pos2_raw);
        $pos3 = $determinarTipo($pos3_raw);

        $empresas = EmpresasClientes::orderBy('razon_social')->get();
        $clientes = ClienteAuditoria::orderBy('razon_social')->get();

        return view('formato_n_solicitud.edit', compact('formato', 'empresas', 'clientes', 'pos1', 'pos2', 'pos3', 'signo1', 'signo2'));
    }

    public function update(Request $request, $id)
    {
        $formato = FormatoSolicitudes::findOrFail($id);
        
        $selects = [
            ['tipo' => $request->select_grande_1, 'valor' => $request->input_grande_1_value],
            ['tipo' => $request->select_grande_2, 'valor' => $request->input_grande_2_value],
            ['tipo' => $request->select_grande_3, 'valor' => $request->input_grande_3_value],
        ];
        
        $letra = null;
        $contador_actual = 0;
        $contador_padding = 0;
        $anio = null;
        
        foreach ($selects as $s) {
            if ($s['tipo'] === 'LETRA') {
                $letra = $s['valor'];
            }
            if ($s['tipo'] === 'DIGITO') {
                $contador_actual = (int) ($s['valor'] ?: 0);
                $contador_padding = strlen($s['valor'] ?? '');
            }
            if ($s['tipo'] === 'AÑO') {
                $anio = $s['valor'];
            }
        }

        $partes = [];
        if ($request->select_grande_1 !== 'NADA')
            $partes[] = $request->select_grande_1 === 'DIGITO' ? '{N}' : $request->input_grande_1_value;
        if ($request->select_pequeno_1 !== 'NADA')
            $partes[] = $request->select_pequeno_1;
        if ($request->select_grande_2 !== 'NADA')
            $partes[] = $request->select_grande_2 === 'DIGITO' ? '{N}' : $request->input_grande_2_value;
        if ($request->select_pequeno_2 !== 'NADA')
            $partes[] = $request->select_pequeno_2;
        if ($request->select_grande_3 !== 'NADA')
            $partes[] = $request->select_grande_3 === 'DIGITO' ? '{N}' : $request->input_grande_3_value;

        $formatoFinal = implode('', $partes);

        $tipo = $formato->id_empresas_clientes ? 'empresa' : 'cliente';
        $target_id = $formato->id_empresas_clientes ?: $formato->id_cliente;

        $regexPattern = '/^' . str_replace('\{N\}', '(\d+)', preg_quote($formatoFinal, '/')) . '$/';
        $likePattern = str_replace('{N}', '%', $formatoFinal);

        $facturasQuery = \App\Models\Factura::whereNotIn('estado', ['Anulada'])
            ->whereNotNull('numero_solicitud')
            ->where('numero_solicitud', 'LIKE', $likePattern);

        if ($tipo === 'empresa') {
            $facturasQuery->where('id_empresa_cliente', $target_id);
        } elseif ($tipo === 'cliente') {
            $facturasQuery->where('id_cliente', $target_id);
        }

        $maxContador = null;
        foreach ($facturasQuery->pluck('numero_solicitud') as $correlativo) {
            if (preg_match($regexPattern, $correlativo, $matches)) {
                $numero = (int)$matches[1];
                if ($maxContador === null || $numero > $maxContador) {
                    $maxContador = $numero;
                }
            }
        }

        if ($maxContador !== null) {
            $contador_actual = max($contador_actual, $maxContador);
        }

        $formato->letra = $letra;
        $formato->signo1 = $request->select_pequeno_1 !== 'NADA' ? $request->select_pequeno_1 : null;
        $formato->contador_actual = $contador_actual;
        $formato->contador_padding = $contador_padding;
        $formato->signo2 = $request->select_pequeno_2 !== 'NADA' ? $request->select_pequeno_2 : null;
        $formato->anio = $anio;
        $formato->formato_final = $formatoFinal;
        $formato->save();

        return redirect()->route('formato_n_solicitud.index')->with('success', 'Formato actualizado correctamente.');
    }

    public function getFormatoAjax(Request $request)
    {
        $id_empresa_input = $request->id_empresa;
        if (!$id_empresa_input)
            return response()->json(['success' => false]);
            
        [$tipo, $id] = explode('_', $id_empresa_input, 2);
        $query = FormatoSolicitudes::query();
        
        if ($tipo === 'empresa') {
            $query->where('id_empresas_clientes', $id);
        } else {
            $query->where('id_cliente', $id);
        }
        
        $formato = $query->first();
        if ($formato) {
            return response()->json([
                'success' => true,
                'formato' => $formato->generarCadenaFormateada($formato->contador_actual + 1)
            ]);
        }

        return response()->json(['success' => false]);
    }

    public function getUltimasDiezAjax(Request $request)
    {
        $id_empresa_input = $request->id_empresa;
        $limite = $request->limite ?? 10;
        
        if (!$id_empresa_input) return response()->json(['success' => false, 'data' => []]);

        [$tipo, $id] = explode('_', $id_empresa_input, 2);
        
        $facturasQuery = \App\Models\Factura::with(['empresa', 'cliente'])
            ->whereNotNull('numero_solicitud')
            ->where('numero_solicitud', '!=', '');

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

        $facturas = $facturasQuery->get()->map(function($f) {
                return [
                    'id_factura' => $f->id_factura,
                    'fecha' => $f->fecha_emision ? date('d/m/Y', strtotime($f->fecha_emision)) : ($f->created_at ? $f->created_at->format('d/m/Y') : ''),
                    'numero' => $f->numero_solicitud,
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

    /**
     * Recalcula el contador_actual de FormatoSolicitudes al máximo valor presente
     * en las facturas activas (no anuladas) del receptor.
     */
    public static function recalcularContador($id_cliente = null, $id_empresa_cliente = null, ?FormatoSolicitudes $formato = null): int
    {
        if (!$formato) {
            $query = FormatoSolicitudes::query();
            if ($id_empresa_cliente) {
                $query->where('id_empresas_clientes', $id_empresa_cliente);
            } elseif ($id_cliente) {
                $query->where('id_cliente', $id_cliente);
            } else {
                return 0;
            }
            $formato = $query->latest('id_formato_solicitudes')->first();
        }

        if (!$formato || empty($formato->formato_final)) {
            return 0;
        }

        $regexPattern = '/^' . str_replace('\{N\}', '(\d+)', preg_quote($formato->formato_final, '/')) . '$/';
        $likePattern = str_replace('{N}', '%', $formato->formato_final);

        $facturasQuery = \App\Models\Factura::whereNotIn('estado', ['Anulada'])
            ->whereNotNull('numero_solicitud')
            ->where('numero_solicitud', 'LIKE', $likePattern);

        if ($formato->id_empresas_clientes) {
            $facturasQuery->where('id_empresa_cliente', $formato->id_empresas_clientes);
        } elseif ($formato->id_cliente) {
            $facturasQuery->where('id_cliente', $formato->id_cliente);
        }

        $maxContador = 0;
        foreach ($facturasQuery->pluck('numero_solicitud') as $solicitud) {
            if (preg_match($regexPattern, $solicitud, $matches)) {
                $numero = (int) $matches[1];
                if ($numero > $maxContador) {
                    $maxContador = $numero;
                }
            }
        }

        if ($formato->contador_actual !== $maxContador) {
            $formato->contador_actual = $maxContador;
            $formato->save();
        }

        return $maxContador;
    }
}
