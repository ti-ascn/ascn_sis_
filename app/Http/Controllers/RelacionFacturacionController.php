<?php

namespace App\Http\Controllers;

use App\Models\EmpresasClientes;
use App\Models\RelacionFacturacion;
use Illuminate\Http\Request;

class RelacionFacturacionController extends Controller
{
    public function index()
    {
        $relaciones = RelacionFacturacion::with(['origen', 'destino'])->get();

        // Agrupar por origen
        $agrupado = [];
        foreach ($relaciones as $rel) {
            $o = $rel->id_empresa_origen;

            if (!isset($agrupado[$o])) {
                $agrupado[$o] = [
                    'origen' => $rel->origen,
                    'destinos' => []
                ];
            }

            $agrupado[$o]['destinos'][] = $rel->destino;
        }

        return view('relaciones_facturacion.index', compact('agrupado'));
    }

    public function create()
    {
        $empresas = EmpresasClientes::orderBy('razon_social')->get();
        return view('relaciones_facturacion.create', compact('empresas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_empresa_origen' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'id_empresas_destino' => 'required|array|min:1',
            'id_empresas_destino.*' => 'exists:empresas_clientes,id_empresas_clientes|different:id_empresa_origen',
        ]);

        $origen = $request->id_empresa_origen;

        foreach ($request->id_empresas_destino as $destino) {

            $existe = RelacionFacturacion::where('id_empresa_origen', $origen)
                ->where('id_empresa_destino', $destino)
                ->exists();

            if (!$existe) {
                RelacionFacturacion::create([
                    'id_empresa_origen' => $origen,
                    'id_empresa_destino' => $destino
                ]);
            }
        }

        return redirect()->route('relaciones_facturacion.index')
            ->with('success', 'Relaciones creadas correctamente.');
    }


    public function edit($id_origen)
    {
        $empresas = EmpresasClientes::orderBy('razon_social')->get();

        $destinosActuales = RelacionFacturacion::where('id_empresa_origen', $id_origen)
            ->pluck('id_empresa_destino')
            ->toArray();

        $origen = EmpresasClientes::findOrFail($id_origen);

        return view('relaciones_facturacion.edit', compact(
            'empresas',
            'origen',
            'destinosActuales'
        ));
    }


    public function update(Request $request, $id_origen)
    {
        // 1. Si NO hay destinos → eliminar todas las relaciones
        if (!$request->has('id_empresas_destino') || empty($request->id_empresas_destino)) {

            RelacionFacturacion::where('id_empresa_origen', $id_origen)->delete();

            return redirect()->route('relaciones_facturacion.index')
                ->with('success', 'Todas las relaciones fueron eliminadas correctamente.');
        }

        // 2. Validar SOLO si vienen destinos
        $validated = $request->validate([
            'id_empresas_destino' => 'array',
            'id_empresas_destino.*' => 'exists:empresas_clientes,id_empresas_clientes|different:id_origen',
        ]);

        // 3. Eliminar las relaciones actuales
        RelacionFacturacion::where('id_empresa_origen', $id_origen)->delete();

        // 4. Crear las nuevas
        foreach ($request->id_empresas_destino as $dest) {
            RelacionFacturacion::create([
                'id_empresa_origen' => $id_origen,
                'id_empresa_destino' => $dest,
            ]);
        }

        return redirect()->route('relaciones_facturacion.index')
            ->with('success', 'Relaciones actualizadas correctamente.');
    }

    public function destroyGrupo($id_origen)
    {
        $origen = EmpresasClientes::findOrFail($id_origen);

        // Eliminar TODAS las relaciones cuyo origen coincida
        RelacionFacturacion::where('id_empresa_origen', $id_origen)->delete();

        return back()->with('success', 'Se eliminaron todas las relaciones de: ' . $origen->razon_social);
    }

}
