<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\SireEmpresaCliente;
use App\Models\EmpresasClientes;
use Carbon\Carbon;

class SireEmpresaClienteController extends Controller
{
    public function create($id_empresas_clientes, Request $request)
    {
        $empresaCliente = EmpresasClientes::findOrFail($id_empresas_clientes);
        $vencimientoSIRE = $request->query('vencimientoSIRE'); // Obtener el parámetro de consulta
        return view('sire_empresas_clientes.create', compact('empresaCliente', 'vencimientoSIRE'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_empresas_clientes_sire' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'vencimientoSIRE' => 'required|date',
            'opcion_si_no' => 'required|in:si,no',
            'observacion' => 'nullable|string',
        ]);

        // Convertir la fecha a un objeto Carbon
        $vencimientoSIRE = Carbon::parse($request->vencimientoSIRE)->startOfDay(); // Ignorar la hora
        $fechaActual = Carbon::now()->startOfDay(); // Ignorar la hora

        // Determinar el estado
        $estado = $vencimientoSIRE->greaterThanOrEqualTo($fechaActual) ? 'dentro' : 'fuera';

        $existe = SireEmpresaCliente::where('id_empresas_clientes_sire', $request->id_empresas_clientes_sire)
            ->whereMonth('fecha', $fechaActual->month)
            ->whereYear('fecha', $fechaActual->year)
            ->exists();

        if ($existe) {
            return redirect()->back()->with('error', 'Ya existe un registro SIRE para este cliente en este mes.');
        }


        // Guardar el registro
        SireEmpresaCliente::create([
            'id_empresas_clientes_sire' => $request->id_empresas_clientes_sire,
            'estado' => $estado,
            'fecha' => $fechaActual,
            'id_usuario' => auth()->id(),
            'opcion_si_no' => $request->opcion_si_no,
            'observacion' => $request->observacion,
        ]);

        return redirect()->route('empresas_clientes.index')->with('success', 'Registro SIRE creado exitosamente.');
    }

    // Método para mostrar el formulario de edición
    public function edit($id_empresas_clientes_sire)
    {
        $sireCliente = SireEmpresaCliente::findOrFail($id_empresas_clientes_sire);

        // Verificar si el usuario tiene permisos
        if (
            Auth::user()->tipo_usuario != 1 &&
            ($sireCliente->id_usuario != Auth::id() || !Auth::user()->puede_editar)
        ) {
            abort(403, 'No tienes permiso para editar este registro.🫵😂');
        }

        return view('empresas_clientes_sire.edit', compact('sireCliente'));
    }

    // Método para actualizar un registro de SIRE
    public function update(Request $request, $id_empresas_clientes_sire)
    {
        // Buscar el registro de SIRE
        $sireCliente = SireEmpresaCliente::findOrFail($id_empresas_clientes_sire);

        // Verificar si el usuario tiene permisos para editar
        if (
            Auth::user()->tipo_usuario != 1 &&
            (Auth::user()->id != $sireCliente->id_usuario || !Auth::user()->puede_editar)
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

        // Actualizar el registro de SIRE
        $sireCliente->update($validated);

        // Obtener el mes de la fecha del registro editado
        $mes = date('F', strtotime($sireCliente->fecha));

        // Redirigir a la ruta 'empresas_clientes.filtrar_por_mes' con el mes como parámetro
        return redirect()->route('empresas_clientes.filtrar_por_mes', ['mes' => $mes])
            ->with('success', 'Registro SIRE actualizado correctamente.');
    }
}