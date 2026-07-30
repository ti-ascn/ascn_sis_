<?php

namespace App\Http\Controllers;

use App\Models\UnidadMedida;
use Illuminate\Http\Request;

namespace App\Http\Controllers;

use App\Models\UnidadMedida;
use Illuminate\Http\Request;

class UnidadMedidaController extends Controller
{
    public function index()
    {
        $unidades = UnidadMedida::orderBy('nombre')->get();
        return view('unidades.index', compact('unidades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:unidades_medida,nombre',
        ]);

        UnidadMedida::create([
            'nombre' => strtoupper($request->nombre),
            'estado' => 1
        ]);

        return back()->with('success', 'Unidad creada correctamente');
    }

    public function update(Request $request, $id_unidad_medida)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:unidades_medida,nombre,' . $id_unidad_medida . ',id_unidad_medida',
        ]);

        $unidad = UnidadMedida::findOrFail($id_unidad_medida);
        $unidad->nombre = strtoupper($request->nombre);
        $unidad->save();

        return back()->with('success', 'Unidad actualizada correctamente');
    }

    public function toggle($id)
    {
        $unidad = UnidadMedida::findOrFail($id);
        $unidad->estado = !$unidad->estado;
        $unidad->save();

        return back()->with('success', 'Estado actualizado');
    }
}
