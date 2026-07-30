<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmpresasClientes;
use App\Models\FormatoCotizaciones2;
use App\Models\Factura;
use Carbon\Carbon;

class FormatoCotizaciones2Controller extends Controller
{
    /* ------------------------------------------------------------------ */
    /*  INDEX                                                               */
    /* ------------------------------------------------------------------ */
    public function index()
    {
        $formatos = FormatoCotizaciones2::with('emisor')->orderBy('id_formato_cotizaciones_2')->get();
        return view('formato_cotizaciones_2.index', compact('formatos'));
    }

    /* ------------------------------------------------------------------ */
    /*  CREATE                                                              */
    /* ------------------------------------------------------------------ */
    public function create()
    {
        // Solo empresas que aún no tienen formato
        $emisoresOcupados = FormatoCotizaciones2::pluck('id_empresas_clientes_emisor')->toArray();
        $empresas = EmpresasClientes::whereNotIn('id_empresas_clientes', $emisoresOcupados)
            ->orderBy('razon_social')
            ->get();

        return view('formato_cotizaciones_2.create', compact('empresas'));
    }

    /* ------------------------------------------------------------------ */
    /*  STORE                                                               */
    /* ------------------------------------------------------------------ */
    public function store(Request $request)
    {
        $request->validate([
            'id_empresa_emisor' => 'required|exists:empresas_clientes,id_empresas_clientes',
        ]);

        $idEmisor = $request->id_empresa_emisor;

        // Un solo formato por emisor
        if (FormatoCotizaciones2::where('id_empresas_clientes_emisor', $idEmisor)->exists()) {
            return redirect()->back()->with('error', 'Ya existe un formato registrado para este emisor.');
        }

        [$letra, $signo1, $contador_actual, $contador_padding, $signo2, $anio, $formatoFinal] =
            $this->parsearFormulario($request);

        // Auto-detectar max contador existente en facturas del emisor
        $contador_actual = $this->detectarMaxContador($idEmisor, $formatoFinal, $contador_actual);

        FormatoCotizaciones2::create([
            'id_empresas_clientes_emisor' => $idEmisor,
            'letra'             => $letra,
            'signo1'            => $signo1,
            'contador_actual'   => $contador_actual,
            'contador_padding'  => $contador_padding,
            'signo2'            => $signo2,
            'anio'              => $anio,
            'formato_final'     => $formatoFinal,
        ]);

        return redirect()->route('formato_cotizaciones_2.index')
            ->with('success', 'Formato de cotización (v2) creado correctamente.');
    }

    /* ------------------------------------------------------------------ */
    /*  EDIT                                                                */
    /* ------------------------------------------------------------------ */
    public function edit($id)
    {
        $formato = FormatoCotizaciones2::with('emisor')->findOrFail($id);

        [$pos1, $signo1, $pos2, $signo2, $pos3] = $this->descomponerFormato($formato);

        $empresas = EmpresasClientes::orderBy('razon_social')->get();

        return view('formato_cotizaciones_2.edit', compact(
            'formato', 'empresas',
            'pos1', 'signo1', 'pos2', 'signo2', 'pos3'
        ));
    }

    /* ------------------------------------------------------------------ */
    /*  UPDATE                                                              */
    /* ------------------------------------------------------------------ */
    public function update(Request $request, $id)
    {
        $formato = FormatoCotizaciones2::findOrFail($id);

        [$letra, $signo1, $contador_actual, $contador_padding, $signo2, $anio, $formatoFinal] =
            $this->parsearFormulario($request);

        // Eliminamos detectarMaxContador para permitir que el usuario establezca el contador que desee
        // $contador_actual = $this->detectarMaxContador(
        //     $formato->id_empresas_clientes_emisor,
        //     $formatoFinal,
        //     $contador_actual
        // );

        $formato->update([
            'letra'             => $letra,
            'signo1'            => $signo1,
            'contador_actual'   => $contador_actual,
            'contador_padding'  => $contador_padding,
            'signo2'            => $signo2,
            'anio'              => $anio,
            'formato_final'     => $formatoFinal,
        ]);

        return redirect()->route('formato_cotizaciones_2.index')
            ->with('success', 'Formato de cotización (v2) actualizado correctamente.');
    }

