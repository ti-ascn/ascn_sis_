<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\PdtEmpresaCliente;
use App\Models\EmpresasClientes;
use Carbon\Carbon;

class PdtEmpresaClienteController extends Controller
{
    public function create($id_empresas_clientes, Request $request)
    {
        $empresaCliente = EmpresasClientes::findOrFail($id_empresas_clientes);
        $vencimientoPDT = $request->query('vencimientoPDT'); // Obtener el parámetro de consulta
        return view('pdt_empresas_clientes.create', compact('empresaCliente', 'vencimientoPDT'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_empresas_clientes_pdt' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'vencimientoPDT' => 'required|date',
            'opcion_si_no' => 'required|in:si,no',
            'observacion' => 'nullable|string',
        ]);

        // Convertir la fecha a un objeto Carbon
        $vencimientoPDT = Carbon::parse($request->vencimientoPDT)->startOfDay(); // Ignorar la hora
        $fechaActual = Carbon::now()->startOfDay(); // Ignorar la hora

        // Determinar el estado
        $estado = $vencimientoPDT->greaterThanOrEqualTo($fechaActual) ? 'dentro' : 'fuera';

        // Prevenir registro duplicado por mes y cliente
        $existe = PdtEmpresaCliente::where('id_empresas_clientes_pdt', $request->id_empresas_clientes_pdt)
            ->whereMonth('fecha', $fechaActual->month)
            ->whereYear('fecha', $fechaActual->year)
            ->exists();

        if ($existe) {
            return redirect()->back()->with('error','Ya existe un registro PDT para este cliente en este mes.');
        }

        // Guardar el registro
        PdtEmpresaCliente::create([
            'id_empresas_clientes_pdt' => $request->id_empresas_clientes_pdt,
            'estado' => $estado,
            'fecha' => $fechaActual,
            'id_usuario' => auth()->id(),
            'opcion_si_no' => $request->opcion_si_no,
            'observacion' => $request->observacion,
        ]);

        return redirect()->route('empresas_clientes.index')->with('success', 'Registro PDT creado exitosamente.');
    }

    // Método para mostrar el formulario de edición
    public function edit($id_empresas_clientes_pdt)
    {
        $pdtCliente = PdtEmpresaCliente::findOrFail($id_empresas_clientes_pdt);

        // Verificar si el usuario tiene permisos
        if (
            Auth::user()->tipo_usuario != 1 &&
            ($pdtCliente->id_usuario != Auth::id() || !Auth::user()->puede_editar)
        ) {
            abort(403, 'No tienes permiso para editar este registro.🫵😂');
        }
        return view('empresas_clientes_pdt.edit', compact('pdtCliente'));
    }

    // Método para actualizar un registro de PDT
    public function update(Request $request, $id_empresas_clientes_pdt)
    {
        // Buscar el registro de PDT
        $pdtCliente = PdtEmpresaCliente::findOrFail($id_empresas_clientes_pdt);

        // Verificar si el usuario tiene permisos para editar
        if (
            Auth::user()->tipo_usuario != 1 &&
            (Auth::user()->id != $pdtCliente->id_usuario || !Auth::user()->puede_editar)
        ) {
            abort(403, 'No tienes permiso para editar este registro.🫵😂');
        }

        // Validar los datos del formulario
        $validated = $request->validate([
            'opcion_si_no' => 'required|in:si,no',
            'observacion' => 'nullable|string',
            'estado' => 'required|in:dentro,fuera', // Validar el campo estado
        ]);

        // Asignar el ID del usuario autenticado manualmente
        $validated['id_usuario'] = auth()->id();

        // Actualizar el registro de PDT
        $pdtCliente->update($validated);

        // Obtener el mes de la fecha del registro editado
        $mes = date('F', strtotime($pdtCliente->fecha));

        // Redirigir a la ruta 'empresas_clientes.filtrar_por_mes' con el mes como parámetro
        return redirect()->route('empresas_clientes.filtrar_por_mes', ['mes' => $mes])
            ->with('success', 'Registro PDT actualizado correctamente.');
    }
}