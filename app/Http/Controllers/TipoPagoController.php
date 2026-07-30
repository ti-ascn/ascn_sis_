<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TiposPago;

class TipoPagoController extends Controller
{
    public function index()
    {
        $tipos_pago = TiposPago::latest()->paginate(10);
        return view('tipos_pagos.index', compact('tipos_pago'));
    }

    public function create()
    {
        return view('tipos_pagos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:tipos_pagos,nombre',
            'descripcion' => 'nullable|string|max:255',
        ]);

        // Convertir el nombre a mayúsculas antes de almacenarlo
        $request['nombre'] = strtoupper($request->nombre);

        TiposPago::create($request->all());

        return redirect()->route('tipos_pagos.index')->with('success', 'Tipo de pago registrado correctamente.');
    }


    public function edit($id_tipo_pago)
    {
        $tipo_pago = TiposPago::findOrFail($id_tipo_pago);  // Usamos findOrFail para obtener el tipo de pago o abortar si no existe
        return view('tipos_pagos.edit', compact('tipo_pago'));  // Pasamos el tipo de pago a la vista
    }

    public function update(Request $request, $id_tipo_pago)
    {
        // Validamos los datos del formulario
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:tipos_pagos,nombre,' . $id_tipo_pago . ',id_tipo_pago',
            'descripcion' => 'nullable|string|max:255',
        ]);

        // Convertimos el campo 'nombre' a mayúsculas antes de guardarlo
        $validated['nombre'] = strtoupper($validated['nombre']);

        // Obtenemos el tipo de pago usando el ID
        $tipoPago = TiposPago::findOrFail($id_tipo_pago);

        // Actualizamos el tipo de pago con los datos validados
        $tipoPago->update($validated);

        // Redirigimos con un mensaje de éxito
        return redirect()->route('tipos_pagos.index')->with('success', 'Tipo de pago actualizado correctamente.');
    }

    public function destroy($id)
    {
        $tipoPago = TiposPago::findOrFail($id);

        if ($tipoPago->pagosProgramados()->exists()) {
            return redirect()->route('tipos_pagos.index')
                ->with('error', 'No se puede eliminar: Hay pagos programados que dependen de este tipo de pago.');
        }

        $tipoPago->delete();

        return redirect()->route('tipos_pagos.index')
            ->with('success', 'Tipo de pago eliminado correctamente.');
    }

}
