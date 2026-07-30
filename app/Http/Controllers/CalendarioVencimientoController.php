<?php

namespace App\Http\Controllers;

use App\Models\CalendarioVencimiento;
use Illuminate\Http\Request;

class CalendarioVencimientoController extends Controller
{
    public function index()
    {
        $calendarios = CalendarioVencimiento::all();
        return view('calendario.index', compact('calendarios'));
    }

    public function create()
    {
        return view('calendario.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo' => 'required|string|max:20',
            'mes' => 'required|string|max:7', // formato YYYY-MM
            'fechas' => 'required|array|min:10|max:10',
            'estado' => 'required|boolean',
        ]);

        // Verificar si ya existe un calendario para ese tipo y mes
        $existe = CalendarioVencimiento::where('tipo', $request->tipo)
            ->where('mes', $request->mes)
            ->exists();

        if ($existe) {
            return back()->with('error', 'Ya existe un calendario para este tipo y mes.');
        }

        $validated['fechas'] = json_encode($request->fechas);

        CalendarioVencimiento::create($validated);

        return redirect()->route('calendario.index')->with('success', 'Calendario creado correctamente.');
    }


    public function edit($id_calendario)
    {
        $calendario = CalendarioVencimiento::findOrFail($id_calendario);
        return view('calendario.edit', compact('calendario'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'tipo' => 'required|string|max:20',
            'mes' => 'required|string|max:7',
            'fechas' => 'required|array|min:10|max:10',
            'estado' => 'required|boolean',
        ]);

        $existe = CalendarioVencimiento::where('tipo', $request->tipo)
            ->where('mes', $request->mes)
            ->where('id_calendario', '!=', $id) // excluir el actual
            ->exists();

        if ($existe) {
            return back()->with('error', 'Ya existe un calendario para este tipo y mes.');
        }

        $validated['fechas'] = json_encode($request->fechas);

        $calendario = CalendarioVencimiento::findOrFail($id);
        $calendario->update($validated);

        return redirect()->route('calendario.index')->with('success', 'Calendario actualizado correctamente.');
    }


    public function destroy($id)
    {
        $calendario = CalendarioVencimiento::findOrFail($id);
        $calendario->delete();

        return redirect()->route('calendario.index')->with('success', 'Calendario eliminado correctamente.');
    }
}