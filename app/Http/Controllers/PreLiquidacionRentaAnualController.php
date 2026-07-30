<?php

namespace App\Http\Controllers;

use App\Models\DeterminacionRenta3CA;
use Illuminate\Http\Request;
use App\Models\PreLiquidacionRentaAnual;

class PreLiquidacionRentaAnualController extends Controller
{
    // Función para mostrar el formulario de pre-liquidación
    public function create($id_empresa, $anio)
    {
        // Aquí puedes obtener la información de la empresa y pasarla al formulario
        $empresa = \App\Models\EmpresasClientes::findOrFail($id_empresa);
        return view('pre_liquidacion.create', compact('empresa', 'anio'));
    }

    // Función para guardar los datos de la pre-liquidación
    public function store(Request $request)
    {
        $request->validate([
            'id_empresas_clientes' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'anio' => 'required|integer|min:2000|max:' . date('Y'),
            'ventas_netas' => 'required|numeric',
            'costo_ventas' => 'nullable|numeric',
            'resultado_bruto' => 'required|numeric',
            'gastos_ventas' => 'nullable|numeric',
            'gastos_administracion' => 'nullable|numeric',
            'depreciacion' => 'nullable|numeric',
            'resultado_operacion' => 'required|numeric',

            'perdida_tipo_cambio' => 'nullable|numeric',
            'intereses_otros_gastos' => 'nullable|numeric',
            'total_gastos_financieros' => 'required|numeric',

            'ingresos_financieros_gravados' => 'nullable|numeric',
            'ingresos_financieros_tipo_cambio' => 'nullable|numeric',
            'total_ingresos_financieros' => 'required|numeric',

            'otros_ingresos_gravados' => 'nullable|numeric',

            'total' => 'required|numeric',


        ]);

        // Crear la pre-liquidación con valores predeterminados en caso de que los campos no se ingresen
        $preLiquidacion = PreLiquidacionRentaAnual::create([
            'id_empresas_clientes' => $request->id_empresas_clientes,
            'anio' => $request->anio,
            'ventas_netas' => $request->ventas_netas,
            'costo_ventas' => $request->costo_ventas ?? 0,
            'gastos_ventas' => $request->gastos_ventas ?? 0,
            'resultado_bruto' => $request->resultado_bruto,
            'gastos_administracion' => $request->gastos_administracion ?? 0,
            'depreciacion' => $request->depreciacion ?? 0,
            'resultado_operacion' => $request->resultado_operacion,

            'perdida_tipo_cambio' => $request->perdida_tipo_cambio ?? 0,
            'intereses_otros_gastos' => $request->intereses_otros_gastos ?? 0,
            'total_gastos_financieros' => $request->total_gastos_financieros,

            'ingresos_financieros_gravados' => $request->ingresos_financieros_gravados ?? 0,
            'ingresos_financieros_tipo_cambio' => $request->ingresos_financieros_tipo_cambio ?? 0,
            'total_ingresos_financieros' => $request->total_ingresos_financieros,

            'otros_ingresos_gravados' => $request->otros_ingresos_gravados ?? 0,

            'total' => $request->total,

            'id_usuario' => auth()->id(),
            'fecha' => now(),
        ]);

        return redirect()->route('determinacion-renta.create', ['id_pre_liquidacion' => $preLiquidacion->id_pre_liquidacion])
            ->with('success', 'Pre-liquidación registrada exitosamente.');
    }


    // Método para eliminar una determinación de renta
    public function destroy($id_pre_liquidacion)
    {
        // Buscar la pre-liquidación por su ID
        $preLiquidacion = PreLiquidacionRentaAnual::findOrFail($id_pre_liquidacion);

        // Eliminar la determinación de renta asociada (si existe)
        $determinacion = DeterminacionRenta3CA::where('id_pre_liquidacion', $id_pre_liquidacion)->first();
        if ($determinacion) {
            $determinacion->delete();
        }

        // Eliminar la pre-liquidación
        $preLiquidacion->delete();

        // Redirigir con un mensaje de éxito
        return redirect()->route('determinacion-renta.index')
            ->with('success', 'Determinación de renta eliminada exitosamente.');
    }

    //EDITAR
    public function edit($id)
    {
        $preLiquidacion = PreLiquidacionRentaAnual::findOrFail($id);
        $determinacion = DeterminacionRenta3CA::where('id_pre_liquidacion', $id)->first();

        return view('pre_liquidacion.edit', compact('preLiquidacion', 'determinacion'));
    }

    public function update(Request $request, $id)
    {
        // Validación de los campos, permitiendo que algunos sean nullable
        $request->validate([
            'anio' => 'required|numeric',
            'ventas_netas' => 'required|numeric',
            'costo_ventas' => 'nullable|numeric',
            'gastos_ventas' => 'nullable|numeric',
            'gastos_administracion' => 'nullable|numeric',
            'depreciacion' => 'nullable|numeric',
            'perdida_tipo_cambio' => 'nullable|numeric',
            'intereses_otros_gastos' => 'nullable|numeric',
            'ingresos_financieros_gravados' => 'nullable|numeric',
            'ingresos_financieros_tipo_cambio' => 'nullable|numeric',
            'otros_ingresos_gravados' => 'nullable|numeric',
            'total' => 'required|numeric',
            'resultado_bruto' => 'required|numeric',
            'resultado_operacion' => 'required|numeric',
            'total_gastos_financieros' => 'required|numeric',
            'total_ingresos_financieros' => 'required|numeric'
        ]);

        // Buscar la pre-liquidación por su ID
        $preLiquidacion = PreLiquidacionRentaAnual::findOrFail($id);

        // Actualizar todos los campos, incluso si algunos están vacíos
        $preLiquidacion->update([
            'anio' => $request->anio,
            'ventas_netas' => $request->ventas_netas,
            'costo_ventas' => $request->costo_ventas ?? 0,
            'gastos_ventas' => $request->gastos_ventas ?? 0,
            'gastos_administracion' => $request->gastos_administracion ?? 0,
            'depreciacion' => $request->depreciacion ?? 0,
            'perdida_tipo_cambio' => $request->perdida_tipo_cambio ?? 0,
            'intereses_otros_gastos' => $request->intereses_otros_gastos ?? 0,
            'ingresos_financieros_gravados' => $request->ingresos_financieros_gravados ?? 0,
            'ingresos_financieros_tipo_cambio' => $request->ingresos_financieros_tipo_cambio ?? 0,
            'otros_ingresos_gravados' => $request->otros_ingresos_gravados ?? 0,
            'total' => $request->total,
            'resultado_bruto' => $request->resultado_bruto,
            'resultado_operacion' => $request->resultado_operacion,
            'total_gastos_financieros' => $request->total_gastos_financieros,
            'total_ingresos_financieros' => $request->total_ingresos_financieros,
        ]);

        // Redirigir a la edición de la determinación de renta si existe
        if ($request->id_determinacion) {
            return redirect()->route('determinacion-renta.edit', $request->id_determinacion)
                ->with('success', 'Pre-Liquidación actualizada correctamente. Ahora edita la Determinación de Renta.');
        }

        return redirect()->route('determinacion-renta.index')->with('error', 'No se encontró la determinación de renta asociada.');
    }

}