    /* ================================================================== */
    /*  AJAX — getFormatoAjax                                              */
    /*                                                                     */
    /*  Calcula el PRÓXIMO nro_cotizacion para el emisor dado y            */
    /*  la fecha_emision indicada. No guarda nada, solo devuelve           */
    /*  el valor esperado.                                                 */
    /* ================================================================== */
    public function getFormatoAjax(Request $request)
    {
        $idEmisor    = $request->id_empresa_emisor;
        $fechaEmision = $request->fecha_emision; // Y-m-d

        if (!$idEmisor) {
            return response()->json(['success' => false]);
        }

        $formato = FormatoCotizaciones2::where('id_empresas_clientes_emisor', $idEmisor)->first();
        if (!$formato || empty($formato->formato_final)) {
            return response()->json(['success' => false]);
        }

        $siguiente = $this->calcularSiguienteNro($formato, $fechaEmision);

        return response()->json([
            'success' => true,
            'formato' => $siguiente,
        ]);
    }

    public function getNumerosDisponiblesAjax(Request $request)
    {
        $idEmisor    = $request->id_empresa_emisor;
        $fechaEmision = $request->fecha_emision; // Y-m-d
        $idFactura    = $request->id_factura;

        if (!$idEmisor) {
            return response()->json(['success' => false]);
        }

        $formato = FormatoCotizaciones2::where('id_empresas_clientes_emisor', $idEmisor)->first();
        if (!$formato || empty($formato->formato_final)) {
            return response()->json(['success' => false]);
        }

        $disponibles = self::calcularNumerosDisponibles($formato, $fechaEmision, $idFactura ? (int)$idFactura : null);

        return response()->json([
            'success' => true,
            'disponibles' => $disponibles,
        ]);
    }

    /* ================================================================== */
    /*  AJAX — getUltimasDiezAjax                                          */
    /*  Historial de nro_cotizacion para el emisor (sin filtrar receptor)  */
    /* ================================================================== */
    public function getUltimasDiezAjax(Request $request)
    {
        $idEmisor = $request->id_empresa_emisor;
        $limite   = $request->limite ?? 10;

        if (!$idEmisor) {
            return response()->json(['success' => false, 'data' => []]);
        }

        $emisor = EmpresasClientes::find($idEmisor);
        if (!$emisor) {
            return response()->json(['success' => false, 'data' => []]);
        }

        $query = Factura::with(['empresa', 'cliente'])
            ->where('ruc_emisor', $emisor->ruc)
            ->whereNotNull('nro_cotizacion')
            ->where('nro_cotizacion', '!=', '')
            ->where('nro_cotizacion', 'NOT LIKE', 'PENDIENTE-%');

        $total_records = $query->count();

        $query = $query->latest('id_factura');
        if ($limite !== 'todos') {
            $query->take((int) $limite);
        }

        $facturas = $query->get()->map(function ($f) {
            return [
                'id_factura'         => $f->id_factura,
                'fecha'              => $f->fecha_emision
                    ? date('d/m/Y', strtotime($f->fecha_emision))
                    : ($f->created_at ? $f->created_at->format('d/m/Y') : ''),
                'numero'             => $f->nro_cotizacion,
                'receptor'           => $f->empresa?->razon_social ?? $f->cliente?->razon_social ?? '',
                'total_neto'         => $f->total_neto,
                'tipo_moneda'        => $f->tipo_moneda,
                'precio_dolar'       => $f->precio_dolar,
                'precio_dolar_factura' => $f->precio_dolar_factura,
                'url_detalle'        => route('factura.detalle', $f->id_factura),
            ];
        });

        return response()->json([
            'success'       => true,
            'data'          => $facturas,
            'total_records' => $total_records,
        ]);
    }

