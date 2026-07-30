<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\AfpEmpresaCliente;
use App\Models\EmpresasClientes;
use Carbon\Carbon;

class AfpEmpresaClienteController extends Controller
{
    public function create($id_empresas_clientes)
    {
        $empresaCliente = EmpresasClientes::findOrFail($id_empresas_clientes);
        return view('afp_empresas_clientes.create', compact('empresaCliente'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_empresas_clientes_afp' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'opcion_si_no' => 'required|in:si,no,na',
            'observacion' => 'nullable|string',
        ]);

        // Obtener la fecha de vencimiento de la AFP
        $fechaVencimientoAFP = '07/' . date('m/Y'); // Fecha de vencimiento de la AFP (formato: d/m/Y)
        $fechaVencimientoAFP = Carbon::createFromFormat('d/m/Y', $fechaVencimientoAFP)->startOfDay(); // Convertir a Carbon y eliminar la hora
        $fechaActual = Carbon::now()->startOfDay(); // Fecha actual sin hora

        // Determinar el estado
        $estado = $fechaVencimientoAFP->greaterThanOrEqualTo($fechaActual) ? 'dentro' : 'fuera';

        $existe = AfpEmpresaCliente::where('id_empresas_clientes_afp', $request->id_empresas_clientes_afp)
            ->whereMonth('fecha', $fechaActual->month)
            ->whereYear('fecha', $fechaActual->year)
            ->exists();

        if ($existe) {
            return redirect()->back()->with('error', 'Ya existe un registro AFP para este cliente en este mes.');
        }


        // Guardar el registro
        AfpEmpresaCliente::create([
            'id_empresas_clientes_afp' => $request->id_empresas_clientes_afp,
            'fecha' => $fechaActual, // Fecha actual
            'opcion_si_no' => $request->opcion_si_no,
            'id_usuario' => auth()->id(), // ID del usuario autenticado
            'observacion' => $request->observacion, // Observación (opcional)
            'estado' => $estado, // Estado basado en la fecha de vencimiento
        ]);

        return redirect()->route('empresas_clientes.afp_plame')->with('success', 'Registro AFP creado exitosamente.');
    }
    public function edit($id_afp)
    {
        $afpCliente = AfpEmpresaCliente::findOrFail($id_afp);

        if (
            !in_array(Auth::user()->tipo_usuario, [1, 2]) &&
            ($afpCliente->id_usuario != Auth::id() || !Auth::user()->puede_editar)
        ) {
            abort(403, 'No tienes permiso para editar este registro.🫵😂');
        }
        return view('empresas_clientes_afp.edit', compact('afpCliente'));
    }

    public function update(Request $request, $id_afp)
    {
        // Buscar el registro de AFP
        $afpCliente = AfpEmpresaCliente::findOrFail($id_afp);

        // Verificar si el usuario tiene permisos para editar
        if (
            !in_array(Auth::user()->tipo_usuario, [1, 2]) &&
            (Auth::user()->id != $afpCliente->id_usuario || !Auth::user()->puede_editar)
        ) {
            abort(403, 'No tienes permiso para editar este registro.🫵😂');
        }

        // Validar los datos del formulario
        $validated = $request->validate([
            'observacion' => 'nullable|string',
            'opcion_si_no' => 'required|in:si,no,na',
            'estado' => 'required|in:dentro,fuera', // Validar el campo estado
        ]);

        // Asignar el ID del usuario autenticado manualmente
        $validated['id_usuario'] = auth()->id();

        // Actualizar el registro de AFP
        $afpCliente->update($validated);

        // Obtener el mes de la fecha del registro editado
        $mes = date('F', strtotime($afpCliente->fecha));

        // Redirigir a la ruta 'empresas_clientes.filtrar_por_mes_afp_pdtplame' con el mes como parámetro
        return redirect()->route('empresas_clientes.filtrar_por_mes_afp_pdtplame', ['mes' => $mes])
            ->with('success', 'Registro AFP actualizado correctamente.');
    }
}