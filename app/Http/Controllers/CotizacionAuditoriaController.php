<?php

namespace App\Http\Controllers;

use App\Exports\CotizacionExport;
use App\Models\CotizacionPlantilla;
use App\Models\PlantillaReceptor;
use App\Models\ClienteAuditoria;
use App\Models\EmpresasClientes;
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CotizacionAuditoriaController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    //  GENERACIÓN DE COTIZACIÓN
    // ══════════════════════════════════════════════════════════════

    /**
     * Descarga la cotización Excel para una factura dada.
     * Ruta: GET /cotizaciones/{factura}/descargar
     */
    public function descargar(Factura $factura)
    {
        $empresaEmisora = EmpresasClientes::where('ruc', $factura->ruc_emisor)->first();

        if (!$empresaEmisora) {
            return back()->with('error', 'No se encontró la empresa emisora.');
        }

        $plantilla = CotizacionPlantilla::where('id_empresa_cliente', $empresaEmisora->id_empresas_clientes)->first();

        if (!$plantilla) {
            return back()->with('error', 'Esta empresa no tiene una plantilla de cotización configurada.');
        }

        if (!Storage::exists($plantilla->archivo)) {
            return back()->with('error', 'El archivo de plantilla no se encontró. Sube la plantilla nuevamente.');
        }

        try {
            $export = new CotizacionExport($factura, $plantilla);
            $rutaTemp = $export->generarYGuardar();

            $nombreDescarga = 'Cotizacion_' . ($factura->nro_cotizacion ?? $factura->id_factura) . '.xlsx';

            return response()->download($rutaTemp, $nombreDescarga)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Error al generar la cotización: ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  PANEL DE ADMINISTRACIÓN DE PLANTILLAS
    // ══════════════════════════════════════════════════════════════

    /**
     * Listado de plantillas por empresa.
     * Ruta: GET /cotizaciones/plantillas
     */
    public function indexPlantillas()
    {
        // EMISORES
        $empresas = EmpresasClientes::where('estado_actual', 1)->orderBy('razon_social')->get();
        $plantillas = CotizacionPlantilla::with('empresa')->get()->keyBy('id_empresa_cliente');
        $celdas = CotizacionPlantilla::celdasEstandar();

        // RECEPTORES (Unificados en una sola lista)
        $clientes = ClienteAuditoria::where('estado_actual', 1)->get()->map(function ($item) {
            $item->tipo_receptor = 'cliente';
            $item->id_display = $item->id_cliente;
            return $item;
        });

        $empresasR = EmpresasClientes::where('estado_actual', 1)->get()->map(function ($item) {
            $item->tipo_receptor = 'empresa';
            $item->id_display = $item->id_empresas_clientes;
            return $item;
        });

        $receptores = $clientes->concat($empresasR)->sortBy('razon_social');

        $plantillasReceptorData = PlantillaReceptor::all()->keyBy(function ($item) {
            return ($item->id_cliente ? 'cliente_' : 'empresa_') . ($item->id_cliente ?? $item->id_empresa_cliente);
        });

        return view('cotizaciones.plantillas.index', compact(
            'empresas',
            'plantillas',
            'celdas',
            'receptores',
            'plantillasReceptorData'
        ));
    }

    /**
     * Formulario para crear/editar la plantilla de una empresa.
     * Ruta: GET /cotizaciones/plantillas/{empresa}
     */
    public function editarPlantilla(EmpresasClientes $empresa)
    {
        $plantilla = CotizacionPlantilla::where('id_empresa_cliente', $empresa->id_empresas_clientes)->first();
        $celdas = CotizacionPlantilla::celdasEstandar();

        return view('cotizaciones.plantillas.form', compact('empresa', 'plantilla', 'celdas'));
    }

    /**
     * Guarda (crea o actualiza) la plantilla de una empresa.
     * Ruta: POST /cotizaciones/plantillas/{empresa}
     */
    public function guardarPlantilla(Request $request, EmpresasClientes $empresa)
    {
        $request->validate([
            'archivo' => 'nullable|file|mimes:xlsx|max:5120',
            'mapa_celdas' => 'nullable|array',
        ]);

        $plantilla = CotizacionPlantilla::firstOrNew([
            'id_empresa_cliente' => $empresa->id_empresas_clientes,
        ]);

        // Subir nuevo archivo si se adjuntó
        if ($request->hasFile('archivo')) {
            // Eliminar el anterior si existe
            if ($plantilla->archivo && Storage::exists($plantilla->archivo)) {
                Storage::delete($plantilla->archivo);
            }

            $ruta = $request->file('archivo')->store(
                'plantillas/' . $empresa->id_empresas_clientes,
                'local'
            );

            $plantilla->archivo = $ruta;
            $plantilla->nombre_original = $request->file('archivo')->getClientOriginalName();
        }

        // Guardar mapa de celdas personalizados (solo los que difieren del estándar)
        $mapaLimpio = [];
        foreach ($request->input('mapa_celdas', []) as $clave => $valor) {
            $valor = trim($valor);
            // Solo guardar si el admin escribió algo distinto al nombre estándar
            if ($valor !== '' && $valor !== $clave) {
                $mapaLimpio[$clave] = strtoupper($valor);
            }
        }

        $plantilla->mapa_celdas = empty($mapaLimpio) ? null : $mapaLimpio;
        $plantilla->save();

        return redirect()
            ->route('cotizaciones.plantillas.index')
            ->with('success', 'Plantilla guardada correctamente para ' . $empresa->razon_social);
    }

    /**
     * Elimina la plantilla de una empresa.
     * Ruta: DELETE /cotizaciones/plantillas/{empresa}
     */
    public function eliminarPlantilla(EmpresasClientes $empresa)
    {
        $plantilla = CotizacionPlantilla::where('id_empresa_cliente', $empresa->id_empresas_clientes)->first();

        if ($plantilla) {
            if ($plantilla->archivo && Storage::exists($plantilla->archivo)) {
                Storage::delete($plantilla->archivo);
            }
            $plantilla->delete();
        }

        return redirect()
            ->route('cotizaciones.plantillas.index')
            ->with('success', 'Plantilla eliminada.');
    }

    public function descargarPlantilla(EmpresasClientes $empresa)
    {
        $plantilla = CotizacionPlantilla::where('id_empresa_cliente', $empresa->id_empresas_clientes)->first();

        if (!$plantilla || !$plantilla->archivo) {
            return back()->with('error', 'No hay archivo de plantilla.');
        }

        if (!Storage::exists($plantilla->archivo)) {
            return back()->with('error', 'El archivo no existe.');
        }

        return Storage::download(
            $plantilla->archivo,
            $plantilla->nombre_original ?? 'plantilla.xlsx'
        );
    }

    // ══════════════════════════════════════════════════════════════
    //  NUEVOS MÉTODOS PARA PLANTILLAS DE RECEPTOR
    // ══════════════════════════════════════════════════════════════

    public function editarPlantillaReceptor(Request $request, $id)
    {
        $tipo = $request->query('tipo', 'cliente');

        if ($tipo === 'empresa') {
            $receptor = EmpresasClientes::findOrFail($id);
            $plantilla = PlantillaReceptor::where('id_empresa_cliente', $id)->first();
            $idReceptor = $receptor->id_empresas_clientes;
        } else {
            $receptor = ClienteAuditoria::findOrFail($id);
            $plantilla = PlantillaReceptor::where('id_cliente', $id)->first();
            $idReceptor = $receptor->id_cliente;
        }

        return view('cotizaciones.plantillas.form_receptor', compact('receptor', 'plantilla', 'tipo', 'idReceptor'));
    }

    public function guardarPlantillaReceptor(Request $request, $id)
    {
        $request->validate([
            'tipo' => 'required|in:empresa,cliente',
            'archivo_solicitud' => 'nullable|file|mimes:xlsx|max:5120',
            'archivo_orden_compra' => 'nullable|file|mimes:xlsx|max:5120',
        ]);

        $tipo = $request->tipo;
        $params = $tipo === 'empresa' ? ['id_empresa_cliente' => $id] : ['id_cliente' => $id];

        $plantilla = PlantillaReceptor::firstOrNew($params);

        // Subir archivo de SOLICITUD
        if ($request->hasFile('archivo_solicitud')) {
            if ($plantilla->archivo_solicitud && Storage::exists($plantilla->archivo_solicitud)) {
                Storage::delete($plantilla->archivo_solicitud);
            }
            $ruta = $request->file('archivo_solicitud')->store('plantillas_receptor/solicitudes', 'local');
            $plantilla->archivo_solicitud = $ruta;
            $plantilla->nombre_original_solicitud = $request->file('archivo_solicitud')->getClientOriginalName();
        }

        // Subir archivo de ORDEN DE COMPRA
        if ($request->hasFile('archivo_orden_compra')) {
            if ($plantilla->archivo_orden_compra && Storage::exists($plantilla->archivo_orden_compra)) {
                Storage::delete($plantilla->archivo_orden_compra);
            }
            $ruta = $request->file('archivo_orden_compra')->store('plantillas_receptor/ordenes', 'local');
            $plantilla->archivo_orden_compra = $ruta;
            $plantilla->nombre_original_orden_compra = $request->file('archivo_orden_compra')->getClientOriginalName();
        }

        $plantilla->save();

        return redirect()
            ->route('cotizaciones.plantillas.index', ['tab' => 'receptores'])
            ->with('success', 'Plantillas guardadas correctamente.');
    }

    public function eliminarPlantillaReceptor(Request $request, $id)
    {
        $tipo = $request->query('tipo', 'cliente');
        $field = $tipo === 'empresa' ? 'id_empresa_cliente' : 'id_cliente';

        $plantilla = PlantillaReceptor::where($field, $id)->first();

        if ($plantilla) {
            if ($plantilla->archivo_solicitud && Storage::exists($plantilla->archivo_solicitud)) {
                Storage::delete($plantilla->archivo_solicitud);
            }
            if ($plantilla->archivo_orden_compra && Storage::exists($plantilla->archivo_orden_compra)) {
                Storage::delete($plantilla->archivo_orden_compra);
            }
            $plantilla->delete();
        }

        return redirect()
            ->route('cotizaciones.plantillas.index', ['tab' => 'receptores'])
            ->with('success', 'Plantillas eliminadas.');
    }

    // ══════════════════════════════════════════════════════════════
    //  NUEVOS MÉTODOS PARA DESCARGA (SOLICITUD Y ORDEN)
    // ══════════════════════════════════════════════════════════════

    public function descargarSolicitud(Factura $factura)
    {
        return $this->generarExportReceptor($factura, 'solicitud');
    }

    public function descargarOrdenCompra(Factura $factura)
    {
        return $this->generarExportReceptor($factura, 'orden_compra');
    }

    private function generarExportReceptor(Factura $factura, string $tipo)
    {
        $cliente = $factura->cliente;
        if ($factura->cliente) {

            $plantilla = PlantillaReceptor::where(
                'id_cliente',
                $factura->cliente->id_cliente
            )->first();

        } elseif ($factura->empresa) {

            $plantilla = PlantillaReceptor::where(
                'id_empresa_cliente',
                $factura->empresa->id_empresas_clientes
            )->first();

        } else {

            return back()->with(
                'error',
                'No se encontró el receptor de esta factura.'
            );
        }

        $campoArchivo = $tipo === 'solicitud'
            ? 'archivo_solicitud'
            : 'archivo_orden_compra';

        if (!$plantilla) {
            return back()->with('error', 'Este receptor no tiene plantillas configuradas.');
        }

        $campoArchivo = $tipo === 'solicitud' ? 'archivo_solicitud' : 'archivo_orden_compra';
        $campoNombre = $tipo === 'solicitud' ? 'nombre_original_solicitud' : 'nombre_original_orden_compra';

        if (!$plantilla->$campoArchivo || !Storage::exists($plantilla->$campoArchivo)) {
            return back()->with('error', 'El archivo de plantilla no se encontró o no está configurado.');
        }

        try {
            // Se usa la clase App\Exports\PlantillaReceptor para generar los documentos del receptor
            // ya que esta clase maneja los placeholders específicos (NRO_SOLICITUD, NRO_ORD_COMPRA, etc.)
            $pFake = new CotizacionPlantilla();
            $pFake->archivo = $plantilla->$campoArchivo;
            $pFake->mapa_celdas = null;

            $export = new \App\Exports\PlantillaReceptor($factura, $pFake);
            $rutaTemp = $export->generarYGuardar();

            $prefijo = $tipo === 'solicitud' ? 'Solicitud_' : 'OrdenCompra_';
            $nombreDescarga = $prefijo . ($factura->id_factura) . '.xlsx';

            return response()->download($rutaTemp, $nombreDescarga)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Error al generar el documento: ' . $e->getMessage());
        }
    }

    public function descargarPlantillaReceptor($id, string $tipo)
    {
        $tipoReceptor = request()->query('tipo', 'cliente');
        $field = $tipoReceptor === 'empresa' ? 'id_empresa_cliente' : 'id_cliente';

        $p = PlantillaReceptor::where($field, $id)->firstOrFail();

        $archivo = ($tipo === 'solicitud') ? $p->archivo_solicitud : $p->archivo_orden_compra;
        $nombre = ($tipo === 'solicitud') ? $p->nombre_original_solicitud : $p->nombre_original_orden_compra;

        if (!$archivo || !Storage::exists($archivo)) {
            return back()->with('error', 'El archivo no existe.');
        }

        return Storage::download($archivo, $nombre);
    }
}