    /* ================================================================== */
    /*  HELPER: calcularSiguienteNro                                       */
    /*                                                                     */
    /*  Reglas de negocio:                                                 */
    /*  - Cada fecha tiene su propio rango de números (los pares saltados) */
    /*  - Al agregar a una fecha existente, se llenan los huecos de esa    */
    /*    fecha (números entre min y max de esa fecha que no están usados) */
    /*  - Si la fecha no tiene números aún, se aplica salto desde el max   */
    /*    global: últimos 7 días del mes → +6, resto → +2                  */
    /*  - Si no hay huecos disponibles en la fecha → devuelve null         */
    /*    (la fecha está llena, no se puede crear más)                     */
    /* ================================================================== */
    public static function calcularSiguienteNro(FormatoCotizaciones2 $formato, ?string $fechaEmision = null, ?int $excluirIdFactura = null): string
    {
        $fecha = $fechaEmision ? Carbon::parse($fechaEmision) : now();
        $fechaStr = $fecha->toDateString(); // Y-m-d

        // Determinar salto según posición en el mes
        $diasEnElMes = $fecha->daysInMonth;
        $diaActual   = $fecha->day;
        $salto = ($diasEnElMes - $diaActual < 7) ? 6 : 2;

        $emisor = EmpresasClientes::find($formato->id_empresas_clientes_emisor);
        if (!$emisor) {
            return $formato->generarCadenaFormateada($formato->contador_actual + $salto);
        }

        $regex = '/^' . str_replace('\{N\}', '(\d+)', preg_quote($formato->formato_final, '/')) . '$/';
        $like  = str_replace('{N}', '%', $formato->formato_final);

        // Obtener todos los números usados con su fecha de emisión
        $filas = Factura::where('ruc_emisor', $emisor->ruc)
            ->whereNotIn('estado', ['Anulada'])
            ->whereNotNull('nro_cotizacion')
            ->where('nro_cotizacion', 'LIKE', $like)
            ->when($excluirIdFactura, fn($q) => $q->where('id_factura', '!=', $excluirIdFactura))
            ->get(['nro_cotizacion', 'fecha_emision']);

        $porFecha = [];
        $todosLosNumeros = [];
        foreach ($filas as $fila) {
            if (!preg_match($regex, $fila->nro_cotizacion, $m)) continue;
            $num = (int) $m[1];
            $f   = Carbon::parse($fila->fecha_emision)->toDateString();
            $porFecha[$f][] = $num;
            $todosLosNumeros[] = $num;
        }

        // Si no hay ningún número registrado, generar desde el contador base
        if (empty($todosLosNumeros)) {
            $base = $formato->contador_actual;
            return $formato->generarCadenaFormateada($base == 0 ? 1 : $base + $salto);
        }

        $maxGlobal = max($todosLosNumeros);

        // prev_max: mayor número de fechas ESTRICTAMENTE anteriores a fechaStr
        // Inicia en 0, NO en contador_actual (que refleja el máximo global, no el de fechas previas)
        $prev_max = 0;
        foreach ($porFecha as $f => $nums) {
            if ($f < $fechaStr) {
                $maxF = max($nums);
                if ($maxF > $prev_max) $prev_max = $maxF;
            }
        }

        // next_min: menor número de fechas ESTRICTAMENTE posteriores a fechaStr
        $next_min = null;
        foreach ($porFecha as $f => $nums) {
            if ($f > $fechaStr) {
                $minF = min($nums);
                if ($next_min === null || $minF < $next_min) $next_min = $minF;
            }
        }

        // Números que YA existen en esta fecha
        $numerosEnFecha = $porFecha[$fechaStr] ?? [];

        if (!empty($numerosEnFecha)) {
            // --- La fecha YA tiene números ---
            $minFecha = min($numerosEnFecha);
            $maxFecha = max($numerosEnFecha);

            if ($next_min !== null) {
                // Huecos desde minFecha hasta next_min - 1
                for ($i = $minFecha; $i < $next_min; $i++) {
                    if (!in_array($i, $todosLosNumeros)) {
                        return $formato->generarCadenaFormateada($i);
                    }
                }
                return 'FECHA_LLENA';
            } else {
                // Es la última fecha (no hay posteriores): saltar normal desde el max global
                return $formato->generarCadenaFormateada($maxGlobal + $salto);
            }
        } else {
            // --- La fecha NO tiene números aún ---
            if ($next_min !== null) {
                // Buscar huecos entre prev_max y next_min
                for ($i = $prev_max + 1; $i < $next_min; $i++) {
                    if (!in_array($i, $todosLosNumeros)) {
                        return $formato->generarCadenaFormateada($i);
                    }
                }
                return 'FECHA_LLENA';
            } else {
                // Es una fecha nueva al final: saltar normal desde el max global
                return $formato->generarCadenaFormateada($maxGlobal + $salto);
            }
        }
    }

