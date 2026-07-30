<?php

namespace App\Http\Controllers;

use App\Models\DeterminacionMensual;
use App\Models\EmpresasClientes;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Barryvdh\DomPDF\Facade\Pdf;
use Smalot\PdfParser\Parser;

class DeterminacionMensualController extends Controller
{
    //
    public function index(Request $request)
    {
        // Obtener el año seleccionado (o el año actual si no se proporciona)
        $anoActual = $request->input('ano', now()->year);

        // Obtener las empresas que tienen al menos una determinación mensual en cualquier año
        $empresasConDeterminaciones = EmpresasClientes::whereIn('estado_actual', [1, 2])
            ->whereHas('determinacionesMensuales')
            ->get();

        // Obtener las determinaciones mensuales del año seleccionado
        $determinaciones = DeterminacionMensual::whereYear('fecha', $anoActual)->get();

        // Crear un array para almacenar los datos de la tabla
        $datosTabla = [];

        foreach ($empresasConDeterminaciones as $empresa) {
            $fila = [
                'empresa' => $empresa->razon_social,
                'id_empresas_clientes' => $empresa->id_empresas_clientes,
            ];

            // Inicializar todos los meses como "Sin determinación"
            for ($mes = 1; $mes <= 12; $mes++) {
                $fila[$mes] = [
                    'tiene' => false,
                    'url' => null,
                ];
            }

            // Contar las determinaciones de la empresa en el año actual
            $determinacionesEmpresa = $determinaciones->where('id_empresas_clientes', $empresa->id_empresas_clientes);
            $fila['total_determinaciones'] = $determinacionesEmpresa->count();

            // Verificar si ya tiene determinaciones para el próximo año
            $fila['tiene_determinaciones_proximo_ano'] = DeterminacionMensual::where('id_empresas_clientes', $empresa->id_empresas_clientes)
                ->whereYear('fecha', $anoActual + 1)
                ->exists();

            // Buscar las determinaciones de la empresa
            foreach ($determinacionesEmpresa as $determinacion) {
                $fecha = Carbon::parse($determinacion->fecha);
                $mes = $fecha->month; // Obtener el mes de la determinación
                $fila[$mes] = [
                    'tiene' => true,
                    'url' => route('determinacion_mensual.show', $determinacion->id_determinacion_mensual),
                ];
            }

            $datosTabla[] = $fila;
        }

        // Pasar los datos a la vista
        return view('determinacion_mensual.index', [
            'datosTabla' => $datosTabla,
            'anoActual' => $anoActual,
        ]);
    }
    public function show($id)
    {
        // Obtener la determinación mensual por su ID
        $determinacion = DeterminacionMensual::findOrFail($id);

        // Obtener la empresa asociada a la determinación
        $empresa = EmpresasClientes::findOrFail($determinacion->id_empresas_clientes);

        // Pasar los datos a la vista
        return view('determinacion_mensual.show', [
            'determinacion' => $determinacion,
            'empresa' => $empresa,
        ]);
    }

    public function empresasSinDeterminacion()
    {
        // Obtener todas las empresas
        $empresas = EmpresasClientes::whereIn('estado_actual', [1, 2])->get();

        // Obtener las empresas que no tienen determinación mensual
        $empresasSinDeterminacion = $empresas->filter(function ($empresa) {
            return !DeterminacionMensual::where('id_empresas_clientes', $empresa->id_empresas_clientes)->exists();
        });

        return view('determinacion_mensual.empresas_sin_determinacion', compact('empresasSinDeterminacion'));
    }

    public function create(Request $request, $id_empresa)
    {
        // Buscar la empresa seleccionada por su ID
        $empresa = EmpresasClientes::findOrFail($id_empresa);

        // Obtener mes y año de la request, o usar los actuales por defecto
        $mes = $request->input('mes', now()->month);
        $ano = $request->input('ano', now()->year);

        // Asignar la fecha según los parámetros recibidos
        $fecha = Carbon::createFromDate((int) $ano, (int) $mes, 1);

        // Pasar el saldo a favor del período anterior (si existe) a 0 porque estamos creando una nueva determinación
        $saldoAFavorPeriodoAnterior = 0;
        $rentaAFavorPeriodoAnterior = 0;

        // Retornar la vista con los datos de la empresa y la fecha calculada
        return view('determinacion_mensual.create', compact(
            'empresa',
            'fecha',
            'saldoAFavorPeriodoAnterior',
            'rentaAFavorPeriodoAnterior'
        ));
    }

