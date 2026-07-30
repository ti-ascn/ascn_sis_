<?php

namespace App\Http\Controllers;

use App\Models\TipoAfp;
use Illuminate\Http\Request;

class TipoAfpController extends Controller
{
    public function index()
    {
        $tipoAfp = TipoAfp::all();
        return view('tipo_afp.index', compact('tipoAfp'));
    }

    public function create()
    {
        return view('tipo_afp.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:tipo_afp,nombre',
            'estado' => 'required|boolean',
        ]);

        TipoAfp::create($validated);
        return redirect()->route('tipo-afp.index')->with('success', 'Tipo AFP creado con éxito.');
    }

    public function edit($id_afp)
    {
        $tipoAfp = TipoAfp::findOrFail($id_afp);
        return view('tipo_afp.edit', compact('tipoAfp'));
    }

    public function update(Request $request, $id_afp)
    {
        $validated = $request->validate([
            'nombre' => "required|string|max:255|unique:tipo_afp,nombre,$id_afp,id_afp",
            'estado' => 'required|boolean',
        ]);

        $tipoAfp = TipoAfp::findOrFail($id_afp);
        $tipoAfp->update($validated);
        return redirect()->route('tipo-afp.index')->with('success', 'Tipo AFP actualizado con éxito.');
    }

    public function destroy($id_afp)
    {
        $tipoAfp = TipoAfp::findOrFail($id_afp);
        $tipoAfp->delete();
        return redirect()->route('tipo-afp.index')->with('success', 'Tipo AFP eliminado con éxito.');
    }
}