    public static function calcularNumerosDisponibles(FormatoCotizaciones2 $formato, ?string $fechaEmision = null, ?int $excluirIdFactura = null): array
    {
        $fecha = $fechaEmision ? Carbon::parse($fechaEmision) : now();
        $fechaStr = $fecha->toDateString(); // Y-m-d

        $diasEnElMes = $fecha->daysInMonth;
        $diaActual   = $fecha->day;
        $salto = ($diasEnElMes - $diaActual < 7) ? 6 : 2;

        $emisor = EmpresasClientes::find($formato->id_empresas_clientes_emisor);
        if (!$emisor) {
            return [];
        }

        $regex = '/^' . str_replace('\{N\}', '(\d+)', preg_quote($formato->formato_final, '/')) . '$/';
        $like  = str_replace('{N}', '%', $formato->formato_final);

        $filas = Factura::where('ruc_emisor', $emisor->ruc)
            ->whereNotIn('estado', ['Anulada'])
            ->whereNotNull('nro_cotizacion')
            ->where('nro_cotizacion', 'LIKE', $like)
            ->when($excluirIdFactura, fn($q) => $q->where('id_factura', '!=', $excluirIdFactura))
            ->get(['nro_cotizacion', 'fecha_emision']);

        $porFecha = [];
        $todosLosNumeros = [];
        foreach ($filas as $fila) {
            if (!preg_match($regex, $fila->nro_cotizacion, $m)) continue;
            $num = (int) $m[1];
            $f   = Carbon::parse($fila->fecha_emision)->toDateString();
            $porFecha[$f][] = $num;
            $todosLosNumeros[] = $num;
        }

        // Sin números: generar secuencia inicial
        if (empty($todosLosNumeros)) {
            $base = $formato->contador_actual;
            $disponibles = [];
            if ($base == 0) {
                $disponibles[] = 1;
                $curr = 1;
                for ($j = 1; $j < 10; $j++) {
                    $curr += $salto;
                    $disponibles[] = $curr;
                }
            } else {
                for ($j = 1; $j <= 10; $j++) {
                    $disponibles[] = $base + ($salto * $j);
                }
            }
            return array_map(fn($d) => $formato->generarCadenaFormateada($d), $disponibles);
        }

        $maxGlobal = max($todosLosNumeros);

        // prev_max: mayor número de fechas ESTRICTAMENTE anteriores (0 si no hay)
        $prev_max = 0;
        foreach ($porFecha as $f => $nums) {
            if ($f < $fechaStr) {
                $maxF = max($nums);
                if ($maxF > $prev_max) $prev_max = $maxF;
            }
        }

        // next_min: menor número de fechas ESTRICTAMENTE posteriores
        $next_min = null;
        foreach ($porFecha as $f => $nums) {
            if ($f > $fechaStr) {
                $minF = min($nums);
                if ($next_min === null || $minF < $next_min) $next_min = $minF;
            }
        }

        $numerosEnFecha = $porFecha[$fechaStr] ?? [];
        $disponibles = [];

        if (!empty($numerosEnFecha)) {
            // --- La fecha YA tiene números ---
            $minFecha = min($numerosEnFecha);
            $maxFecha = max($numerosEnFecha);

            if ($next_min !== null) {
                // Huecos desde minFecha hasta next_min - 1
                for ($i = $minFecha; $i < $next_min; $i++) {
                    if (!in_array($i, $todosLosNumeros)) {
                        $disponibles[] = $i;
                    }
                }
            } else {
                // Es la última fecha: saltar normal hacia adelante
                for ($j = 1; $j <= 10; $j++) {
                    $disponibles[] = $maxGlobal + ($salto * $j);
                }
            }
        } else {
            // --- La fecha NO tiene números ---
            if ($next_min !== null) {
                // Huecos entre prev_max y next_min
                for ($i = $prev_max + 1; $i < $next_min; $i++) {
                    if (!in_array($i, $todosLosNumeros)) {
                        $disponibles[] = $i;
                    }
                }
            } else {
                // Es una fecha nueva al final: saltar normal hacia adelante
                for ($j = 1; $j <= 10; $j++) {
                    $disponibles[] = $maxGlobal + ($salto * $j);
                }
            }
        }

        $disponibles = array_unique($disponibles);
        sort($disponibles);

        return array_map(fn($d) => $formato->generarCadenaFormateada($d), $disponibles);
    }

