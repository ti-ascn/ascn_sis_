<?php

namespace App\Http\Controllers;

use App\Models\Cuenta;
use App\Models\Empresa;
use Illuminate\Http\Request;

class CuentaController extends Controller
{
    // Mostrar lista de cuentas
    public function index()
    {
        $cuentas = Cuenta::all();
        return view('cuentas.index', compact('cuentas'));
    }

    public function showCuentas(Empresa $empresa)
    {
        // Obtener todas las cuentas asociadas a esta empresa
        $cuentas = $empresa->cuentas;

        // Pasar las cuentas a la vista
        return view('empresas.cuentas', compact('empresa', 'cuentas'));
    }

    // Mostrar formulario para crear una nueva cuenta
    public function create(Request $request)
    {
        // Obtener el ID de la empresa desde el parámetro de la URL
        $empresaId = $request->query('empresa');

        // Buscar la empresa por ID
        $empresa = Empresa::find($empresaId);

        if (!$empresa) {
            // Si no se encuentra la empresa, redirigir con un mensaje de error
            return redirect()->route('empresas.index')->with('error', 'Empresa no encontrada.');
        }

        // Pasar la empresa a la vista
        return view('cuentas.create', compact('empresa'));
    }


    // Guardar nueva cuenta
    public function store(Request $request)
    {
        // Validar campos
        $validated = $request->validate([
            'id_empresa' => 'required|exists:empresas,id_empresa', // Validar que la empresa existe
            'nombre' => 'required|string|max:100',
            'numero_cuenta' => 'required|string|max:30|unique:cuentas',
            'numero_interbancario' => 'required|string|max:30|unique:cuentas',
            'tipo' => 'required|string|max:10',
            'saldo_actual' => 'nullable|numeric|min:0',
        ]);

        // Crear la cuenta
        $cuenta = Cuenta::create($validated);

        // Obtener el ID de la empresa de la cuenta recién creada
        $empresaId = $cuenta->id_empresa;

        // Redirigir al URL correspondiente de la empresa con su id
        return redirect()->route('empresas.showCuentas', ['empresa' => $empresaId])
            ->with('success', 'Cuenta creada con éxito.');
    }


    public function edit(Cuenta $cuenta)
    {
        // Retornar la vista con los datos de la cuenta
        return view('cuentas.edit', compact('cuenta'));
    }


    // Actualizar cuenta
    public function update(Request $request, Cuenta $cuenta)
    {
        // Validación
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'numero_cuenta' => 'required|string|max:30|unique:cuentas,numero_cuenta,' . $cuenta->id_cuenta . ',id_cuenta', // Excluye el número de cuenta de la cuenta actual
            'numero_interbancario' => 'required|string|max:30|unique:cuentas,numero_interbancario,' . $cuenta->id_cuenta . ',id_cuenta', // Excluye el número interbancario de la cuenta actual
            'tipo' => 'required|string|max:10',
            'saldo_actual' => 'nullable|numeric',
            'id_empresa' => 'required|exists:empresas,id_empresa',
        ]);

        // Obtener el ID de la empresa desde el modelo 'Cuenta'
        $empresaId = $cuenta->id_empresa;

        // Actualizar la cuenta con los datos validados
        $cuenta->update([
            'nombre' => $validated['nombre'],
            'numero_cuenta' => $validated['numero_cuenta'],
            'numero_interbancario' => $validated['numero_interbancario'],
            'tipo' => $validated['tipo'],
            'saldo_actual' => $validated['saldo_actual'],
            'id_empresa' => $validated['id_empresa'],
        ]);

        // Redirigir al URL correcto, pasando el id_empresa en la ruta
        return redirect()->route('empresas.showCuentas', ['empresa' => $empresaId])
            ->with('success', 'La cuenta ha sido actualizada exitosamente.');
    }



    // Eliminar cuenta
    public function destroy(Cuenta $cuenta)
    {
        $empresaId = $cuenta->id_empresa;  // Obtener el id_empresa de la cuenta eliminada

        // Eliminar la cuenta
        $cuenta->delete();

        // Redirigir a la lista de cuentas de la empresa correspondiente
        return redirect()->route('empresas.showCuentas', $empresaId)
            ->with('success', 'Cuenta eliminada con éxito.');
    }
}

