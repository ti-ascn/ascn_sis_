<?php

namespace App\Http\Controllers;

use App\Models\Chofer;
use App\Models\EmpresasClientes;
use Illuminate\Http\Request;

class ChoferController extends Controller
{
    public function index()
    {
        $choferes = Chofer::with('empresa')->get();
        return view('choferes.index', compact('choferes'));
    }

    public function create($empresaId = null)
    {
        $empresas = EmpresasClientes::orderBy('razon_social')->get();
        return view('choferes.create', compact('empresas', 'empresaId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_empresas_clientes' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'nombre' => 'required|string|max:255',
            'dni' => 'required|string|max:20',
            'placa' => 'nullable|string|max:255',
            'marca' => 'nullable|string|max:255',
            'licencia' => 'nullable|string|max:255',
            'certificado' => 'nullable|string|max:255',
            'config_vehicular' => 'nullable|string|max:255',
            'cng' => 'nullable|string|max:255',
            'peso' => 'nullable|numeric',
            'color' => 'nullable|string|max:7',
            'estado' => 'required|boolean',
        ]);

        $validated['nombre'] = strtoupper($validated['nombre']);
        $validated['fecha_alta'] = now();
        $validated['fecha_baja'] = null;

        Chofer::create($validated);

        return redirect()->route('choferes.index')->with('success', 'Chofer creado con éxito.');
    }


    public function edit($id_chofer)
    {
        $chofer = Chofer::findOrFail($id_chofer);
        $empresas = EmpresasClientes::all();
        return view('choferes.edit', compact('chofer', 'empresas'));
    }

    public function update(Request $request, $id_chofer)
    {
        $validated = $request->validate([
            'id_empresas_clientes' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'nombre' => 'required|string|max:255',
            'dni' => 'required|string|max:20',
            'placa' => 'nullable|string|max:255',
            'marca' => 'nullable|string|max:255',
            'licencia' => 'nullable|string|max:255',
            'certificado' => 'nullable|string|max:255',
            'config_vehicular' => 'nullable|string|max:255',
            'cng' => 'nullable|string|max:255',
            'peso' => 'nullable|numeric',
            'color' => 'nullable|string|max:7',
            'estado' => 'required|boolean',
        ]);

        $validated['nombre'] = strtoupper($validated['nombre']);

        $chofer = Chofer::findOrFail($id_chofer);
        $chofer->update($validated);

        return redirect()->route('choferes.index')->with('success', 'Chofer actualizado con éxito.');
    }


    public function destroy($id_chofer)
    {
        $chofer = Chofer::findOrFail($id_chofer);
        $chofer->delete();

        return redirect()->route('choferes.index')->with('success', 'Chofer eliminado con éxito.');
    }

    public function desactivar($id_chofer)
    {
        $chofer = Chofer::findOrFail($id_chofer);
        $chofer->estado = 0; // Inactivo
        $chofer->fecha_baja = now(); // Fecha actual
        $chofer->save();

        return redirect()->route('choferes.index')->with('success', 'Chofer desactivado con éxito.');
    }

    public function reactivar($id_chofer)
    {
        $chofer = Chofer::findOrFail($id_chofer);
        $chofer->estado = 1; // Activo
        $chofer->fecha_baja = null; // Limpiar la fecha de baja
        $chofer->fecha_alta = now(); // Actualizar la fecha de alta a la fecha actual
        $chofer->save();

        return redirect()->route('choferes.index')->with('success', 'Chofer reactivado con éxito.');
    }

    public function porEmpresa($id)
    {
        $empresa = EmpresasClientes::findOrFail($id);
        $choferes = Chofer::where('id_empresas_clientes', $id)->get();

        return view('choferes.lista', compact('empresa', 'choferes'));
    }

}

