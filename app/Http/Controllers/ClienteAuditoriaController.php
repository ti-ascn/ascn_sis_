<?php

namespace App\Http\Controllers;

use App\Models\ClienteAuditoria;
use Illuminate\Http\Request;

class ClienteAuditoriaController extends Controller
{
    public function index()
    {
        $clientes = ClienteAuditoria::orderBy('razon_social')->get();
        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ruc' => 'required|string|max:11|unique:clientes_auditoria,ruc',
            'razon_social' => 'required|string|max:255',
            'rubro' => 'nullable|string|max:255',
            'contacto' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'correo' => 'nullable|email|max:255',
        ], [
            'ruc.unique' => 'El RUC ya ha sido registrado.',
            'ruc.required' => 'El RUC es obligatorio.',
            'ruc.max' => 'El RUC no debe exceder los 11 caracteres.',
            'correo.email' => 'El formato del correo no es válido.',
        ]);

        $validated['razon_social'] = strtoupper($validated['razon_social']);
        $validated['rubro'] = strtoupper($validated['rubro']);
        $validated['contacto'] = strtoupper($validated['contacto']);
        $validated['telefono'] = strtoupper($validated['telefono']);

        if ($request->ajax()) {
            $nuevo = ClienteAuditoria::create($validated);
            return response()->json(['success' => true, 'cliente' => $nuevo]);
        }

        ClienteAuditoria::create($validated);

        return redirect()->route('clientes.index')->with('success', 'Cliente registrado con éxito.');
    }

    public function edit($id)
    {
        $cliente = ClienteAuditoria::findOrFail($id);
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'ruc' => 'required|string|max:11',
            'razon_social' => 'required|string|max:255',
            'rubro' => 'nullable|string|max:255',
            'contacto' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'correo' => 'nullable|email|max:255',
        ]);

        $validated['razon_social'] = strtoupper($validated['razon_social']);
        $validated['rubro'] = strtoupper($validated['rubro']);
        $validated['contacto'] = strtoupper($validated['contacto']);
        $validated['telefono'] = strtoupper($validated['telefono']);

        $cliente = ClienteAuditoria::findOrFail($id);
        $cliente->update($validated);

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado con éxito.');
    }

    public function destroy($id)
    {
        ClienteAuditoria::destroy($id);
        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado correctamente.');
    }

    public function activar($id)
    {
        $cliente = ClienteAuditoria::findOrFail($id);
        $cliente->estado_actual = 1;
        $cliente->save();
        return back()->with('success', 'Cliente activado correctamente.');
    }

    public function suspender($id)
    {
        $cliente = ClienteAuditoria::findOrFail($id);
        $cliente->estado_actual = 2;
        $cliente->save();
        return back()->with('success', 'Cliente suspendido correctamente.');
    }

    public function retirar($id)
    {
        $cliente = ClienteAuditoria::findOrFail($id);
        $cliente->estado_actual = 0;
        $cliente->save();
        return back()->with('success', 'Cliente retirado correctamente.');
    }

}
