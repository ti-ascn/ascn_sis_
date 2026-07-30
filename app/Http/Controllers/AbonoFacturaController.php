<?php

namespace App\Http\Controllers;

use App\Models\AbonoFactura;
use App\Models\ClienteAuditoria;
use App\Models\EmpresasClientes;
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Guia;

class AbonoFacturaController extends Controller
{
    /* =========================================================
     |  INDEX
     ========================================================= */
    public function index(Request $request)
    {
        $reqDesde = $request->input('desde');
        $reqHasta = $request->input('hasta');

        if (empty($reqDesde) && empty($reqHasta)) {
            $desde = now()->format('Y-m');
            $hasta = now()->format('Y-m');
        } elseif (empty($reqDesde)) {
            $desde = $reqHasta;
            $hasta = $reqHasta;
        } elseif (empty($reqHasta)) {
            $desde = $reqDesde;
            $hasta = $reqDesde;
        } else {
            if ($reqDesde > $reqHasta) {
                $desde = $reqHasta;
                $hasta = $reqDesde;
            } else {
                $desde = $reqDesde;
                $hasta = $reqHasta;
            }
        }
        $buscar = $request->input('buscar');
        $estadoAbono = $request->input('estado');
        $defaultTipo = match ((int) Auth::id()) {
            16 => '2',
            22 => '1',
            default => '1',
        };

        $tipo = (string) $request->input('tipo', $defaultTipo);

        if (Auth::id() == 22)
            $tipo = '1';
        if (Auth::id() == 16)
            $tipo = '2';

        $fechaInicio = \Carbon\Carbon::createFromFormat('Y-m', $desde)->startOfMonth()->format('Y-m-d');
        $fechaFin = \Carbon\Carbon::createFromFormat('Y-m', $hasta)->endOfMonth()->format('Y-m-d');

        $query = Factura::with(['empresa', 'cliente', 'abonos'])
            ->whereBetween('fecha_emision', [$fechaInicio, $fechaFin])
            ->whereIn('estado', ['Facturada', 'Pendiente']);

        if ($tipo !== 'todos') {
            $query->where('tipo_factura', $tipo);
        }

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('ruc_emisor', 'like', "%{$buscar}%")
                    ->orWhere('ruc_receptor', 'like', "%{$buscar}%")
                    ->orWhere('nro_factura', 'like', "%{$buscar}%")
                    ->orWhereHas('empresa', function ($qE) use ($buscar) {
                        $qE->where('razon_social', 'like', "%{$buscar}%");
                    })
                    ->orWhereHas('cliente', function ($qC) use ($buscar) {
                        $qC->where('razon_social', 'like', "%{$buscar}%");
                    })
                    ->orWhereHas('emisor', function ($qEm) use ($buscar) {
                        $qEm->where('razon_social', 'like', "%{$buscar}%");
                    });
            });
        }

        if ($estadoAbono) {
            if (strtolower($estadoAbono) === 'b.cliente') {
                $query->where('estado_abono', 'bancarizado_cliente');
            } else {
                $query->where('estado_abono', strtolower($estadoAbono));
            }
        }

        $facturas = $query->orderByDesc('fecha_emision')->get();
        $empresas = EmpresasClientes::select('ruc', 'razon_social')->get()->keyBy('ruc');

        // ── NUEVO: listas para el modal exportar ──
        $empresasList = EmpresasClientes::select('ruc', 'razon_social')
            ->orderBy('razon_social')->get()
            ->map(fn($e) => ['ruc' => $e->ruc, 'razon' => $e->razon_social]);

        $clientesList = ClienteAuditoria::select('ruc', 'razon_social')
            ->where('estado_actual', 1)
            ->orderBy('razon_social')->get()
            ->map(fn($c) => ['ruc' => $c->ruc, 'razon' => $c->razon_social]);

        $empresasConCuentas = EmpresasClientes::whereHas('cuentas', function ($q) {
            $q->where('estado', 1);
        })
            ->orderBy('razon_social')
            ->select('ruc', 'razon_social')
            ->get()
            ->map(fn($e) => ['ruc' => $e->ruc, 'razon' => $e->razon_social]);

        return view('abono_factura.index', compact(
            'facturas',
            'empresas',
            'desde',
            'hasta',
            'tipo',
            'empresasList',
            'clientesList',
            'empresasConCuentas',
            'buscar',
            'estadoAbono'
        ));
    }

    /* =========================================================
     |  STORE – Abono individual
     ========================================================= */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_factura' => 'required|exists:factura,id_factura',
            'monto' => 'required|numeric|min:0.01',
            'moneda' => 'required|in:PEN,USD',
            'precio_dolar' => 'nullable|numeric',
            'banco' => 'nullable|string|max:100',
            'fecha' => 'required|date',
            'nro_operacion' => 'nullable|string|max:100',
        ]);

        $factura = Factura::findOrFail($validated['id_factura']);
        if ($factura->estado_abono === 'bancarizado') {
            return response()->json(['message' => 'Esta factura ya está completamente abonada.'], 422);
        }

        $validated['id_usuario'] = Auth::id();
        AbonoFactura::create($validated);
        $factura->recalcularEstadoAbono();
        $this->sincronizarBancarizacionGuia($factura);
        $factura->load('abonos');
        $totalAbonado = $factura->calcularTotalAbonadoEnMonedaFactura();
        $montoPendiente = max(0, $factura->monto_para_abonos - $totalAbonado);

        $ultimoAbonoConDolar = $factura->abonos
            ->where('precio_dolar', '>', 0)
            ->sortByDesc('fecha')
            ->first();

        return response()->json([
            'success' => true,
            'total_abonado' => round($totalAbonado, 2),
            'monto_pendiente' => round($montoPendiente, 2),
            'estado_abono' => $factura->estado_abono,
            'tipo_moneda' => $factura->tipo_moneda,
            'precio_dolar' => $ultimoAbonoConDolar?->precio_dolar ?? 0,
        ]);
    }

    /* =========================================================
     |  STORE MASIVO – Abono para N facturas en un lote
     ========================================================= */
    public function storeMasivo(Request $request)
    {
        $request->validate([
            'facturas' => 'required|array|min:1',
            'facturas.*.id_factura' => 'required|exists:factura,id_factura',
            'facturas.*.monto' => 'required|numeric|min:0.01',
            'moneda' => 'required|in:PEN,USD',
            'facturas.*.precio_dolar' => 'required|numeric',
            'banco' => 'nullable|string|max:100',
            'fecha' => 'required|date',
            'nro_operacion' => 'nullable|string|max:100',
        ]);

        $grupoId = Str::uuid()->toString();
        $resultados = [];

        DB::transaction(function () use ($request, $grupoId, &$resultados) {
            foreach ($request->facturas as $item) {
                $factura = Factura::findOrFail($item['id_factura']);
                if ($factura->estado_abono === 'bancarizado')
                    continue;

                AbonoFactura::create([
                    'id_factura' => $factura->id_factura,
                    'id_usuario' => Auth::id(),
                    'monto' => $item['monto'],
                    'moneda' => $request->moneda,
                    'precio_dolar' => $item['precio_dolar'],
                    'banco' => $request->banco,
                    'fecha' => $request->fecha,
                    'nro_operacion' => $request->nro_operacion,
                    'id_grupo_abono' => $grupoId,
                ]);

                $factura->recalcularEstadoAbono();
                $this->sincronizarBancarizacionGuia($factura);
                $factura->load('abonos');
                $totalAbonado = $factura->calcularTotalAbonadoEnMonedaFactura();
                $montoPendiente = max(0, $factura->monto_para_abonos - $totalAbonado);

                $resultados[] = [
                    'id_factura' => $factura->id_factura,
                    'total_abonado' => round($totalAbonado, 2),
                    'monto_pendiente' => round($montoPendiente, 2),
                    'estado_abono' => $factura->estado_abono,
                    'tipo_moneda' => $factura->tipo_moneda,
                    'grupo_id' => $grupoId,
                ];
            }
        });

        return response()->json([
            'success' => true,
            'grupo_id' => $grupoId,
            'procesados' => count($resultados),
            'resultados' => $resultados,
        ]);
    }

    /* =========================================================
     |  UPDATE
     ========================================================= */
    public function update(Request $request, int $id)
    {
        $abono = AbonoFactura::findOrFail($id);
        $validated = $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'moneda' => 'required|in:PEN,USD',
            'precio_dolar' => 'nullable|numeric',
            'banco' => 'nullable|string|max:100',
            'fecha' => 'required|date',
            'nro_operacion' => 'nullable|string|max:100',
        ]);

        $abono->update($validated);
        $factura = Factura::findOrFail($abono->id_factura);
        $factura->recalcularEstadoAbono();
        $this->sincronizarBancarizacionGuia($factura);
        $factura->load('abonos');
        $totalAbonado = $factura->calcularTotalAbonadoEnMonedaFactura();
        $montoPendiente = max(0, $factura->monto_para_abonos - $totalAbonado);

        return response()->json([
            'success' => true,
            'total_abonado' => round($totalAbonado, 2),
            'monto_pendiente' => round($montoPendiente, 2),
            'estado_abono' => $factura->estado_abono,
            'tipo_moneda' => $factura->tipo_moneda,
        ]);
    }

    /* =========================================================
     |  DESTROY
     ========================================================= */
    public function destroy(int $id)
    {
        $abono = AbonoFactura::findOrFail($id);
        $idFactura = $abono->id_factura;
        $abono->delete();

        $factura = Factura::findOrFail($idFactura);
        $factura->recalcularEstadoAbono();
        $this->sincronizarBancarizacionGuia($factura);
        $factura->load('abonos');
        $totalAbonado = $factura->calcularTotalAbonadoEnMonedaFactura();
        $montoPendiente = max(0, $factura->monto_para_abonos - $totalAbonado);

        return response()->json([
            'success' => true,
            'total_abonado' => round($totalAbonado, 2),
            'monto_pendiente' => round($montoPendiente, 2),
            'estado_abono' => $factura->estado_abono,
            'tipo_moneda' => $factura->tipo_moneda,
        ]);
    }

    /* =========================================================
     |  GET ABONOS – Historial de una factura
     ========================================================= */
    public function getAbonos(int $idFactura)
    {
        $factura = Factura::with(['abonos.usuario'])->findOrFail($idFactura);

        $abonos = $factura->abonos->sortByDesc('fecha')->values()->map(fn($a) => [
            'id_abono_factura' => $a->id_abono_factura,
            'monto' => number_format($a->monto, 2),
            'monto_raw' => $a->monto,
            'moneda' => $a->moneda,
            'precio_dolar' => $a->precio_dolar,
            'banco' => $a->banco ?? '—',
            'fecha' => \Carbon\Carbon::parse($a->fecha)->format('d/m/Y'),
            'fecha_raw' => $a->fecha->format('Y-m-d'),
            'nro_operacion' => $a->nro_operacion ?? '—',
            'usuario_nombre' => $a->usuario->name ?? '—',
            'id_grupo_abono' => $a->id_grupo_abono,
        ]);

        return response()->json([
            'abonos' => $abonos,
            'total_abonado' => round($factura->total_abonado, 2),
            'monto_pendiente' => round($factura->monto_pendiente, 2),
            'monto_facturable' => round($factura->monto_para_abonos, 2),
            'tipo_moneda' => $factura->tipo_moneda,
            'estado_abono' => $factura->estado_abono,
        ]);
    }

    /* =========================================================
     |  GET GRUPO – Todas las facturas del mismo grupo de abono
     |  Recibe el id_factura, busca su último grupo y devuelve
     |  todas las facturas hermanas del mismo lote.
     ========================================================= */
    public function getGrupo(int $idFactura)
    {
        // Buscar el grupo más reciente asociado a esta factura
        $grupoAbono = AbonoFactura::where('id_factura', $idFactura)
            ->whereNotNull('id_grupo_abono')
            ->orderByDesc('created_at')
            ->value('id_grupo_abono');

        if (!$grupoAbono) {
            return response()->json(['grupo_id' => null, 'facturas' => []]);
        }

        // Todas las facturas del mismo grupo (distintas)
        $idsFacturas = AbonoFactura::where('id_grupo_abono', $grupoAbono)
            ->distinct()
            ->pluck('id_factura');

        $facturas = Factura::with(['empresa', 'cliente', 'abonos'])
            ->whereIn('id_factura', $idsFacturas)
            ->get()
            ->map(function ($f) use ($grupoAbono) {
                $razonReceptor = $f->empresa->razon_social ?? ($f->cliente->razon_social ?? '—');
                $simbolo = $f->tipo_moneda === 'USD' ? '$' : 'S/';

                $montoBase = $f->tipo_moneda === 'USD' && (float) $f->precio_dolar > 0
                    ? round($f->total_neto / $f->precio_dolar, 2)
                    : round((float) $f->total_neto, 2);

                $totalAbonado = round($f->abonos->sum('monto'), 2);
                $montoPendiente = max(0, $montoBase - $totalAbonado);

                // Monto abonado solo en este grupo
                $montoEnGrupo = round(
                    $f->abonos->where('id_grupo_abono', $grupoAbono)->sum('monto'),
                    2
                );

                return [
                    'id_factura' => $f->id_factura,
                    'nro_factura' => $f->nro_factura ?? 'Sin nro.',
                    'ruc_emisor' => $f->ruc_emisor,
                    'ruc_receptor' => $f->ruc_receptor,
                    'razon_receptor' => $razonReceptor,
                    'fecha_emision' => \Carbon\Carbon::parse($f->fecha_emision)->format('d/m/Y'),
                    'tipo_moneda' => $f->tipo_moneda,
                    'simbolo' => $simbolo,
                    'monto_base' => $montoBase,
                    'total_abonado' => $totalAbonado,
                    'monto_pendiente' => $montoPendiente,
                    'monto_en_grupo' => $montoEnGrupo,
                    'estado_abono' => $f->estado_abono,
                ];
            });

        return response()->json([
            'grupo_id' => $grupoAbono,
            'facturas' => $facturas->values(),
        ]);
    }

    public function exportar(Request $request)
    {
        $request->validate([
            'ruc' => 'required|string|max:20',
            'mes' => 'nullable|integer|min:1|max:12',
            'anio' => 'nullable|integer|min:2000|max:2100',
            'desde' => 'nullable|string|max:7',
            'hasta' => 'nullable|string|max:7',
            'tipo' => 'required|in:empresa,cliente',
            'estado' => 'required|in:todas,ambas,bancarizado,bancarizado_cliente,pendiente',
            'rol' => 'required|in:emisor,receptor',
        ]);

        $export = new \App\Exports\AbonoFacturasExport(
            $request->ruc,
            (int) $request->mes,
            (int) $request->anio,
            $request->tipo,
            $request->estado,
            $request->desde,
            $request->hasta,
            $request->input('rol', 'receptor')
        );

        return \Maatwebsite\Excel\Facades\Excel::download(
            $export,
            $export->getNombreArchivo(),
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    public function toggleBancarizadoCliente(int $idFactura)
    {
        $factura = Factura::findOrFail($idFactura);

        if ($factura->estado_abono === 'pendiente') {
            return response()->json(['message' => 'Solo se puede cambiar el estado si la factura está totalmente abonada.'], 422);
        }

        $nuevoEstado = ($factura->estado_abono === 'bancarizado') ? 'bancarizado_cliente' : 'bancarizado';

        DB::table('factura')
            ->where('id_factura', $idFactura)
            ->update(['estado_abono' => $nuevoEstado]);

        return response()->json([
            'success' => true,
            'estado_abono' => $nuevoEstado,
            'id_factura' => $idFactura
        ]);
    }

    public function porRuc(Request $request)
    {
        $ruc = $request->input('ruc');
        $empresa = EmpresasClientes::where('ruc', $ruc)->first();

        if (!$empresa) {
            return response()->json([]);
        }

        $cuentas = $empresa->cuentas()
            ->where('estado', 1)
            ->get()
            ->map(fn($c) => [
                'sede' => $c->sede ?? 'Trujillo',
                'banco' => $c->nombre . ' (' . ($c->tipo == 0 ? 'S/.' : '$') . ')',
                'cuenta' => $c->nro_cuenta ?? '—',
                'cci' => $c->nro_interbancario ?? '—',
            ]);

        return response()->json($cuentas);
    }
    private function sincronizarBancarizacionGuia(Factura $factura): void
    {
        $ruc = $factura->ruc_receptor;
        $mes = \Carbon\Carbon::parse($factura->fecha_emision)->month;
        $anio = \Carbon\Carbon::parse($factura->fecha_emision)->year;

        $totalFacturas = Factura::whereYear('fecha_emision', $anio)
            ->whereMonth('fecha_emision', $mes)
            ->where('ruc_receptor', $ruc)
            ->whereIn('estado', ['Facturada', 'Pendiente'])
            ->count();

        $totalBancarizadas = Factura::whereYear('fecha_emision', $anio)
            ->whereMonth('fecha_emision', $mes)
            ->where('ruc_receptor', $ruc)
            ->whereIn('estado', ['Facturada', 'Pendiente'])
            ->whereIn('estado_abono', ['bancarizado', 'bancarizado_cliente'])
            ->count();

        $nuevoBancarizacion = ($totalFacturas > 0 && $totalFacturas === $totalBancarizadas)
            ? 'si'
            : '-';

        Guia::where(function ($query) use ($ruc) {
            $query->whereHas('empresa', fn($q) => $q->where('ruc', $ruc))
                ->orWhereHas('cliente', fn($q) => $q->where('ruc', $ruc));
        })
            ->whereYear('recepcion', $anio)
            ->whereMonth('recepcion', $mes)
            ->update(['bancarizacion' => $nuevoBancarizacion]);
    }
}