    public function store(Request $request)
    {
        // Validar los datos del formulario (agregar renta_pagar a las validaciones)
        $request->validate([
            'id_empresas_clientes' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'id_usuario' => 'required|exists:users,id',
            'fecha' => 'required|date',
            'base_compras_18' => 'nullable|numeric',
            'base_compras_10' => 'nullable|numeric',
            'porcentaje_compras' => 'nullable|numeric',
            'base_compras_ng' => 'nullable|numeric',
            'igv_compras_18' => 'nullable|numeric',
            'igv_compras_10' => 'nullable|numeric',
            'total_compras' => 'nullable|numeric',
            'base_ventas' => 'nullable|numeric',
            'igv_ventas' => 'nullable|numeric',
            'base_ventas_10' => 'nullable|numeric',
            'descuento_concedido_tributo' => 'nullable|numeric',
            'igv_ventas_10' => 'nullable|numeric',
            'total_ventas' => 'nullable|numeric',
            'igv_a_pagar' => 'nullable|numeric',
            'saldo_a_favor_periodo_anterior' => 'nullable|numeric',
            'total_igv_a_pagar' => 'nullable|numeric',
            'saldo_a_favor_renta_periodo_anterior' => 'nullable|numeric',
            'renta_pagar' => 'nullable|numeric', // Nuevo campo
            'total_renta_a_pagar' => 'nullable|numeric',
            'total_a_pagar_tributos' => 'nullable|numeric',
            'retenciones_del_periodo' => 'nullable|numeric',
            'retenciones_periodo_anterior' => 'nullable|numeric',
            'total_retenciones' => 'nullable|numeric',
            'percepciones_del_periodo' => 'nullable|numeric',
            'percepcion_periodo_anterior' => 'nullable|numeric',
            'percepciones_no_aplicadas' => 'nullable|numeric',
            'total_percepciones' => 'nullable|numeric',
            'porcentaje_mype' => 'nullable|numeric',
            'ingresos_netos' => 'nullable|numeric',
            'descuentos_conce_devolu' => 'nullable|numeric', // Nuevos campos para PDF
            'ventas_nogravadas_sinexpor' => 'nullable|numeric',
            'comprasnetas_gravadas_nogravadas' => 'nullable|numeric',
            'comprasnetas_nogravadas_exclusivas' => 'nullable|numeric',
            'comprasinternas_nogravadas' => 'nullable|numeric',
            'archivo_pdt' => 'nullable|extensions:pdf|max:10240',
            'archivo_pdt_rectificado' => 'nullable|extensions:pdf|max:10240',
            'archivo_pdt_constancia_rectificado' => 'nullable|extensions:pdf|max:10240',
        ]);

        // Guardar los datos en la base de datos
        $determinacion = DeterminacionMensual::create([
            'id_empresas_clientes' => $request->id_empresas_clientes,
            'id_usuario' => $request->id_usuario,
            'fecha' => $request->fecha,

            // Compras
            'base_compras_18' => $request->base_compras_18 ?? 0,
            'base_compras_10' => $request->base_compras_10 ?? 0,
            'porcentaje_compras' => $request->porcentaje_compras ?? 0,
            'base_compras_ng' => $request->base_compras_ng ?? 0,
            'igv_compras_18' => $request->igv_compras_18 ?? 0,
            'igv_compras_10' => $request->igv_compras_10 ?? 0,
            'total_compras' => $request->total_compras ?? 0,

            // Ventas
            'base_ventas' => $request->base_ventas ?? 0,
            'igv_ventas' => $request->igv_ventas ?? 0,
            'base_ventas_10' => $request->base_ventas_10 ?? 0,
            'igv_ventas_10' => $request->igv_ventas_10 ?? 0,
            'total_ventas' => $request->total_ventas ?? 0,

            // IGV
            'igv_a_pagar' => $request->igv_a_pagar ?? 0,
            'saldo_a_favor_periodo_anterior' => $request->saldo_a_favor_periodo_anterior ?? 0,
            'total_igv_a_pagar' => $request->total_igv_a_pagar ?? 0,

            // Renta
            'ingresos_netos' => $request->ingresos_netos ?? 0,
            'renta_pagar' => $request->renta_pagar ?? 0,
            'saldo_a_favor_renta_periodo_anterior' => $request->saldo_a_favor_renta_periodo_anterior ?? 0,
            'total_renta_a_pagar' => $request->total_renta_a_pagar ?? 0,

            // Tributos
            'total_a_pagar_tributos' => $request->total_a_pagar_tributos ?? 0,

            // Retenciones y Percepciones
            'retenciones_del_periodo' => $request->retenciones_del_periodo ?? 0,
            'retenciones_periodo_anterior' => $request->retenciones_periodo_anterior ?? 0,
            'total_retenciones' => $request->total_retenciones ?? 0,
            'percepciones_del_periodo' => $request->percepciones_del_periodo ?? 0,
            'percepcion_periodo_anterior' => $request->percepcion_periodo_anterior ?? 0,
            'percepciones_no_aplicadas' => $request->percepciones_no_aplicadas ?? 0,
            'total_percepciones' => $request->total_percepciones ?? 0,

            // Porcentaje MYPE
            'porcentaje_mype' => $request->porcentaje_mype ?? 0,

            // Nuevo campo
            'descuentos_conce_devolu' => $request->descuentos_conce_devolu ?? 0,
            'descuento_concedido_tributo' => $request->descuento_concedido_tributo ?? 0,
            'ventas_nogravadas_sinexpor' => $request->ventas_nogravadas_sinexpor ?? 0,
            'comprasnetas_gravadas_nogravadas' => $request->comprasnetas_gravadas_nogravadas ?? 0,
            'comprasnetas_nogravadas_exclusivas' => $request->comprasnetas_nogravadas_exclusivas ?? 0,
            'comprasinternas_nogravadas' => $request->comprasinternas_nogravadas ?? 0,
        ]);

        /*|--------------------------------------------------------------------------
        | Guardar archivo PDT (desde Temporal)
        |--------------------------------------------------------------------------*/
        if ($request->has('temp_pdt_path') && !empty($request->temp_pdt_path)) {
            $publicTempPath = $request->temp_pdt_path;

            $tempFullPath = public_path($publicTempPath);

            if (File::exists($tempFullPath)) {
                $dir = public_path("determinaciones/{$determinacion->id_determinacion_mensual}");
                if (!File::exists($dir)) {
                    File::makeDirectory($dir, 0775, true);
                }

                $empresa = EmpresasClientes::findOrFail($determinacion->id_empresas_clientes);
                $razonSocial = Str::upper(Str::slug($empresa->razon_social, '_'));
                $ruc = $empresa->ruc;
                $periodo = Carbon::parse($determinacion->fecha)->format('Ym');

                $filename = "{$razonSocial}_{$ruc}_{$periodo}_PDT621.pdf";

                // Mover archivo
                if (File::move($tempFullPath, $dir . '/' . $filename)) {
                    $determinacion->archivo_pdt = "determinaciones/{$determinacion->id_determinacion_mensual}/{$filename}";
                    $determinacion->save();
                }
            }
        }

        /*|--------------------------------------------------------------------------
        | Fallback: Guardar archivo PDT (subida input normal)
        |--------------------------------------------------------------------------*/
        if ($request->hasFile('archivo_pdt')) {
            $dir = public_path("determinaciones/{$determinacion->id_determinacion_mensual}");
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0775, true);
            }

            $empresa = EmpresasClientes::findOrFail($determinacion->id_empresas_clientes);
            $razonSocial = Str::upper(Str::slug($empresa->razon_social, '_'));
            $ruc = $empresa->ruc;
            $periodo = Carbon::parse($determinacion->fecha)->format('Ym');

            $filename = "{$razonSocial}_{$ruc}_{$periodo}_PDT.pdf";
            $request->file('archivo_pdt')->move($dir, $filename);

