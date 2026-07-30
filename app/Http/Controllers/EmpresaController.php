<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Transaccion;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function showTransacciones($id_empresa)
    {
        // Obtener la empresa
        $empresa = Empresa::findOrFail($id_empresa);

        // Obtener todas las transacciones de las cuentas de la empresa
        $transacciones = Transaccion::whereIn('id_cuenta', $empresa->cuentas->pluck('id_cuenta'))
            ->whereNull('deleted_at')
            ->get();

        // Retornar vista con las transacciones
        return view('empresas.transacciones', compact('empresa', 'transacciones'));
    }

    public function index()
    {
        $empresas = Empresa::all();
        return view('empresas.index', compact('empresas'));
    }

    // Mostrar formulario para editar una cuenta
    public function showCuentas(Empresa $empresa)
    {
        $cuentas = $empresa->cuentas; // Asegúrate de que la relación 'cuentas' esté definida en el modelo Empresa
        return view('cuentas.lista', compact('cuentas', 'empresa'));
    }

    public function create()
    {
        return view('empresas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ruc' => 'required|string|size:11|unique:empresas',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:15',
        ]);

        Empresa::create($request->all());

        return redirect()->route('empresas.index')->with('success', 'Empresa creada con éxito.');
    }

    public function show(Empresa $empresa)
    {
        return view('empresas.show', compact('empresa'));
    }

    public function edit(Empresa $empresa)
    {
        return view('empresas.edit', compact('empresa'));
    }

    public function update(Request $request, Empresa $empresa)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ruc' => 'required|string|size:11|unique:empresas,ruc,' . $empresa->id_empresa . ',id_empresa',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:15',
        ]);

        $empresa->update($request->all());

        return redirect()->route('empresas.index')->with('success', 'Empresa actualizada con éxito.');
    }

    public function destroy(Empresa $empresa)
    {
        $empresa->delete();

        return redirect()->route('empresas.index')->with('success', 'Empresa eliminada con éxito.');
    }

    //APIS
    // public function getEmpresas()
    // {
    //     $empresas = Empresa::all();
    //     return response()->json($empresas);
    // }

}