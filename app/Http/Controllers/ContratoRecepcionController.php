<?php

namespace App\Http\Controllers;

use App\Models\ContratoRecepcion;
use App\Models\CotizacionRecepcion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContratoRecepcionController extends Controller
{
    public function index(Request $request)
    {
        $query = CotizacionRecepcion::with(['empresaCliente', 'contratoPrincipal'])
            ->where('tipo_cotizacion', 'contable')

            // SOLO CLIENTES ACTIVOS
            ->whereHas('empresaCliente', function ($q) {
                $q->where('estado_actual', 1);
            })

            ->whereIn('id_cotizacion', function ($sub) {
                $sub->selectRaw('MAX(id_cotizacion)')
                    ->from('cotizacion_recepcion')
                    ->where('tipo_cotizacion', 'contable')
                    ->groupBy('id_empresa_cliente');
            });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nro_cotizacion', 'like', "%{$search}%")
                    ->orWhereHas('empresaCliente', function ($q2) use ($search) {
                        $q2->where('razon_social', 'like', "%{$search}%")
                            ->orWhere('ruc', 'like', "%{$search}%");
                    });
            });
        }

        $cotizaciones = $query->orderBy('id_cotizacion', 'desc')->get();

        return view('contratos_recepcion.index', compact('cotizaciones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_cotizacion' => 'required|exists:cotizacion_recepcion,id_cotizacion',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'archivo_cotizacion' => 'nullable|file|extensions:pdf|max:10240',
            'archivo_contrato' => 'nullable|file|extensions:pdf|max:10240',
        ]);

        // ya esta jefe（；´д｀）ゞ
        ContratoRecepcion::where('id_cotizacion', $request->id_cotizacion)
            ->update(['es_principal' => false]);

        $data = [
            'id_cotizacion' => $request->id_cotizacion,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'es_principal' => true,
        ];

        if ($request->hasFile('archivo_cotizacion')) {
            $data['archivo_cotizacion'] = $this->guardarArchivo(
                $request->file('archivo_cotizacion'),
                $request->id_cotizacion,
                'cotizacion'
            );
        }

        if ($request->hasFile('archivo_contrato')) {
            $data['archivo_contrato'] = $this->guardarArchivo(
                $request->file('archivo_contrato'),
                $request->id_cotizacion,
                'contrato'
            );
        }

        $contrato = ContratoRecepcion::create($data);

        return response()->json([
            'message' => 'Contrato creado correctamente.',
            'contrato' => $contrato,
        ]);
    }

    public function update(Request $request, $id)
    {
        $contrato = ContratoRecepcion::findOrFail($id);

        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'archivo_cotizacion' => 'nullable|file|extensions:pdf|max:10240',
            'archivo_contrato' => 'nullable|file|extensions:pdf|max:10240',
        ]);

        $data = [
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
        ];

        if ($request->hasFile('archivo_cotizacion')) {
            if ($contrato->archivo_cotizacion && file_exists(public_path($contrato->archivo_cotizacion))) {
                unlink(public_path($contrato->archivo_cotizacion));
            }
            $data['archivo_cotizacion'] = $this->guardarArchivo(
                $request->file('archivo_cotizacion'),
                $contrato->id_cotizacion,
                'cotizacion'
            );
        }

        if ($request->hasFile('archivo_contrato')) {
            if ($contrato->archivo_contrato && file_exists(public_path($contrato->archivo_contrato))) {
                unlink(public_path($contrato->archivo_contrato));
            }
            $data['archivo_contrato'] = $this->guardarArchivo(
                $request->file('archivo_contrato'),
                $contrato->id_cotizacion,
                'contrato'
            );
        }

        $contrato->update($data);
        $contrato->refresh();

        return response()->json([
            'message' => 'Contrato actualizado correctamente.',
            'contrato' => $contrato,
        ]);
    }

    public function destroy($id)
    {
        $contrato = ContratoRecepcion::findOrFail($id);
        $cotizacionId = $contrato->id_cotizacion;
        $eraPrincipal = $contrato->es_principal;

        if ($contrato->archivo_cotizacion && file_exists(public_path($contrato->archivo_cotizacion))) {
            unlink(public_path($contrato->archivo_cotizacion));
        }
        if ($contrato->archivo_contrato && file_exists(public_path($contrato->archivo_contrato))) {
            unlink(public_path($contrato->archivo_contrato));
        }

        $contrato->delete();

        $principal = null;

        if ($eraPrincipal) {
            $nuevo = ContratoRecepcion::where('id_cotizacion', $cotizacionId)
                ->latest('id_contrato_recepcion')->first();
            if ($nuevo) {
                $nuevo->update(['es_principal' => true]);
                $nuevo->refresh();
                $principal = $nuevo;
            }
        } else {
            $principal = ContratoRecepcion::where('id_cotizacion', $cotizacionId)
                ->where('es_principal', true)->first();
        }

        return response()->json([
            'message' => 'Contrato eliminado correctamente.',
            'principal' => $principal,
        ]);
    }

    public function historial($id_cotizacion)
    {
        $contratos = ContratoRecepcion::where('id_cotizacion', $id_cotizacion)
            ->orderBy('es_principal', 'desc')
            ->orderBy('id_contrato_recepcion', 'desc')
            ->get();

        return response()->json(['contratos' => $contratos]);
    }

    public function marcarPrincipal($id)
    {
        $contrato = ContratoRecepcion::findOrFail($id);

        ContratoRecepcion::where('id_cotizacion', $contrato->id_cotizacion)
            ->update(['es_principal' => false]);

        $contrato->update(['es_principal' => true]);
        $contrato->refresh();

        return response()->json([
            'message' => 'Contrato marcado como principal.',
            'principal' => $contrato,
        ]);
    }

    // ── TODOS LOS CONTRATOS DE TODAS LAS EMPRESAS ─────────────────
    public function todos()
    {
        $contratos = ContratoRecepcion::with(['cotizacion.empresaCliente'])
            ->orderByRaw('(SELECT id_empresa_cliente FROM cotizacion_recepcion WHERE cotizacion_recepcion.id_cotizacion = contratos_recepcion.id_cotizacion) ASC')
            ->orderBy('id_cotizacion', 'asc')
            ->orderBy('id_contrato_recepcion', 'desc')
            ->get()
            ->map(function ($c) {
                $hoy = \Carbon\Carbon::today();
                $fechaFin = \Carbon\Carbon::parse($c->fecha_fin);
                $diff = $hoy->diffInDays($fechaFin, false);

                if ($diff > 6)
                    $vencimiento = ['texto' => $diff . ' días restantes', 'clase' => 'text-green-600 font-semibold'];
                elseif ($diff > 0)
                    $vencimiento = ['texto' => $diff . ' días restantes', 'clase' => 'text-orange-500 font-semibold'];
                elseif ($diff == 0)
                    $vencimiento = ['texto' => 'Vence hoy', 'clase' => 'text-red-600 font-bold'];
                else
                    $vencimiento = ['texto' => 'Venció hace ' . abs($diff) . ' días', 'clase' => 'text-red-500 font-semibold'];

                return [
                    'empresa' => $c->cotizacion->empresaCliente->razon_social ?? '—',
                    'nro_cotizacion' => $c->cotizacion->nro_cotizacion ?? '—',
                    'fecha_inicio' => $c->fecha_inicio,
                    'fecha_fin' => $c->fecha_fin,
                    'vencimiento' => $vencimiento,
                    'archivo_cotizacion' => $c->archivo_cotizacion,
                    'archivo_contrato' => $c->archivo_contrato,
                    'es_principal' => $c->es_principal,
                ];
            });

        return response()->json(['contratos' => $contratos]);
    }

    // ── HELPER PRIVADO: guardar PDF en public/ ────────────────────
    private function guardarArchivo($file, $id_cotizacion, string $tipo): string
    {
        $cotizacion = CotizacionRecepcion::with('empresaCliente')->find($id_cotizacion);

        // Separar cada palabra de la razón social con "_"
        // Str::ascii convierte caracteres especiales (á → a, ñ → n, etc.)
        // luego dividimos por cualquier espacio/separador y filtramos vacíos.
        $palabras = array_filter(
            preg_split('/[\s\-_\/\\\\]+/', Str::ascii($cotizacion->empresaCliente->razon_social)),
            fn($p) => $p !== ''
        );
        $razon = implode('_', array_map(
            fn($p) => strtoupper(preg_replace('/[^a-z0-9]/i', '', $p)),
            $palabras
        ));

        $nro = strtoupper($cotizacion->nro_cotizacion);
        $timestamp = now()->format('Ymd_His');

        if ($tipo === 'cotizacion') {
            $subcarpeta = 'archivos_contratos/cotizaciones';
            $baseNombre = "COTIZACION_{$nro}_{$razon}_{$timestamp}";
        } else {
            $subcarpeta = 'archivos_contratos/contratos';
            $baseNombre = "CONTRATO_LOCACION_{$razon}_{$timestamp}";
        }

        $carpeta = public_path($subcarpeta);
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0755, true);
        }

        $nombreFinal = $baseNombre . '.pdf';
        $contador = 1;
        while (file_exists("{$carpeta}/{$nombreFinal}")) {
            $nombreFinal = "{$baseNombre}_{$contador}.pdf";
            $contador++;
        }

        $file->move($carpeta, $nombreFinal);

        return "{$subcarpeta}/{$nombreFinal}";
    }
}