    /* ================================================================== */
    /*  HELPER: asignarNroCotizacion                                       */
    /*  Se llama desde FacturaController al crear/guardar una factura.     */
    /* ================================================================== */
    public static function asignarNroCotizacion(
        FormatoCotizaciones2 $formato,
        string $fechaEmision,
        ?int $idFacturaExcluir = null
    ): string {
        $emisor = EmpresasClientes::find($formato->id_empresas_clientes_emisor);
        if (!$emisor) {
            throw new \RuntimeException('Emisor no encontrado en el formato de cotización.');
        }

        // Validar unicidad de fecha_emision por emisor
        $duplicado = Factura::where('ruc_emisor', $emisor->ruc)
            ->whereDate('fecha_emision', $fechaEmision)
            ->whereNotIn('estado', ['Anulada'])
            ->when($idFacturaExcluir, fn($q) => $q->where('id_factura', '!=', $idFacturaExcluir))
            ->exists();

        if ($duplicado) {
            throw new \RuntimeException(
                'Ya existe una factura con fecha de emisión ' . $fechaEmision . ' para el emisor. No se puede registrar otro comprobante en la misma fecha.'
            );
        }

        $siguiente = self::calcularSiguienteNro($formato, $fechaEmision);

        // Actualizar contador_actual si el nuevo número es mayor
        $regex = '/^' . str_replace('\{N\}', '(\d+)', preg_quote($formato->formato_final, '/')) . '$/';
        if (preg_match($regex, $siguiente, $m)) {
            $numero = (int) $m[1];
            if ($numero > $formato->contador_actual) {
                $formato->contador_actual = $numero;
                $formato->save();
            }
        }

        return $siguiente;
    }

    /* ================================================================== */
    /*  PRIVATE HELPERS                                                    */
    /* ================================================================== */

