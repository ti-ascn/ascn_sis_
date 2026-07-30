<?php

namespace App\Http\Controllers;

use App\Models\ClienteAuditoria;
use Illuminate\Http\Request;
use App\Models\Direccion;
use App\Models\EmpresasClientes;

class DireccionController extends Controller
{
    public function index()
    {
        // Cargamos también 'creador' y 'editor'
        $direcciones = Direccion::with(['empresa', 'creador', 'editor'])->get();
        return view('direcciones.index', compact('direcciones'));
    }

    public function createEmpresa($empresaId = null)
    {
        $empresas = EmpresasClientes::orderBy('razon_social')->get();
        return view('direcciones.create_empresa', compact('empresas', 'empresaId'));
    }

    public function createCliente($clienteId = null)
    {
        $clientes = ClienteAuditoria::orderBy('razon_social')->get();
        return view('direcciones.create_cliente', compact('clientes', 'clienteId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_empresas_clientes' => 'nullable|exists:empresas_clientes,id_empresas_clientes',
            'id_cliente' => 'nullable|exists:clientes_auditoria,id_cliente',
            'nombre' => 'required|string|max:255',
            'distrito' => 'nullable|string|max:255',
            'provincia' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'estado' => 'required|boolean',
            'tipo_domicilio' => 'required|in:Fiscal,Sucursal,Punto de Llegada,Emision',
        ]);

        // Validación adicional personalizada
        if (!$request->filled('id_empresas_clientes') && !$request->filled('id_cliente')) {
            return back()->withErrors(['id_empresas_clientes' => 'Debe seleccionar una empresa o un cliente.'])->withInput();
        }

        if ($request->filled('id_empresas_clientes') && $request->filled('id_cliente')) {
            return back()->withErrors(['id_empresas_clientes' => 'Solo puede asignar la dirección a una empresa o a un cliente, no a ambos.'])->withInput();
        }

        $validated['nombre'] = strtoupper($validated['nombre']);

        Direccion::create($validated);

        return redirect()->route('direcciones.index')->with('success', 'Dirección registrada con éxito.');
    }

    public function edit($id_direccion)
    {
        $direccion = Direccion::findOrFail($id_direccion);
        $empresas = EmpresasClientes::orderBy('razon_social')->get();
        $clientes = ClienteAuditoria::orderBy('razon_social')->get();

        return view('direcciones.edit', compact('direccion', 'empresas', 'clientes'));
    }

    public function update(Request $request, $id_direccion)
    {
        $validated = $request->validate([
            'id_empresas_clientes' => 'nullable|exists:empresas_clientes,id_empresas_clientes',
            'id_cliente' => 'nullable|exists:clientes_auditoria,id_cliente',
            'nombre' => 'required|string|max:255',
            'distrito' => 'nullable|string|max:255',
            'provincia' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'estado' => 'required|boolean',
            'tipo_domicilio' => 'required|in:Fiscal,Sucursal,Punto de Llegada,Emision',
        ]);

        if (!$request->filled('id_empresas_clientes') && !$request->filled('id_cliente')) {
            return back()->withErrors(['id_empresas_clientes' => 'Debe asignarse a una empresa o un cliente.'])->withInput();
        }

        if ($request->filled('id_empresas_clientes') && $request->filled('id_cliente')) {
            return back()->withErrors(['id_empresas_clientes' => 'Solo uno puede estar asignado.'])->withInput();
        }

        $validated['nombre'] = strtoupper($validated['nombre']);

        $direccion = Direccion::findOrFail($id_direccion);
        $direccion->update($validated);

        return redirect()->route('direcciones.index')->with('success', 'Dirección actualizada con éxito.');
    }


    public function destroy($id_direccion)
    {
        $direccion = Direccion::findOrFail($id_direccion);
        $direccion->delete();

        return redirect()->route('direcciones.index')->with('success', 'Dirección eliminada correctamente.');
    }

    public function porEmpresa($id)
    {
        $empresa = EmpresasClientes::findOrFail($id);
        $direcciones = Direccion::where('id_empresas_clientes', $id)->get();

        return view('direcciones.lista', compact('empresa', 'direcciones'));
    }

    public function porCliente($id)
    {
        $cliente = ClienteAuditoria::findOrFail($id);
        $direcciones = Direccion::where('id_cliente', $id)->get();

        return view('direcciones.lista_cliente', compact('cliente', 'direcciones'));
    }

    public function createDesdeCliente($clienteId)
    {
        $clientes = ClienteAuditoria::orderBy('razon_social')->get();

        return view('direcciones.create_cliente', compact('clientes', 'clienteId'));
    }

    public function desactivar($id)
    {
        $direccion = Direccion::findOrFail($id);
        $direccion->estado = 0;
        $direccion->fecha_baja = now(); // establece la fecha actual
        $direccion->save();

        return redirect()->back()->with('success', 'Dirección desactivada con éxito.');
    }

    public function reactivar($id)
    {
        $direccion = Direccion::findOrFail($id);
        $direccion->estado = 1;
        $direccion->fecha_baja = null; // limpia la fecha_baja
        $direccion->save();

        return redirect()->back()->with('success', 'Dirección activada con éxito.');
    }


}
