<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\VentasEmpresaCliente;
use App\Models\EmpresasClientes;
use Carbon\Carbon;

class VentasEmpresaClienteController extends Controller
{
    public function create($id_empresas_clientes)
    {
        $empresaCliente = EmpresasClientes::findOrFail($id_empresas_clientes);
        return view('ventas_empresas_clientes.create', compact('empresaCliente'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_empresas_clientes_ventas' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'registro' => 'required|in:si,no,na',
            'observacion' => 'nullable|string',
        ]);

        // Registrar la fecha automáticamente
        $fechaActual = Carbon::now()->startOfDay(); // Fecha actual sin hora

        $existe = VentasEmpresaCliente::where('id_empresas_clientes_ventas', $request->id_empresas_clientes_ventas)
            ->whereMonth('fecha', $fechaActual->month)
            ->whereYear('fecha', $fechaActual->year)
            ->exists();

        if ($existe) {
            return redirect()->back()->with('error','Ya existe un registro de VENTAS para este cliente en este mes.');
        }

        // Guardar el registro
        VentasEmpresaCliente::create([
            'id_empresas_clientes_ventas' => $request->id_empresas_clientes_ventas,
            'registro' => $request->registro,
            'fecha' => $fechaActual, // Fecha asignada automáticamente
            'id_usuario' => auth()->id(), // Usuario autenticado
            'observacion' => $request->observacion,
        ]);

        return redirect()->route('empresas_clientes.index')->with('success', 'Registro de ventas registrado exitosamente.');
    }

    // Método para mostrar el formulario de edición
    public function edit($id_empresas_clientes_ventas)
    {
        $ventasCliente = VentasEmpresaCliente::findOrFail($id_empresas_clientes_ventas);
        if (
            Auth::user()->tipo_usuario != 1 &&
            ($ventasCliente->id_usuario != Auth::id() || !Auth::user()->puede_editar)
        ) {
            abort(403, 'No tienes permiso para editar este registro.🫵😂');
        }

        return view('empresas_clientes_ventas.edit', compact('ventasCliente'));
    }

    // Método para actualizar un registro de ventas
    public function update(Request $request, $id_empresas_clientes_ventas)
    {
        // Buscar el registro de Ventas
        $ventasCliente = VentasEmpresaCliente::findOrFail($id_empresas_clientes_ventas);

        // Verificar si el usuario tiene permisos para editar
        if (
            Auth::user()->tipo_usuario != 1 &&
            (Auth::user()->id != $ventasCliente->id_usuario || !Auth::user()->puede_editar)
        ) {
            abort(403, 'No tienes permiso para editar este registro.🫵😂');
        }

        // Validar los datos del formulario
        $validated = $request->validate([
            'registro' => 'required|in:si,no,na', // Validar el campo registro
            'observacion' => 'nullable|string',
        ]);

        // Asignar el ID del usuario autenticado manualmente
        $validated['id_usuario'] = auth()->id();

        // Actualizar el registro de Ventas
        $ventasCliente->update($validated);

        // Obtener el mes de la fecha del registro editado
        $mes = date('F', strtotime($ventasCliente->fecha));

        // Redirigir a la ruta 'empresas_clientes.filtrar_por_mes' con el mes como parámetro
        return redirect()->route('empresas_clientes.filtrar_por_mes', ['mes' => $mes])
            ->with('success', 'Registro de ventas actualizadas correctamente.');
    }
}