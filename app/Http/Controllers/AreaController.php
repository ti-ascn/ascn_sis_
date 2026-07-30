<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\User;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index()
    {
        $areas = Area::with('user')->get();
        return view('areas.index', compact('areas'));
    }

    public function create()
    {
        $users = User::whereNotIn('id', Area::pluck('id_user'))->get();

        return view('areas.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'id_user' => 'required|exists:users,id',
            'estado' => 'required|boolean',
        ], [
            'id_user.required' => 'Cree un nuevo usuario para asignar un área.', // Mensaje personalizado
        ]);

        // Convertir el campo 'nombre' a mayúsculas
        $validated['nombre'] = strtoupper($validated['nombre']);

        Area::create($validated);
        return redirect()->route('areas.index')->with('success', 'Área creada con éxito.');
    }

    public function edit($id_area)
    {
        $area = Area::findOrFail($id_area);
        $users = User::whereNotIn('id', Area::pluck('id_user'))->get();
        return view('areas.edit', compact('area', 'users'));
    }

    public function update(Request $request, $id_area)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'id_user' => 'required|exists:users,id',
            'estado' => 'required|boolean',
        ]);

        // Convertir el campo 'nombre' a mayúsculas
        $validated['nombre'] = strtoupper($validated['nombre']);

        $area = Area::findOrFail($id_area);
        $area->update($validated);
        return redirect()->route('areas.index')->with('success', 'Área actualizada con éxito.');
    }

    public function destroy($id_area)
    {
        $area = Area::findOrFail($id_area);
        $area->delete();
        return redirect()->route('areas.index')->with('success', 'Área eliminada con éxito.');
    }
}

