<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\PdtplameEmpresaCliente;
use App\Models\EmpresasClientes;
use Carbon\Carbon;

class PdtplameEmpresaClienteController extends Controller
{
    public function create($id_empresas_clientes, Request $request)
    {
        $empresaCliente = EmpresasClientes::findOrFail($id_empresas_clientes);
        $vencimientoPDTPlame = $request->query('vencimientoPDTPlame'); // Obtener el parámetro de consulta
        return view('pdtplame_empresas_clientes.create', compact('empresaCliente', 'vencimientoPDTPlame'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_empresas_clientes_pdtplame' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'vencimientoPDTPlame' => 'required|date',
            'opcion_si_no' => 'required|in:si,no,na',
            'observacion' => 'nullable|string',
        ]);

        // Convertir la fecha a un objeto Carbon
        $vencimientoPDTPlame = Carbon::parse($request->vencimientoPDTPlame)->startOfDay(); // Ignorar la hora
        $fechaActual = Carbon::now()->startOfDay(); // Ignorar la hora

        // Determinar el estado
        $estado = $vencimientoPDTPlame->greaterThanOrEqualTo($fechaActual) ? 'dentro' : 'fuera';

        // Prevenir duplicado
        $existe = PdtplameEmpresaCliente::where('id_empresas_clientes_pdtplame', $request->id_empresas_clientes_pdtplame)
            ->whereMonth('fecha', $fechaActual->month)
            ->whereYear('fecha', $fechaActual->year)
            ->exists();

        if ($existe) {
            return redirect()->back()->with('error', 'Ya existe un registro PDT PLAME para este cliente en este mes.');
        }

        // Guardar el registro
        PdtplameEmpresaCliente::create([
            'id_empresas_clientes_pdtplame' => $request->id_empresas_clientes_pdtplame,
            'estado' => $estado,
            'fecha' => $fechaActual,
            'id_usuario' => auth()->id(),
            'opcion_si_no' => $request->opcion_si_no,
            'observacion' => $request->observacion,
        ]);

        return redirect()->route('empresas_clientes.afp_plame')->with('success', 'Registro PDT Plame creado exitosamente.');
    }

    public function edit($id_pdtplame)
    {
        $pdtplameCliente = PdtplameEmpresaCliente::findOrFail($id_pdtplame);

        // Verificar si el usuario tiene permisos
        if (
            !in_array(Auth::user()->tipo_usuario, [1, 2]) &&
            ($pdtplameCliente->id_usuario != Auth::id() || !Auth::user()->puede_editar)
        ) {
            abort(403, 'No tienes permiso para editar este registro.🫵😂');
        }

        return view('empresas_clientes_pdtplame.edit', compact('pdtplameCliente'));
    }


    public function update(Request $request, $id_pdtplame)
    {
        $pdtplameCliente = PdtplameEmpresaCliente::findOrFail($id_pdtplame);

        // Verificar si el usuario tiene permisos para editar
        if (
            !in_array(Auth::user()->tipo_usuario, [1, 2]) &&
            (Auth::user()->id != $pdtplameCliente->id_usuario || !Auth::user()->puede_editar)
        ) {
            abort(403, 'No tienes permiso para editar este registro.🫵😂');
        }

        // Validar los datos del formulario
        $validated = $request->validate([
            'opcion_si_no' => 'required|in:si,no,na',
            'observacion' => 'nullable|string',
            'estado' => 'required|in:dentro,fuera',
        ]);

        // Asignar el ID del usuario autenticado manualmente
        $validated['id_usuario'] = auth()->id();

        // Actualizar el registro
        $pdtplameCliente->update($validated);

        // Obtener el mes
        $mes = date('F', strtotime($pdtplameCliente->fecha));

        return redirect()->route('empresas_clientes.filtrar_por_mes_afp_pdtplame', ['mes' => $mes])
            ->with('success', 'Registro PDT Plame actualizado correctamente.');
    }

}