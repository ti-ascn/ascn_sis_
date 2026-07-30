<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\ComprasEmpresaCliente;
use App\Models\EmpresasClientes;
use Carbon\Carbon;

class ComprasEmpresaClienteController extends Controller
{
    public function create($id_empresas_clientes)
    {
        $empresaCliente = EmpresasClientes::findOrFail($id_empresas_clientes);
        return view('compras_empresas_clientes.create', compact('empresaCliente'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_empresas_clientes_compras' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'registro' => 'required|in:si,no,na',
            'observacion' => 'nullable|string',
        ]);

        // Registrar la fecha automáticamente
        $fechaActual = Carbon::now()->startOfDay(); // Fecha actual sin hora

        $existe = ComprasEmpresaCliente::where('id_empresas_clientes_compras', $request->id_empresas_clientes_compras)
            ->whereMonth('fecha', $fechaActual->month)
            ->whereYear('fecha', $fechaActual->year)
            ->exists();

        if ($existe) {
            return redirect()->back()->with('error','Ya existe un registro de COMPRAS para este cliente en este mes.');
        }

        // Guardar el registro
        ComprasEmpresaCliente::create([
            'id_empresas_clientes_compras' => $request->id_empresas_clientes_compras,
            'registro' => $request->registro,
            'fecha' => $fechaActual, // Fecha asignada automáticamente
            'id_usuario' => auth()->id(), // Usuario autenticado
            'observacion' => $request->observacion,
        ]);

        return redirect()->route('empresas_clientes.index')->with('success', 'Registro de compras registrado exitosamente.');
    }

    public function edit($id_empresas_clientes_compras)
    {
        $comprasCliente = ComprasEmpresaCliente::findOrFail($id_empresas_clientes_compras);

        // Verificar si el usuario tiene permisos
        if (
            Auth::user()->tipo_usuario != 1 &&
            ($comprasCliente->id_usuario != Auth::id() || !Auth::user()->puede_editar)
        ) {
            abort(403, 'No tienes permiso para editar este registro.🫵😂');
        }

        return view('empresas_clientes_compras.edit', compact('comprasCliente'));
    }

    public function update(Request $request, $id_empresas_clientes_compras)
    {
        // Buscar el registro de Compras
        $comprasCliente = ComprasEmpresaCliente::findOrFail($id_empresas_clientes_compras);

        // Verificar si el usuario tiene permisos para editar
        if (
            Auth::user()->tipo_usuario != 1 &&
            (Auth::user()->id != $comprasCliente->id_usuario || !Auth::user()->puede_editar)
        ) {
            abort(403, 'No tienes permiso para editar este registro.🫵😂');
        }

        // Validar los datos del formulario
        $validated = $request->validate([
            'registro' => 'required|in:si,no,na', // Solo validamos el campo 'registro'
            'observacion' => 'nullable|string',
        ]);

        // Asignar el ID del usuario autenticado manualmente
        $validated['id_usuario'] = auth()->id();

        // Actualizar el registro de Compras
        $comprasCliente->update($validated);

        // Obtener el mes de la fecha del registro editado
        $mes = date('F', strtotime($comprasCliente->fecha));

        // Redirigir a la ruta 'empresas_clientes.filtrar_por_mes' con el mes como parámetro
        return redirect()->route('empresas_clientes.filtrar_por_mes', ['mes' => $mes])
            ->with('success', 'Registro de compras actualizadas correctamente.');
    }

}