            $determinacion->archivo_pdt = "determinaciones/{$determinacion->id_determinacion_mensual}/{$filename}";
            $determinacion->save();
        }

        return redirect()->route('determinacion_mensual.index', ['ano' => now()->year])
            ->with('success', 'Determinación mensual guardada correctamente.');
    }
    public function edit($id)
    {
        // Obtener la determinación mensual por su ID
        $determinacion = DeterminacionMensual::findOrFail($id);

        $fecha = Carbon::parse($determinacion->fecha);

        // Obtener la empresa asociada a la determinación
        $empresa = EmpresasClientes::findOrFail($determinacion->id_empresas_clientes);

        // Pasar los datos a la vista de edición
        return view('determinacion_mensual.edit', compact('determinacion', 'empresa', 'fecha'));
    }

    public function update(Request $request, $id)
    {
        // Validar los datos del formulario
        $request->validate([
            'id_empresas_clientes' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'id_usuario' => 'required|exists:users,id',
            'fecha' => 'required|date',
            'base_compras_18' => 'nullable|numeric',
            'base_compras_10' => 'nullable|numeric',
            'porcentaje_compras' => 'nullable|numeric',
            'base_compras_ng' => 'nullable|numeric',
            'igv_compras_18' => 'nullable|numeric',
            'igv_compras_10' => 'nullable|numeric',
            'total_compras' => 'nullable|numeric',
            'base_ventas' => 'nullable|numeric',
            'igv_ventas' => 'nullable|numeric',
            'base_ventas_10' => 'nullable|numeric',
            'descuento_concedido_tributo' => 'nullable|numeric',
            'igv_ventas_10' => 'nullable|numeric',
            'total_ventas' => 'nullable|numeric',
            'igv_a_pagar' => 'nullable|numeric',
            'saldo_a_favor_periodo_anterior' => 'nullable|numeric',
            'total_igv_a_pagar' => 'nullable|numeric',
            'saldo_a_favor_renta_periodo_anterior' => 'nullable|numeric',
            'renta_pagar' => 'nullable|numeric',
            'total_renta_a_pagar' => 'nullable|numeric',
            'total_a_pagar_tributos' => 'nullable|numeric',
            'retenciones_del_periodo' => 'nullable|numeric',
            'retenciones_periodo_anterior' => 'nullable|numeric',
            'total_retenciones' => 'nullable|numeric',
            'percepciones_del_periodo' => 'nullable|numeric',
            'percepcion_periodo_anterior' => 'nullable|numeric',
            'percepciones_no_aplicadas' => 'nullable|numeric',
            'total_percepciones' => 'nullable|numeric',
            'porcentaje_mype' => 'nullable|numeric',
            'ingresos_netos' => 'nullable|numeric',
            'descuentos_conce_devolu' => 'nullable|numeric',
            'ventas_nogravadas_sinexpor' => 'nullable|numeric',
            'comprasnetas_gravadas_nogravadas' => 'nullable|numeric',
            'comprasnetas_nogravadas_exclusivas' => 'nullable|numeric',
            'comprasinternas_nogravadas' => 'nullable|numeric',
            'archivo_pdt_rectificado' => 'nullable|extensions:pdf|max:10240',
            'archivo_pdt_constancia_rectificado' => 'nullable|extensions:pdf|max:10240',
        ]);

        // Buscar la determinación mensual por su ID
        $determinacion = DeterminacionMensual::findOrFail($id);

        // Guardar los valores originales antes de la actualización
        $originalIgvTotal = $determinacion->total_igv_a_pagar;
        $originalRentaTotal = $determinacion->total_renta_a_pagar;

        // Actualizar los datos
        $determinacion->update([
            'id_empresas_clientes' => $request->id_empresas_clientes,
            'id_usuario' => $request->id_usuario,
            'fecha' => $request->fecha,

            // Compras
            'base_compras_18' => $request->base_compras_18 ?? 0,
            'base_compras_10' => $request->base_compras_10 ?? 0,
            'porcentaje_compras' => $request->porcentaje_compras ?? 0,
            'base_compras_ng' => $request->base_compras_ng ?? 0,
            'igv_compras_18' => $request->igv_compras_18 ?? 0,
            'igv_compras_10' => $request->igv_compras_10 ?? 0,
            'total_compras' => $request->total_compras ?? 0,

            // Ventas
            'base_ventas' => $request->base_ventas ?? 0,
            'igv_ventas' => $request->igv_ventas ?? 0,
            'base_ventas_10' => $request->base_ventas_10 ?? 0,
            'descuento_concedido_tributo' => $request->descuento_concedido_tributo ?? 0,
            'igv_ventas_10' => $request->igv_ventas_10 ?? 0,
            'total_ventas' => $request->total_ventas ?? 0,

            // IGV
            'igv_a_pagar' => $request->igv_a_pagar ?? 0,
            'saldo_a_favor_periodo_anterior' => $request->saldo_a_favor_periodo_anterior ?? 0,
            'total_igv_a_pagar' => $request->total_igv_a_pagar ?? 0,

            // Renta
            'ingresos_netos' => $request->ingresos_netos ?? 0,
            'renta_pagar' => $request->renta_pagar ?? 0,
            'saldo_a_favor_renta_periodo_anterior' => $request->saldo_a_favor_renta_periodo_anterior ?? 0,
            'total_renta_a_pagar' => $request->total_renta_a_pagar ?? 0,

            // Tributos
            'total_a_pagar_tributos' => $request->total_a_pagar_tributos ?? 0,

            // Retenciones y Percepciones
            'retenciones_del_periodo' => $request->retenciones_del_periodo ?? 0,
            'retenciones_periodo_anterior' => $request->retenciones_periodo_anterior ?? 0,
            'total_retenciones' => $request->total_retenciones ?? 0,
            'percepciones_del_periodo' => $request->percepciones_del_periodo ?? 0,
            'percepcion_periodo_anterior' => $request->percepcion_periodo_anterior ?? 0,
            'percepciones_no_aplicadas' => $request->percepciones_no_aplicadas ?? 0,
            'total_percepciones' => $request->total_percepciones ?? 0,

            // Porcentaje MYPE
            'porcentaje_mype' => $request->porcentaje_mype ?? 0,
            // Coeficiente
            'coeficiente_renta' => [
                'ref' => '380',
                'type' => 'coeficiente'
            ],

            // Nuevo campo
            'descuentos_conce_devolu' => $request->descuentos_conce_devolu ?? 0,
            'ventas_nogravadas_sinexpor' => $request->ventas_nogravadas_sinexpor ?? 0,
            'comprasnetas_gravadas_nogravadas' => $request->comprasnetas_gravadas_nogravadas ?? 0,
            'comprasnetas_nogravadas_exclusivas' => $request->comprasnetas_nogravadas_exclusivas ?? 0,
            'comprasinternas_nogravadas' => $request->comprasinternas_nogravadas ?? 0,
        ]);

        // Obtener el nuevo total IGV a pagar
        $nuevoIgvTotal = $determinacion->total_igv_a_pagar;
        $nuevoRentaTotal = $determinacion->total_renta_a_pagar;

        // Si hay cambios en los totales, actualizar los meses siguientes
        if ($originalIgvTotal != $nuevoIgvTotal || $originalRentaTotal != $nuevoRentaTotal) {
            $this->actualizarMesesSiguientes($determinacion);
        }

        /*|--------------------------------------------------------------------------
        | Guardar archivo PDT (desde Temporal)
        |--------------------------------------------------------------------------*/
        $pdtFields = ['archivo_pdt', 'archivo_pdt_rectificado', 'pdt_rectificatorio_2', 'pdt_rectificatorio_3', 'pdt_rectificatorio_4'];

        $pdtFileSource = null;
        $isTemporal = false;

        if ($request->has('temp_pdt_path') && !empty($request->temp_pdt_path)) {
            $pdtFileSource = $request->temp_pdt_path;
            $isTemporal = true;
        } elseif ($request->hasFile('archivo_pdt')) {
            $pdtFileSource = $request->file('archivo_pdt');
            $isTemporal = false;
        }

        if ($pdtFileSource) {
            $dir = public_path("determinaciones/{$determinacion->id_determinacion_mensual}");
            if (!File::exists($dir))
                File::makeDirectory($dir, 0775, true);

            $empresa = EmpresasClientes::findOrFail($determinacion->id_empresas_clientes);
            $razonSocial = Str::upper(Str::slug($empresa->razon_social, '_'));
            $ruc = $empresa->ruc;
            $periodo = Carbon::parse($determinacion->fecha)->format('Ym');

            // Encontrar el campo destino
            $targetField = null;
            foreach ($pdtFields as $f) {
                if (!$determinacion->$f) {
                    $targetField = $f;
                    break;
                }
            }
            if (!$targetField)
                $targetField = end($pdtFields);

            // Determinar nombre del archivo según el campo destino
            $index = array_search($targetField, $pdtFields);
            $suffix = "";
            if ($index == 1)
                $suffix = "_RECTIFICADO";
            elseif ($index > 1)
                $suffix = "_RECTIFICADO_" . ($index);

            $filename = "{$razonSocial}_{$ruc}_{$periodo}_PDT621{$suffix}.pdf";

            if ($isTemporal) {
                $tempPath = public_path($pdtFileSource);
                if (File::exists($tempPath) && File::move($tempPath, $dir . '/' . $filename)) {
                    $determinacion->$targetField = "determinaciones/{$determinacion->id_determinacion_mensual}/{$filename}";
                }
            } else {
                if ($targetField == end($pdtFields))
                    $this->borrarArchivoAnterior($determinacion->$targetField);
                $pdtFileSource->move($dir, $filename);
                $determinacion->$targetField = "determinaciones/{$determinacion->id_determinacion_mensual}/{$filename}";
            }
            $determinacion->save();
        }

        return redirect()->route('determinacion_mensual.show', $determinacion->id_determinacion_mensual)
            ->with('success', 'Determinación mensual actualizada correctamente.');
    }

    protected function actualizarMesesSiguientes($determinacionActual)
    {
        // Obtener todas las determinaciones de la misma empresa después de la fecha actual
        $determinacionesSiguientes = DeterminacionMensual::where('id_empresas_clientes', $determinacionActual->id_empresas_clientes)
            ->where('fecha', '>', $determinacionActual->fecha)
            ->orderBy('fecha', 'asc')
            ->get();

        // Variables para llevar el saldo a favor que se propagará
        $saldoIgvAFavor = $determinacionActual->total_igv_a_pagar < 0 ? $determinacionActual->total_igv_a_pagar : 0;
        $saldoRentaAFavor = $determinacionActual->total_renta_a_pagar < 0 ? $determinacionActual->total_renta_a_pagar : 0;

        foreach ($determinacionesSiguientes as $determinacion) {
            // Actualizar el saldo a favor del periodo anterior (IGV)
            $determinacion->saldo_a_favor_periodo_anterior = $saldoIgvAFavor;

            // Recalcular el total IGV a pagar
            $determinacion->total_igv_a_pagar = $determinacion->igv_a_pagar + $saldoIgvAFavor;

            // Actualizar el saldo a favor de renta del periodo anterior
            $determinacion->saldo_a_favor_renta_periodo_anterior = $saldoRentaAFavor;

            // Recalcular el total renta a pagar
            $determinacion->total_renta_a_pagar = $determinacion->renta_pagar + $saldoRentaAFavor;

            // Recalcular el total a pagar tributos
            $totalIgvPositivo = $determinacion->total_igv_a_pagar > 0 ? $determinacion->total_igv_a_pagar : 0;
            $totalRentaPositivo = $determinacion->total_renta_a_pagar > 0 ? $determinacion->total_renta_a_pagar : 0;
            $determinacion->total_a_pagar_tributos = $totalIgvPositivo + $totalRentaPositivo;

            // Guardar los cambios
            $determinacion->save();

            // Actualizar los saldos a favor para la próxima iteración
            $saldoIgvAFavor = $determinacion->total_igv_a_pagar < 0 ? $determinacion->total_igv_a_pagar : 0;
            $saldoRentaAFavor = $determinacion->total_renta_a_pagar < 0 ? $determinacion->total_renta_a_pagar : 0;
        }
    }

    public function destroyByEmpresaAndYear(Request $request, $id_empresa, $ano)
    {
        // Buscar y eliminar las determinaciones de la empresa para el año seleccionado
        DeterminacionMensual::where('id_empresas_clientes', $id_empresa)
            ->whereYear('fecha', $ano)
            ->delete();

        // Redireccionar con un mensaje de éxito
        return redirect()->route('determinacion_mensual.index', ['ano' => $ano])
            ->with('success', 'Todas las determinaciones de la empresa para el año ' . $ano . ' han sido eliminadas.');
    }

    public function detalles($id_empresa, $ano)
    {
        // Obtener la empresa
        $empresa = EmpresasClientes::findOrFail($id_empresa);

        // Obtener las determinaciones mensuales de la empresa para el año seleccionado
        $determinaciones = DeterminacionMensual::where('id_empresas_clientes', $id_empresa)
            ->whereYear('fecha', $ano)
            ->get();

        // Crear un array para almacenar los datos de la tabla
        $datosTabla = [];

        // Inicializar todos los meses como "Vacío"
        for ($mes = 1; $mes <= 12; $mes++) {
            $datosTabla[$mes] = 'Vacío';
        }

        // Llenar los datos de los meses que tienen determinaciones
        foreach ($determinaciones as $determinacion) {
            $fecha = Carbon::parse($determinacion->fecha);
            $mes = $fecha->month; // Obtener el mes de la determinación
            $datosTabla[$mes] = $determinacion;
        }

        // Pasar los datos a la vista
        return view('determinacion_mensual.detalles', [
            'empresa' => $empresa,
            'ano' => $ano,
            'datosTabla' => $datosTabla,
        ]);
    }

    public function subirArchivos(Request $request, $id)
    {
        $request->validate([
            'archivo_pdt' => 'nullable|extensions:pdf|max:10240',
            'archivo_pdt_constancia' => 'nullable|extensions:pdf|max:10240',
            'archivo_pdt_rectificado' => 'nullable|extensions:pdf|max:10240',
            'archivo_pdt_constancia_rectificado' => 'nullable|extensions:pdf|max:10240',
            'pdt_rectificatorio_2' => 'nullable|extensions:pdf|max:10240',
            'pdt_rectificatorio_3' => 'nullable|extensions:pdf|max:10240',
            'pdt_rectificatorio_4' => 'nullable|extensions:pdf|max:10240',
            'pdt_constancia_rectificatorio_2' => 'nullable|extensions:pdf|max:10240',
            'pdt_constancia_rectificatorio_3' => 'nullable|extensions:pdf|max:10240',
            'pdt_constancia_rectificatorio_4' => 'nullable|extensions:pdf|max:10240',
        ]);
        $determinacion = DeterminacionMensual::findOrFail($id);

        /*|--------------------------------------------------------------------------
        | Carpeta destino
        |--------------------------------------------------------------------------*/
        $dir = public_path("determinaciones/{$determinacion->id_determinacion_mensual}");
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0775, true);
        }

        /*|--------------------------------------------------------------------------
        | Datos comunes
        |--------------------------------------------------------------------------*/
        $empresa = EmpresasClientes::findOrFail($determinacion->id_empresas_clientes);
        $razonSocial = Str::upper(Str::slug($empresa->razon_social, '_'));
        $ruc = $empresa->ruc;
        $periodo = Carbon::parse($determinacion->fecha)->format('Ym'); // 202501

        /*|--------------------------------------------------------------------------
        | PROCESAMIENTO DE ARCHIVOS
        |--------------------------------------------------------------------------*/

        // 1. Originales (PDT y Constancia)
        $originales = [
            'archivo_pdt' => 'PDT621',
            'archivo_pdt_constancia' => 'CONSTANCIA_PDT621'
        ];
        foreach ($originales as $field => $prefix) {
            if ($request->hasFile($field)) {
                $this->borrarArchivoAnterior($determinacion->$field);
                $filename = "{$razonSocial}_{$ruc}_{$periodo}_{$prefix}.pdf";
                $request->file($field)->move($dir, $filename);
                $determinacion->$field = "determinaciones/{$determinacion->id_determinacion_mensual}/{$filename}";

                // --- NUEVO: Sincronizar si es constancia ---
                if ($field == 'archivo_pdt_constancia') {
                    $this->syncDeudasFromConstancia($determinacion, public_path($determinacion->$field), 'Original');
                }
            }
        }

        // 2. Rectificados con Rotación Automática
        $rotaciones = [
            'pdt' => [
                'input' => 'archivo_pdt_rectificado',
                'prefix' => 'PDT621_RECTIFICADO',
                'fields' => ['archivo_pdt_rectificado', 'pdt_rectificatorio_2', 'pdt_rectificatorio_3', 'pdt_rectificatorio_4']
            ],
            'constancia' => [
                'input' => 'archivo_pdt_constancia_rectificado',
                'prefix' => 'CONSTANCIA_PDT621_RECTIFICADO',
                'fields' => ['archivo_pdt_constancia_rectificado', 'pdt_constancia_rectificatorio_2', 'pdt_constancia_rectificatorio_3', 'pdt_constancia_rectificatorio_4']
            ]
        ];

        foreach ($rotaciones as $type => $config) {
            if ($request->hasFile($config['input'])) {
                $targetField = null;
                foreach ($config['fields'] as $f) {
                    if (!$determinacion->$f) {
                        $targetField = $f;
                        break;
                    }
                }
                if (!$targetField)
                    $targetField = end($config['fields']);

                // Determinar sufijo para el nombre
                $index = array_search($targetField, $config['fields']);
                $suffix = ($index == 0) ? "" : "_" . ($index + 1);

                // Reajuste manual de sufijos para coincidir con nombres anteriores
                $finalSuffix = "";
                if ($index == 1)
                    $finalSuffix = "_2";
                elseif ($index == 2)
                    $finalSuffix = "_3";
                elseif ($index == 3)
                    $finalSuffix = "_4";

                $filename = "{$razonSocial}_{$ruc}_{$periodo}_{$config['prefix']}{$finalSuffix}.pdf";

                if ($targetField == end($config['fields'])) {
                    $this->borrarArchivoAnterior($determinacion->$targetField);
                }

                $request->file($config['input'])->move($dir, $filename);
                $determinacion->$targetField = "determinaciones/{$determinacion->id_determinacion_mensual}/{$filename}";

                // --- NUEVO: Sincronizar si es constancia ---
                if ($type == 'constancia') {
                    $label = ($index == 0) ? "Rectificado 1" : "Rectificado " . ($index + 1);
                    $this->syncDeudasFromConstancia($determinacion, public_path($determinacion->$targetField), $label);
                }
            }
        }

        // 3. Manuales (Específicos 2, 3, 4)
        for ($i = 2; $i <= 4; $i++) {
            foreach (['pdt_rectificatorio_', 'pdt_constancia_rectificatorio_'] as $base) {
                $f = $base . $i;
                if ($request->hasFile($f)) {
                    $this->borrarArchivoAnterior($determinacion->$f);
                    $prefix = str_contains($base, 'constancia') ? 'CONSTANCIA_PDT621_RECTIFICADO' : 'PDT621_RECTIFICADO';
                    $filename = "{$razonSocial}_{$ruc}_{$periodo}_{$prefix}_{$i}.pdf";
                    $request->file($f)->move($dir, $filename);
                    $determinacion->$f = "determinaciones/{$determinacion->id_determinacion_mensual}/{$filename}";

                    // --- NUEVO: Sincronizar si es constancia manual ---
                    if (str_contains($base, 'constancia')) {
                        $this->syncDeudasFromConstancia($determinacion, public_path($determinacion->$f), "Rectificado $i");
                    }
                }
            }
        }

        $determinacion->save();
        return back()->with('success', 'Archivos PDF actualizados correctamente.');
    }

    /**INICIO DE EXTRAER DATOS DE LOS PDFS*/
    private function normalizePdfText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = str_replace("\t", " ", $text);
        $lines = array_map(function ($line) {
            $line = preg_replace('/[ ]+/', ' ', $line);
            return trim($line);
        }, explode("\n", $text));
        $text = preg_replace("/\n{3,}/", "\n\n", trim(implode("\n", $lines)));
        return $text;
    }
    public function extractPdfData(Request $request)
    {
        try {
            // Validar PDF
            $request->validate([
                'pdf_file' => 'required|file|extensions:pdf|max:10240',
            ]);

            // Guardar temporalmente
            $pdfFile = $request->file('pdf_file');

            // Generate a unique filename
            $tempFilename = uniqid('temp_pdf_') . '.pdf';
            $tempDir = public_path('determinaciones/temp');

            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0775, true);
            }

            $pdfFile->move($tempDir, $tempFilename);
            $tempPath = $tempDir . '/' . $tempFilename;
            $publicTempPath = 'determinaciones/temp/' . $tempFilename;

            // Usar Smalot\PdfParser para extraer el texto
            $parser = new Parser();
            $pdf = $parser->parseFile($tempPath);

            // USAR RECONSTRUCCIÓN POR COORDENADAS (LAYOUT)
            $textStandard = $this->getReconstructedText($pdf, false, false); // Lógica anterior (espacios forzados)
            $textRefined = $this->getReconstructedText($pdf, false, true);   // Nueva lógica (unión por proximidad)

            // Mapeo de campos
            $fieldMapping = [
                // COMPRAS
                'base_compras_18' => ['ref' => '107', 'next' => '108', 'type' => 'base_igv'],
                'base_compras_10' => ['ref' => '156', 'next' => '157', 'type' => 'base_igv'],
                'comprasnetas_gravadas_nogravadas' => ['ref' => '110', 'next' => '111'],
                'comprasnetas_nogravadas_exclusivas' => ['ref' => '113', 'type' => 'single_value'],
                'comprasinternas_nogravadas' => ['ref' => '120', 'next' => 'Compras importadas no gravadas'],

                // VENTAS
                'base_ventas' => ['ref' => '100', 'next' => '101', 'type' => 'base_igv'],
                'base_ventas_10' => ['ref' => '154', 'next' => '155', 'type' => 'base_igv'],
                'descuento_concedido_tributo' => ['ref' => '103', 'type' => 'single_value'],
                'descuentos_conce_devolu' => ['ref' => '102', 'next' => '103', 'type' => 'base_igv'],
                'ventas_nogravadas_sinexpor' => ['ref' => '105', 'type' => 'single_value'],

                // OTROS
                'saldo_a_favor_periodo_anterior' => ['ref' => '145', 'next' => '351', 'type' => 'single_value'],
                'porcentaje_mype' => ['ref' => '315', 'type' => 'percentage'],
                'retenciones_del_periodo' => ['ref' => '179', 'type' => 'single_value'],
                'retenciones_periodo_anterior' => ['ref' => '176', 'type' => 'single_value'],
                'percepciones_del_periodo' => ['ref' => '171', 'type' => 'single_value'],
                'percepcion_periodo_anterior' => ['ref' => '168', 'type' => 'single_value'],
                'percepciones_no_aplicadas' => ['ref' => '164', 'type' => 'single_value'],
            ];
            $extractedData = [];

            foreach ($fieldMapping as $field => $config) {
                // PRIMER INTENTO: Lógica estándar (tu lógica anterior)
                $value = $this->extractValueFromLayoutText($textStandard, $config);

                // SEGUNDO INTENTO: Fallback a lógica refinada (unión de fragmentos "1 0 7")
                if ($value === null) {
                    $value = $this->extractValueFromLayoutText($textRefined, $config);
                }

                if ($value !== null) {
                    if (is_array($value)) {
                        $extractedData[$field] = $value['valor'];
                        $extractedData['mype_tipo'] = $value['tipo'];
                    } else {
                        $extractedData[$field] = $value;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $extractedData,
                'temp_path' => $publicTempPath, // Retornar ruta temporal
                'message' => 'Datos extraídos correctamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 400);
        }
    }
    private function getReconstructedText(\Smalot\PdfParser\Document $pdf, bool $includePageMarkers = false, bool $proximityJoining = false): string
    {
        $fullText = "";
        $charWidth = 6.0;
        foreach ($pdf->getPages() as $index => $page) {
            if ($includePageMarkers) {
                $pageNum = $index + 1;
                $fullText .= "--- PÁGINA {$pageNum} ---\n\n";
            }
            $textParts = $page->getDataTm();
            $lines = [];
            foreach ($textParts as $part) {
                $text = $part[1];
                $tm = $part[0];
                $x = (float) ($tm[4] ?? 0);
                $y = (float) ($tm[5] ?? 0);
                $yKey = (int) round($y / 2) * 2;
                if (!isset($lines[$yKey])) {
                    $lines[$yKey] = [];
                }
                $lines[$yKey][] = ['x' => $x, 'text' => $text, 'width' => mb_strlen($text) * $charWidth];
            }
            krsort($lines);
            foreach ($lines as $y => $parts) {
                usort($parts, function ($a, $b) {
                    return $a['x'] <=> $b['x'];
                });
                $lineContent = "";
                if ($proximityJoining) {
                    // NUEVA LÓGICA: Unión por proximidad (para casos como digitos separados)
                    $lastX = null;
                    $lastWidth = 0;
                    foreach ($parts as $part) {
                        $targetCharPos = (int) ($part['x'] / $charWidth);
                        $currentLen = mb_strlen($lineContent);
                        if ($lastX !== null) {
                            $gap = $part['x'] - ($lastX + $lastWidth);
                            if ($gap > 2.0) {
                                if ($targetCharPos > $currentLen) {
                                    $lineContent .= str_repeat(" ", $targetCharPos - $currentLen);
                                } else {
                                    $lineContent .= " ";
                                }
                            }
                        } else {
                            if ($targetCharPos > 0) {
                                $lineContent .= str_repeat(" ", $targetCharPos);
                            }
                        }

                        $lineContent .= $part['text'];
                        $lastX = $part['x'];
                        $lastWidth = mb_strlen($part['text']) * $charWidth;
                    }
                } else {
                    // LÓGICA ANTERIOR: Espacios forzados (la del usuario)
                    foreach ($parts as $part) {
                        $targetCharPos = (int) ($part['x'] / $charWidth);
                        $currentLen = mb_strlen($lineContent);
                        if ($targetCharPos > $currentLen) {
                            $lineContent .= str_repeat(" ", $targetCharPos - $currentLen);
                        } else if ($currentLen > 0) {
                            $lineContent .= " ";
                        }
                        $lineContent .= $part['text'];
                    }
                }
                $fullText .= $lineContent . "\n";
            }
            $fullText .= "\n";
        }
        return $this->normalizePdfText($fullText);
    }

    /**Extrae un valor numérico del texto con layout preservado*/
    private function extractValueFromLayoutText(string $text, array $config)
    {
        $text = str_replace("\n", ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $ref = preg_quote($config['ref'], '/');
        $next = isset($config['next']) ? preg_quote($config['next'], '/') : null;
        $type = $config['type'] ?? 'single_value';
        if ($type === 'no_value') {
            return null;
        }
        if ($type === 'base_igv' && $next) {
            if (
                preg_match(
                    '/\b' . $ref . '\b\s+([0-9,]+\.\d{2})\s+\b' . $next . '\b/',
                    $text,
                    $m
                )
            ) {
                return number_format((float) str_replace(',', '', $m[1]), 2, '.', '');
            }
        }
        if ($type === 'single_value') {
            if (
                preg_match(
                    '/\b' . $ref . '\b\s+([0-9,]+\.\d{2})/',
                    $text,
                    $m
                )
            ) {
                return number_format((float) str_replace(',', '', $m[1]), 2, '.', '');
            }
        }
        if ($type === 'percentage') {
            // COEFICIENTE (380)
            if (preg_match('/\b380\b\s+([0-9]+(?:\.[0-9]+)?)/', $text, $m)) {
                $coeficiente = (float) $m[1];
                if ($coeficiente > 0) {
                    return [
                        'tipo' => 'coeficiente',
                        'valor' => number_format($coeficiente, 6, '.', '')
                    ];
                }
            }
            // PORCENTAJE MYPE (315)
            if (preg_match('/\b315\b\s+([0-9]+(?:\.[0-9]+)?)/', $text, $m)) {
                return [
                    'tipo' => 'mype',
                    'valor' => number_format((float) $m[1], 4, '.', '')
                ];
            }
        }
        return null;
    }

    /**Parsea valor numérico*/
    private function parseNumericValue($text)
    {
        $text = trim($text);
        $pattern = '/\b([0-9]{1,3}(?:[.,\s][0-9]{3})*(?:[.,][0-9]{1,2})?)\b/';
        if (preg_match($pattern, $text, $matches)) {
            $raw = str_replace(' ', '', $matches[1]);
            if (!preg_match('/[.,]/', $raw))
                return floatval($raw);
            $clean = str_replace([',', '.'], '', $raw);
            if (preg_match('/[.,](\d{1,2})$/', $raw, $d)) {
                $clean = substr($clean, 0, -strlen($d[1])) . '.' . $d[1];
            }
            return floatval($clean) > 0 ? floatval($clean) : null;
        }
        return null;
    }

    private function borrarArchivoAnterior(?string $ruta)
    {
        if ($ruta && file_exists(public_path($ruta))) {
            @unlink(public_path($ruta));
        }
    }

    public function previewPdfText(Request $request)
    {
        try {
            $pdfFile = $request->file('pdf_file');
            if (!$pdfFile && $request->input('pdf_path')) {
                $pdfPath = public_path($request->input('pdf_path'));
            } elseif ($pdfFile) {
                $pdfPath = $pdfFile->getRealPath();
            } else {
                throw new \Exception("No se proporcionó un archivo o ruta válida.");
            }

            $parser = new Parser();
            $pdf = $parser->parseFile($pdfPath);

            // Probar ambas lógicas de reconstrucción
            $textStandard = $this->getReconstructedText($pdf, false, false);
            $textRefined = $this->getReconstructedText($pdf, false, true);

            // Limpieza de espacios entre dígitos para el refinado
            $textRefinedClean = preg_replace_callback('/(?<!\d)(\d)\s+(\d)\s+(\d)\s+(\d)(?!\d)/', function ($m) {
                return $m[1] . $m[2] . $m[3] . $m[4];
            }, $textRefined);

            return response()->json([
                'success' => true,
                'text_standard' => $textStandard,
                'text_refined' => $textRefinedClean,
                'message' => 'Texto extraído correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /* Sincroniza las deudas de la contabilidad extrayendo datos de la constancia PDF */
    private function syncDeudasFromConstancia($determinacion, $pdfPath, $label)
    {
        try {
            $pdfPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $pdfPath);

            if (!File::exists($pdfPath)) {
                \Illuminate\Support\Facades\Log::warning("syncDeudasFromConstancia: archivo no encontrado → $pdfPath");
                return;
            }

            $datosExtraidos = $this->extractConstanciaData($pdfPath);

            if (empty($datosExtraidos)) {
                \Illuminate\Support\Facades\Log::info("syncDeudasFromConstancia: sin tributos para determinación #{$determinacion->id_determinacion_mensual}");
                return;
            }

            $empresa = EmpresasClientes::find($determinacion->id_empresas_clientes);
            if (!$empresa)
                return;

            // ---------- Calcular fecha de vencimiento ----------
            $fechaDet = Carbon::parse($determinacion->fecha);
            $mesDeclaracion = $fechaDet->format('Y-m');

            $calPDT = \App\Models\CalendarioVencimiento::where('tipo', 'PDT')
                ->where('mes', $mesDeclaracion)
                ->where('estado', 1)
                ->first();

            $fechas = $calPDT ? json_decode($calPDT->fechas, true) : [];
            $ud = substr($empresa->ruc, -1);
            $dia = $fechas[$ud] ?? 15;

            $fechaVencimiento = Carbon::createFromDate($fechaDet->year, $fechaDet->month, 1)
                ->addMonth()
                ->setDay((int) $dia)
                ->format('Y-m-d');

            // ---------- Insertar solo deudas con monto > 0 ----------
            foreach ($datosExtraidos as $data) {

                // Ignorar tributos sin deuda
                if ($data['monto'] <= 0)
                    continue;

                $tributo = \App\Models\Tributo::where('codigo', $data['codigo'])->first();
                if (!$tributo) {
                    \Illuminate\Support\Facades\Log::warning("syncDeudasFromConstancia: tributo {$data['codigo']} no encontrado.");
                    continue;
                }

                $esRectificado = stripos($label, 'rectif') !== false;
                $sufijo = $esRectificado ? ' (Rectificado)' : '';
                $observacion = "Extraído de Constancia - {$label}{$sufijo}";

                \App\Models\Deuda::create([
                    'id_empresa_cliente' => $empresa->id_empresas_clientes,
                    'id_usuario' => auth()->id() ?? $determinacion->id_usuario,
                    'id_tributo' => $tributo->id_tributo,
                    'monto_deuda' => $data['monto'],
                    'estado' => 0,
                    'pago_puntual' => 0,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'interes_pendiente' => 0,
                    'interes_calculado' => 0,
                    'observaciones' => $observacion,
                ]);
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('syncDeudasFromConstancia: ' . $e->getMessage());
        }
    }

    /*Extrae tributo y total deuda de un PDF de Constancia de Presentación*/
    private function extractConstanciaData($pdfPath)
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($pdfPath);
            $text = $this->getReconstructedText($pdf, false, true);

            // Reunir dígitos separados por espacios (artefacto del parser)
            $text = preg_replace_callback(
                '/(?<!\d)(\d)\s+(\d)\s+(\d)\s+(\d)(?!\d)/',
                fn($m) => $m[1] . $m[2] . $m[3] . $m[4],
                $text
            );

            $results = [];

            $pattern = '/\b([13]\d{3})\b\s*(?:-\s+)?' // código tributo
                . '[A-ZÁÉÍÓÚÑ][^\n]*?'           // descripción (no cruza líneas)
                . 'S\/\.?\s*([\d,]+(?:\.\d{2})?)' // Total Deuda  →  S/ o S/.  + monto
                . '\s+S\/\.?\s*[\d,]+/';           // Monto Pago  (descartado)

            if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    $monto = (float) str_replace(',', '', $m[2]);

                    // Evitar duplicados por el mismo código (quedarse con el mayor monto)
                    $existing = array_search($m[1], array_column($results, 'codigo'));
                    if ($existing !== false) {
                        if ($monto > $results[$existing]['monto']) {
                            $results[$existing]['monto'] = $monto;
                        }
                    } else {
                        $results[] = [
                            'codigo' => $m[1],
                            'monto' => $monto,
                        ];
                    }
                }
            }

            // Fallback: buscar sólo el bloque "Detalle de Tributos" si el patrón principal falla
            if (empty($results)) {
                // Extraer sección entre "Detalle de Tributos" y "Total a pagar"
                if (preg_match('/Detalle de Tributos(.+?)Total a [Pp]agar/s', $text, $block)) {
                    $block = $block[1];
                    if (preg_match_all('/\b([13]\d{3})\b.*?S\/\.?\s*([\d,]+(?:\.\d{2})?)/s', $block, $m2, PREG_SET_ORDER)) {
                        foreach ($m2 as $entry) {
                            $results[] = [
                                'codigo' => $entry[1],
                                'monto' => (float) str_replace(',', '', $entry[2]),
                            ];
                        }
                    }
                }
            }

            return $results;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('extractConstanciaData: ' . $e->getMessage());
            return [];
        }
    }

    /**FIN DE EXTRAER DATOS DE LOS PDFS*/

    public function descargarPdf($id)
    {
        $determinacion = DeterminacionMensual::with('empresa')
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.determinacionmes', [
            'determinacion' => $determinacion,
            'empresa' => $determinacion->empresa,
            'fecha' => Carbon::parse($determinacion->fecha),
        ]);

        return $pdf->stream(
            'DETERMINACION_MENSUAL_' .
            strtoupper($determinacion->empresa->razon_social) . '_' .
            Carbon::parse($determinacion->fecha)->format('m_Y') .
            '.pdf'
        );
    }

    public function destroyById(Request $request, $id_determinacion_mensual)
    {
        $determinacion = DeterminacionMensual::findOrFail($id_determinacion_mensual);
        $determinacion->delete();
        return redirect()->route('determinacion_mensual.index')
            ->with('success', 'Determinación con ID ' . $id_determinacion_mensual . ' ha sido eliminada.');
    }

}