    private function parsearFormulario(Request $request): array
    {
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
                $letra = strtoupper(trim($s['valor'] ?? '')) ?: null;
            }
            if ($s['tipo'] === 'DIGITO') {
                $raw = $s['valor'] ?? '0';
                $contador_actual  = (int) $raw;
                $contador_padding = max(1, strlen($raw));
            }
            if ($s['tipo'] === 'AÑO') {
                $anio = $s['valor'];
            }
        }

        $partes = [];
        if ($request->select_grande_1 !== 'NADA')
            $partes[] = $request->select_grande_1 === 'DIGITO' ? '{N}' : strtoupper(trim($request->input_grande_1_value ?? ''));
        if ($request->select_pequeno_1 !== 'NADA')
            $partes[] = $request->select_pequeno_1;
        if ($request->select_grande_2 !== 'NADA')
            $partes[] = $request->select_grande_2 === 'DIGITO' ? '{N}' : strtoupper(trim($request->input_grande_2_value ?? ''));
        if ($request->select_pequeno_2 !== 'NADA')
            $partes[] = $request->select_pequeno_2;
        if ($request->select_grande_3 !== 'NADA')
            $partes[] = $request->select_grande_3 === 'DIGITO' ? '{N}' : strtoupper(trim($request->input_grande_3_value ?? ''));

        $formatoFinal = implode('', $partes);

        $signo1 = $request->select_pequeno_1 !== 'NADA' ? $request->select_pequeno_1 : null;
        $signo2 = $request->select_pequeno_2 !== 'NADA' ? $request->select_pequeno_2 : null;

        return [$letra, $signo1, $contador_actual, $contador_padding, $signo2, $anio, $formatoFinal];
    }

    private function detectarMaxContador(int $idEmisor, string $formatoFinal, int $contadorBase): int
    {
        $emisor = EmpresasClientes::find($idEmisor);
        if (!$emisor) return $contadorBase;

        $regex = '/^' . str_replace('\{N\}', '(\d+)', preg_quote($formatoFinal, '/')) . '$/';
        $like  = str_replace('{N}', '%', $formatoFinal);

        $max = null;
        Factura::where('ruc_emisor', $emisor->ruc)
            ->whereNotNull('nro_cotizacion')
            ->where('nro_cotizacion', 'LIKE', $like)
            ->pluck('nro_cotizacion')
            ->each(function ($v) use ($regex, &$max) {
                if (preg_match($regex, $v, $m)) {
                    $n = (int) $m[1];
                    if ($max === null || $n > $max) $max = $n;
                }
            });

        return ($max !== null) ? max($contadorBase, $max) : $contadorBase;
    }

    private function descomponerFormato(FormatoCotizaciones2 $formato): array
    {
        $template = $formato->formato_final ?? '';
        $signo1   = $formato->signo1;
        $signo2   = $formato->signo2;

        $temp = $template;
        $pos3_raw = '';

        // Separar pos3 (después del signo2)
        if ($signo2 && str_contains($temp, $signo2)) {
            $idx = strrpos($temp, $signo2);
            $pos3_raw = substr($temp, $idx + strlen($signo2));
            $temp     = substr($temp, 0, $idx);
        }

        // Separar pos1 y pos2 (por signo1)
        if ($signo1 && str_contains($temp, $signo1)) {
            [$pos1_raw, $pos2_raw] = explode($signo1, $temp, 2);
        } elseif (str_starts_with($temp, '{N}')) {
            $pos1_raw = '{N}';
            $pos2_raw = substr($temp, 3);
        } elseif (str_ends_with($temp, '{N}')) {
            $pos1_raw = substr($temp, 0, -3);
            $pos2_raw = '{N}';
        } else {
            $pos1_raw = $temp;
            $pos2_raw = '';
        }

        // Si pos3 vacío pero pos2 contiene más tokens
        if ($pos3_raw === '') {
            if (str_ends_with($pos2_raw, '{N}')) {
                $pos3_raw = '{N}';
                $pos2_raw = substr($pos2_raw, 0, -3);
            } elseif ($formato->anio && str_ends_with($pos2_raw, (string) $formato->anio)) {
                $pos3_raw = (string) $formato->anio;
                $pos2_raw = substr($pos2_raw, 0, -strlen($formato->anio));
            }
        }

        $determinar = function ($valor) use ($formato) {
            if ($valor === '{N}')
                return ['tipo' => 'DIGITO', 'valor' => str_pad($formato->contador_actual, $formato->contador_padding ?: 1, '0', STR_PAD_LEFT)];
            if ($valor && $valor == $formato->anio)
                return ['tipo' => 'AÑO', 'valor' => $valor];
            if ($valor)
                return ['tipo' => 'LETRA', 'valor' => $valor];
            return ['tipo' => 'NADA', 'valor' => ''];
        };

        return [
            $determinar($pos1_raw),
            $signo1,
            $determinar($pos2_raw),
            $signo2,
            $determinar($pos3_raw),
        ];
    }

    /**
     * Recalcula el contador_actual del FormatoCotizaciones2 al máximo valor presente
     * en las facturas activas (no anuladas) del emisor.
     */
    public static function recalcularContador(?int $idEmisor = null, ?FormatoCotizaciones2 $formato = null): int
    {
        if (!$formato) {
            if (!$idEmisor) return 0;
            $formato = FormatoCotizaciones2::where('id_empresas_clientes_emisor', $idEmisor)->first();
        }

        if (!$formato || empty($formato->formato_final)) {
            return 0;
        }

        $emisor = EmpresasClientes::find($formato->id_empresas_clientes_emisor);
        if (!$emisor) {
            return 0;
        }

        $regexPattern = '/^' . str_replace('\{N\}', '(\d+)', preg_quote($formato->formato_final, '/')) . '$/';
        $likePattern = str_replace('{N}', '%', $formato->formato_final);

        $facturas = Factura::where('ruc_emisor', $emisor->ruc)
            ->whereNotIn('estado', ['Anulada'])
            ->whereNotNull('nro_cotizacion')
            ->where('nro_cotizacion', 'LIKE', $likePattern)
            ->pluck('nro_cotizacion');

        $maxContador = 0;
        foreach ($facturas as $cotizacion) {
            if (preg_match($regexPattern, $cotizacion, $matches)) {
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
