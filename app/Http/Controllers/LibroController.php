<?php

namespace App\Http\Controllers;

use App\Models\Libro;
use Illuminate\Http\Request;

class LibroController extends Controller
{
    public function index()
    {
        $libros = Libro::all();
        return view('libros.index', compact('libros'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo_regimen' => 'required|string|max:255',
            'tramo' => 'required|string|max:255',
            'area' => 'required|string|max:255',
        ]);

        Libro::create($request->all());

        return redirect()->route('libros.index')->with('success', 'Libro creado exitosamente.');
    }

    public function update(Request $request, $id_libro)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo_regimen' => 'required|string|max:255',
            'tramo' => 'required|string|max:255',
            'area' => 'required|string|max:255',
        ]);

        $libro = Libro::findOrFail($id_libro);
        $libro->update($request->all());

        return redirect()->route('libros.index')->with('success', 'Libro actualizado exitosamente.');
    }

    public function destroy($id_libro)
    {
        $libro = Libro::findOrFail($id_libro);
        $libro->delete();

        return redirect()->route('libros.index')->with('success', 'Libro eliminado exitosamente.');
    }
}
