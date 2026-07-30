<?php

namespace App\Http\Controllers;

use App\Models\Feriado;
use Illuminate\Http\Request;

class FeriadoController extends Controller
{
    public function index()
    {
        $feriados = Feriado::orderBy('fecha_feriado', 'asc')->get();
        return view('feriados.index', compact('feriados'));
    }

    public function create()
    {
        return view('feriados.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha_feriado' => 'required|date|unique:feriado,fecha_feriado',
            'nombre_feriado' => 'required|string|max:255',
        ]);

        Feriado::create($validated);
        return redirect()->route('feriados.index')->with('success', 'Feriado creado con éxito.');
    }

    public function edit($id_feriado)
    {
        $feriado = Feriado::findOrFail($id_feriado);
        return view('feriados.edit', compact('feriado'));
    }

    public function update(Request $request, $id_feriado)
    {
        $validated = $request->validate([
            'fecha_feriado' => "required|date|unique:feriado,fecha_feriado,$id_feriado,id_feriado",
            'nombre_feriado' => 'required|string|max:255',
        ]);

        $feriado = Feriado::findOrFail($id_feriado);
        $feriado->update($validated);
        return redirect()->route('feriados.index')->with('success', 'Feriado actualizado con éxito.');
    }

    public function destroy($id_feriado)
    {
        $feriado = Feriado::findOrFail($id_feriado);
        $feriado->delete();
        return redirect()->route('feriados.index')->with('success', 'Feriado eliminado con éxito.');
    }
}
