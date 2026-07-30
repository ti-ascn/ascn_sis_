<?php

namespace App\Http\Controllers;

use App\Models\CotizacionRecepcion;
use App\Models\EmpresasClientes;
use Illuminate\Http\Request;

class CotizacionRecepcionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_empresa_cliente' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'nro_cotizacion' => 'nullable|string|max:255',
            'tipo_cotizacion' => 'required|in:contable,otros'
        ]);

        // Verificar si ya existe una cotización con el mismo número
        $cotizacionExistente = CotizacionRecepcion::where('nro_cotizacion', $request->nro_cotizacion)
            ->where('nro_cotizacion', '!=', null)
            ->where('nro_cotizacion', '!=', '')
            ->first();

        if ($cotizacionExistente) {
            return back()->with('error', 'Cotización existente.');
        }

        CotizacionRecepcion::create([
            'id_empresa_cliente' => $request->id_empresa_cliente,
            'nro_cotizacion' => $request->nro_cotizacion,
            'tipo_cotizacion' => $request->tipo_cotizacion
        ]);

        return back()->with('success', 'Cotización creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CotizacionRecepcion $cotizacionRecepcion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CotizacionRecepcion $cotizacionRecepcion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nro_cotizacion' => 'nullable|string|max:255',
            'tipo_cotizacion' => 'required|in:contable,otros'
        ]);

        $cotizacion = CotizacionRecepcion::findOrFail($id);

        // Verificar si ya existe una cotización con el mismo número (excluyendo la actual)
        if ($request->nro_cotizacion) {
            $cotizacionExistente = CotizacionRecepcion::where('nro_cotizacion', $request->nro_cotizacion)
                ->where('id_cotizacion', '!=', $id)
                ->first();

            if ($cotizacionExistente) {
                return back()->with('error', 'Ya existe una cotización con ese número.');
            }
        }

        $cotizacion->update([
            'nro_cotizacion' => $request->nro_cotizacion,
            'tipo_cotizacion' => $request->tipo_cotizacion
        ]);

        // Redirect to lista if coming from there, otherwise back
        if ($request->has('from_lista')) {
            return redirect()->route('deudas_recepcion.lista')->with('success', 'Cotización actualizada correctamente.');
        }
        
        return back()->with('success', 'Cotización actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $cotizacion = CotizacionRecepcion::findOrFail($id);
        
        // Eliminar todas las deudas asociadas y sus pagos
        foreach ($cotizacion->deudas as $deuda) {
            $deuda->pagos()->delete();
            $deuda->delete();
        }
        
        // Eliminar la cotización
        $cotizacion->delete();
        
        return redirect()->route('deudas_recepcion.lista')->with('success', 'Cotización y todas sus deudas eliminadas correctamente.');
    }
}
