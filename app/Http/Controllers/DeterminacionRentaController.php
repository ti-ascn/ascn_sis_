<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\PreLiquidacionRentaAnual;
use App\Models\DeterminacionRenta3CA;

class DeterminacionRentaController extends Controller
{
    public function create($id_pre_liquidacion)
    {
        // Obtener la pre-liquidación
        $preLiquidacion = PreLiquidacionRentaAnual::findOrFail($id_pre_liquidacion);

        return view('determinacion-renta.create', compact('preLiquidacion'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        // Validar los datos del formulario
        $validatedData = $request->validate([
            'id_pre_liquidacion' => 'required|exists:pre_liquidacion_renta_anual,id_pre_liquidacion',
            'utilidad_antes' => 'required|numeric',
            // 'valor_uit' => 'required|numeric',
            // 'regimen' => 'required|in:1,2',
            'adicion_depreciacion' => 'nullable|numeric',
            'adicion_gastos_personales' => 'nullable|numeric',
            'adicion_multas_cargos' => 'nullable|numeric',
            'adicion_gastos_rcp' => 'nullable|numeric',
            'adicion_otros' => 'nullable|numeric',
            'adicion_total' => 'nullable|numeric',
            'deduccion_vaciones' => 'nullable|numeric',
            'deduccion_depreciacion' => 'nullable|numeric',
            'deduccion_otros' => 'nullable|numeric',
            'deduccion_total' => 'nullable|numeric',
            'utilidad_tributaria' => 'nullable|numeric',
            'impuesto_hasta_15_uit' => 'nullable|numeric',
            'impuesto_exceso_15_uit' => 'nullable|numeric',
            'impuesto_total' => 'nullable|numeric',
            'pagos_cuenta' => 'nullable|numeric',
            'pago_itan' => 'nullable|numeric',
            'saldos_no_aplicados' => 'nullable|numeric',
            'total_por_pagar' => 'nullable|numeric',
            'total_a_favor' => 'nullable|numeric',
            'id_usuario' => 'required',
        ]);

        // Crear una nueva instancia del modelo DeterminacionRenta3CA
        $determinacionRenta = new DeterminacionRenta3CA();

        // Asignar los valores validados a los atributos del modelo
        $determinacionRenta->id_pre_liquidacion = $validatedData['id_pre_liquidacion'];
        $determinacionRenta->utilidad_antes = $validatedData['utilidad_antes'];
        // $determinacionRenta->valor_uit = $validatedData['valor_uit'];
        // $determinacionRenta->regimen = $validatedData['regimen'];
        $determinacionRenta->adicion_depreciacion = $validatedData['adicion_depreciacion'] ?? 0;
        $determinacionRenta->adicion_gastos_personales = $validatedData['adicion_gastos_personales'] ?? 0;
        $determinacionRenta->adicion_multas_cargos = $validatedData['adicion_multas_cargos'] ?? 0;
        $determinacionRenta->adicion_gastos_rcp = $validatedData['adicion_gastos_rcp'] ?? 0;
        $determinacionRenta->adicion_otros = $validatedData['adicion_otros'] ?? 0;
        $determinacionRenta->adicion_total = $validatedData['adicion_total'] ?? 0;
        $determinacionRenta->deduccion_vaciones = $validatedData['deduccion_vaciones'] ?? 0;
        $determinacionRenta->deduccion_depreciacion = $validatedData['deduccion_depreciacion'] ?? 0;
        $determinacionRenta->deduccion_otros = $validatedData['deduccion_otros'] ?? 0;
        $determinacionRenta->deduccion_total = $validatedData['deduccion_total'] ?? 0;
        $determinacionRenta->utilidad_tributaria = $validatedData['utilidad_tributaria'] ?? 0;
        $determinacionRenta->impuesto_hasta_15_uit = $validatedData['impuesto_hasta_15_uit'] ?? 0;
        $determinacionRenta->impuesto_exceso_15_uit = $validatedData['impuesto_exceso_15_uit'] ?? 0;
        $determinacionRenta->impuesto_total = $validatedData['impuesto_total'] ?? 0;
        $determinacionRenta->pagos_cuenta = $validatedData['pagos_cuenta'] ?? 0;
        $determinacionRenta->pago_itan = $validatedData['pago_itan'] ?? 0;
        $determinacionRenta->saldos_no_aplicados = $validatedData['saldos_no_aplicados'] ?? 0;
        $determinacionRenta->total_por_pagar = $validatedData['total_por_pagar'] ?? 0;
        $determinacionRenta->total_a_favor = $validatedData['total_a_favor'] ?? 0;
        $determinacionRenta->id_usuario = $validatedData['id_usuario'];

        // Guardar el modelo en la base de datos
        $determinacionRenta->save();

        // Redireccionar a una ruta específica con un mensaje de éxito
        return redirect()->route('determinacion-renta.show', $determinacionRenta->id_determinacion)
            ->with('success', 'Determinación de renta guardada exitosamente.');
    }

    public function show($id_determinacion)
    {
        // Obtener la pre-liquidación
        $determinacionRenta = DeterminacionRenta3CA::findOrFail($id_determinacion);

        $id_pre_liquidacion = $determinacionRenta->id_pre_liquidacion;

        // Obtener la determinación de renta asociada a la pre-liquidación
        $preLiquidacion = PreLiquidacionRentaAnual::where('id_pre_liquidacion', $id_pre_liquidacion)->first();

        // Pasar los datos a la vista
        return view('determinacion-renta.show', compact('preLiquidacion', 'determinacionRenta'));
    }

    public function index()
    {
        // Obtener todas las determinaciones de renta
        $determinaciones = DeterminacionRenta3CA::all();

        // Retornar la vista con los datos
        return view('determinacion-renta.index', compact('determinaciones'));
    }

    public function edit($id_determinacion)
    {
        // Obtener la pre-liquidación
        $determinacionRenta = DeterminacionRenta3CA::findOrFail($id_determinacion);

        $id_pre_liquidacion = $determinacionRenta->id_pre_liquidacion;

        // Obtener la determinación de renta asociada a la pre-liquidación
        $preLiquidacion = PreLiquidacionRentaAnual::where('id_pre_liquidacion', $id_pre_liquidacion)->first();

        // Pasar los datos a la vista de edición
        return view('determinacion-renta.edit', compact('preLiquidacion', 'determinacionRenta'));
    }

    public function update(Request $request, $id_pre_liquidacion)
    {
        // Validar los datos del formulario
        $validatedData = $request->validate([
            'utilidad_antes' => 'required|numeric',
            'adicion_depreciacion' => 'nullable|numeric',
            'adicion_gastos_personales' => 'nullable|numeric',
            'adicion_multas_cargos' => 'nullable|numeric',
            'adicion_gastos_rcp' => 'nullable|numeric',
            'adicion_otros' => 'nullable|numeric',
            'adicion_total' => 'nullable|numeric',
            'deduccion_vaciones' => 'nullable|numeric',
            'deduccion_depreciacion' => 'nullable|numeric',
            'deduccion_otros' => 'nullable|numeric',
            'deduccion_total' => 'nullable|numeric',
            'utilidad_tributaria' => 'nullable|numeric',
            'impuesto_hasta_15_uit' => 'nullable|numeric',
            'impuesto_exceso_15_uit' => 'nullable|numeric',
            'impuesto_total' => 'nullable|numeric',
            'pagos_cuenta' => 'nullable|numeric',
            'pago_itan' => 'nullable|numeric',
            'saldos_no_aplicados' => 'nullable|numeric',
            'total_por_pagar' => 'nullable|numeric',
            'total_a_favor' => 'nullable|numeric',
            'id_usuario' => 'required',
        ]);

        // Buscar la determinación de renta existente
        $determinacionRenta = DeterminacionRenta3CA::where('id_pre_liquidacion', $id_pre_liquidacion)->firstOrFail();

        // Actualizar los valores
        $determinacionRenta->update($validatedData);

        // Redireccionar a la vista de detalles con un mensaje de éxito
        return redirect()->route('determinacion-renta.show', $determinacionRenta->id_determinacion)
            ->with('success', 'Determinación de renta guardada exitosamente.');
    }

    public function generarPDF(Request $request)
    {
        $idPre = $request->input('id_pre_liquidacion');
        $tituloBase = $request->input('titulo');
        $mesCierre = (int) $request->input('mes_cierre'); // ← forzamos entero

        if (!$idPre || !$tituloBase) {
            return back()->with('error', 'Debe seleccionar todos los campos.');
        }

        $pre = PreLiquidacionRentaAnual::with(['empresaCliente'])->findOrFail($idPre);
        $det = DeterminacionRenta3CA::where('id_pre_liquidacion', $idPre)->first();

        $anio = $pre->anio;
        $empresa = $pre->empresaCliente;

        // Construcción del título final
        $titulo = match (true) {
            str_contains($tituloBase, 'AL CIERRE') =>
            "$tituloBase " . strtoupper(Carbon::create()->month($mesCierre)->locale('es')->monthName) . " $anio",
            default => "$tituloBase $anio"
        };

        $pdf = Pdf::loadView('pdf.renta-anual', [
            'empresa' => $empresa,
            'preLiquidacion' => $pre,
            'determinacionRenta' => $det,
            'anio' => $anio,
            'titulo' => $titulo,
            'fechaHoy' => now()->format('d/m/Y'),
        ])->setPaper('a4', 'portrait');

        $nombre = 'REPORTE_RENTA_' . strtoupper(str_replace(' ', '_', $empresa->razon_social)) . '.pdf';
        return $pdf->stream($nombre);
    }

}