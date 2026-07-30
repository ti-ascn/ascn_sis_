<?php

namespace App\Http\Controllers;

use App\Models\ClienteAuditoria;
use App\Models\CotizacionPlantilla;
use App\Models\CreditoFiscalTotal;
use App\Models\EmpresasClientes;
use App\Models\Factura;
use App\Models\FormatoNumeroFactura;
use App\Models\FormatoCotizaciones;
use App\Models\FormatoCotizaciones2;
use App\Models\FormatoSolicitudes;
use App\Models\PlantillaReceptor;
use App\Models\Guia;
use App\Models\DetalleFactura;
use App\Models\Inventario;
use App\Models\PrecioDolar;
use App\Models\Producto;
use App\Models\RelacionFacturacion;
use App\Models\User;
use App\Models\CuentaEmpresa;
use App\Exports\CuentasEmpresasExport;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\FormatoCotizaciones2Controller;
use App\Http\Controllers\FormatoNOrdenController;
use App\Http\Controllers\FormatoNSolicitudController;
use Carbon\Carbon;

class FacturaController extends Controller
{
    public function index()
    {
        // Facturas paginadas
        $facturas = Factura::with(['empresa', 'cliente'])
            ->orderByDesc('fecha_emision')
            ->paginate(perPage: 100); // numero de registros cargados por pagina

        // Traes solo lo necesario
        $empresas = EmpresasClientes::select('ruc', 'razon_social')->get()->keyBy('ruc');
        $clientes = ClienteAuditoria::select('ruc', 'razon_social')->get()->keyBy('ruc');

        // Inventarios para el modal
        $inventarios = Inventario::select('id_inventario', 'id_empresas_cliente', 'nombre_codigo')
            ->with('empresa:id_empresas_clientes,razon_social')
            ->get();

        // Usuarios auditoría para permisos
        $usuariosAuditoria = User::select('id', 'name', 'puede_editar')
            ->where('tipo_usuario', 3)
            ->where('id_area', 7)
            ->where('estado', 1)
            ->get();

        $empresasConPlantilla = CotizacionPlantilla::with('empresa')
            ->get()
            ->pluck('empresa.ruc')
            ->filter()
            ->toArray();

        $empresasConSolicitud = PlantillaReceptor::with(['empresa', 'cliente'])
            ->get()
            ->filter(fn($r) => !empty($r->archivo_solicitud))
            ->map(fn($r) => $r->empresa?->ruc ?? $r->cliente?->ruc)
            ->filter()
            ->unique()
            ->toArray();

        $empresasConOrdCompra = PlantillaReceptor::with(['empresa', 'cliente'])
            ->get()
            ->filter(fn($r) => !empty($r->archivo_orden_compra))
            ->map(fn($r) => $r->empresa?->ruc ?? $r->cliente?->ruc)
            ->filter()
            ->unique()
            ->toArray();

        $anios = Factura::selectRaw('YEAR(fecha_emision) as anio')->distinct()->orderByDesc('anio')->pluck('anio');
        $mesesArray = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        return view('facturas.index', compact(
            'facturas',
            'usuariosAuditoria',
            'empresas',
            'clientes',
            'inventarios',
            'empresasConPlantilla',
            'empresasConSolicitud',
            'empresasConOrdCompra',
            'anios',
            'mesesArray'
        ));
    }

    public function createDesdeEmpresa($id_inventario, Request $request)
    {
        $inventario = Inventario::with([
            'empresa',
            'productos' => fn($q) => $q->where('estado', 1),
        ])->findOrFail($id_inventario);

        $empresa = $inventario->empresa;

        // FILTRO SEGÚN RELACIONES
        $destinos = RelacionFacturacion::where('id_empresa_origen', $empresa->id_empresas_clientes)
            ->pluck('id_empresa_destino')
            ->toArray();

        if (empty($destinos)) {
            $empresas = EmpresasClientes::all();
        } else {
            $empresas = EmpresasClientes::whereIn('id_empresas_clientes', $destinos)->get();
        }

        // Precio dólar
        $precioDolar = PrecioDolar::first();

        // Receptor preseleccionado
        $idReceptorSeleccionado = $request->query('receptor');

        // Ventana dinámica
        $anchorDay = 3;
        $hoy = now();

        if ($hoy->day < $anchorDay) {
            $fechaInicio = $hoy->copy()->subMonthNoOverflow()->day($anchorDay)->startOfDay();
            $fechaFinExclusivo = $hoy->copy()->day($anchorDay)->startOfDay();
        } else {
            $fechaInicio = $hoy->copy()->day($anchorDay)->startOfDay();
            $fechaFinExclusivo = $hoy->copy()->addMonthNoOverflow()->day($anchorDay)->startOfDay();
        }

        // Contador de ventas
        $conteoVentas = [];
        $formatoCorrelativo = null;
        if ($idReceptorSeleccionado) {
            $conteoVentas = DetalleFactura::select('id_producto', DB::raw('COUNT(*) as total'))
                ->whereHas('factura', function ($query) use ($idReceptorSeleccionado, $fechaInicio, $fechaFinExclusivo) {
                    $query->where('id_empresa_cliente', $idReceptorSeleccionado)
                        ->where('estado', '!=', 'Anulada')
                        ->where('fecha_emision', '>=', $fechaInicio)
                        ->where('fecha_emision', '<', $fechaFinExclusivo);
                })
                ->groupBy('id_producto')
                ->pluck('total', 'id_producto')
                ->toArray();

            $formato = FormatoNumeroFactura::where('id_empresas_clientes', $idReceptorSeleccionado)
                ->latest('id_formato_numero_factura')
                ->first();
            if ($formato) {
                $formatoCorrelativo = $formato->generarCadenaFormateada($formato->contador_actual + 1);
            }
        }
        $pendientePorFacturar = $request->query('pendiente');
        $monedaPendiente = $request->query('moneda');

        return view('facturas.create_empresa', compact(
            'empresa',
            'empresas',
            'precioDolar',
            'inventario',
            'idReceptorSeleccionado',
            'conteoVentas',
            'formatoCorrelativo',
            'pendientePorFacturar',
            'monedaPendiente'
        ));
    }

    public function createDesdeCliente($id_inventario, Request $request)
    {
        $inventario = Inventario::with([
            'empresa',
            'productos' => function ($query) {
                $query->where('estado', 1);
            }
        ])->findOrFail($id_inventario);

        $empresa = $inventario->empresa;
        $clientes = ClienteAuditoria::all();
        $precioDolar = PrecioDolar::first();
        $idReceptorSeleccionado = $request->query('receptor');

        // Ventana de conteo: del 3 de cada mes (incl.) al 3 del mes siguiente (excl.)
        $anchorDay = 3; // si quieres "del 2 al 2", cambia a 2
        $hoy = now();

        if ($hoy->day < $anchorDay) {
            // Antes del anchor del mes actual → inicia el 3 del mes anterior
            $fechaInicio = $hoy->copy()->subMonthNoOverflow()->day($anchorDay)->startOfDay();
            $fechaFinExclusivo = $hoy->copy()->day($anchorDay)->startOfDay(); // 3 de este mes 00:00
        } else {
            // En/tras el anchor del mes actual → inicia el 3 de este mes
            $fechaInicio = $hoy->copy()->day($anchorDay)->startOfDay();
            $fechaFinExclusivo = $hoy->copy()->addMonthNoOverflow()->day($anchorDay)->startOfDay(); // 3 del próximo 00:00
        }

        // Obtener conteo de ventas por producto en la ventana definida
        $conteoVentas = [];
        $formatoCorrelativo = null;
        if ($idReceptorSeleccionado) {
            $conteoVentas = DetalleFactura::select('id_producto', DB::raw('COUNT(*) as total'))
                ->whereHas('factura', function ($query) use ($idReceptorSeleccionado, $fechaInicio, $fechaFinExclusivo) {
                    $query->where('id_cliente', $idReceptorSeleccionado)
                        ->where('estado', '!=', 'Anulada')               // opcional, igual que en Empresa
                        ->where('fecha_emision', '>=', $fechaInicio)     // inicio inclusivo
                        ->where('fecha_emision', '<', $fechaFinExclusivo); // fin exclusivo
                })
                ->groupBy('id_producto')
                ->pluck('total', 'id_producto')
                ->toArray();

            $formato = FormatoNumeroFactura::where('id_cliente', $idReceptorSeleccionado)
                ->latest('id_formato_numero_factura')
                ->first();
            if ($formato) {
                $formatoCorrelativo = $formato->generarCadenaFormateada($formato->contador_actual + 1);
            }
        }
        $pendientePorFacturar = $request->query('pendiente');
        $monedaPendiente = $request->query('moneda');

        return view('facturas.create_cliente', compact(
            'empresa',
            'clientes',
            'precioDolar',
            'inventario',
            'idReceptorSeleccionado',
            'conteoVentas',
            'formatoCorrelativo',
            'pendientePorFacturar',
            'monedaPendiente'
        ));
    }


    public function verPorInventario($id)
    {
        $inventario = Inventario::with('empresa')->findOrFail($id);
        $rucEmisor = $inventario->empresa->ruc;

        $facturas = Factura::where('ruc_emisor', $rucEmisor)->orderByDesc('fecha_emision')->get();

        $clientes = ClienteAuditoria::all()->keyBy('ruc');
        $empresasClientes = EmpresasClientes::all()->keyBy('ruc');

        return view('facturas.index_por_inventario', compact('inventario', 'facturas', 'clientes', 'empresasClientes'));
    }

    public function verPorCliente($id_cliente)
    {
        $cliente = ClienteAuditoria::findOrFail($id_cliente);

        $facturas = Factura::where('id_cliente', $id_cliente)
            ->orderByDesc('fecha_emision')
            ->get();

        $empresas = EmpresasClientes::all()->keyBy('ruc');

        return view('facturas.index_por_cliente', compact('cliente', 'facturas', 'empresas'));
    }

    public function verPorEmpresa($id_empresa)
    {
        $empresa = EmpresasClientes::findOrFail($id_empresa);

        $facturas = Factura::where('id_empresa_cliente', $id_empresa)
            ->orderByDesc('fecha_emision')
            ->get();

        $empresas = EmpresasClientes::all()->keyBy('ruc');


        return view('facturas.index_por_empresa', compact('empresa', 'facturas', 'empresas'));
    }

    public function verDetalle($id)
    {
        $factura = Factura::with(['empresa', 'cliente', 'detalles.producto.inventario.empresa', 'usuario', 'programacionChofer.empresa'])
            ->findOrFail($id);

        // Para mostrar datos del emisor (dueño del inventario)
        $emisor = EmpresasClientes::where('ruc', $factura->ruc_emisor)->first();

        return view('facturas.detalle', compact('factura', 'emisor'));
    }

    // !inicio store
    public function store(Request $request)
    {
        $request->validate([
            'origen_receptor' => 'required|in:empresa,cliente',
            'id_receptor' => 'required|integer',

            // SUNAT
            'tipo_factura' => 'required|integer',

            'agente_retenedor' => 'required|in:S,N',
            'tipo_moneda' => 'required|in:PEN,USD',

            'productos' => 'required|array',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0.01',
            'productos.*.peso' => 'nullable|numeric|min:0',
            'productos.*.unidad' => 'required|string|max:50',

            'tipo_credito' => 'required',
            'tipo_credito_personalizado' => 'nullable|numeric|min:1|max:365',

            'nro_cotizacion' => 'nullable|string|max:255',
            'fecha_cotizacion' => 'nullable|date',
            'nombre_correlativo' => 'required|string|max:255',
            'numero_solicitud' => 'required|string|max:255',
            'fecha_solicitud' => 'nullable|date',
            'nro_factura' => 'nullable|string|max:255',
            'grr' => 'nullable|string|max:255',
            'grt' => 'nullable|string|max:255',
            'fecha_traslado' => 'nullable|date',
            'id_programacion' => 'nullable|integer',
            'fecha_emision' => 'required|date',

            'id_empresa_contexto' => 'required|integer',
            'precio_dolar' => 'required',
            'precio_dolar_factura' => 'nullable|numeric|min:0.001',
        ]);

        // =====================================================
        // EMPRESA EMISORA
        // =====================================================
        $empresaContexto = EmpresasClientes::findOrFail($request->id_empresa_contexto);
        $rucEmisor = $empresaContexto->ruc;

        // =====================================================
        // RECEPTOR (CLARO Y SIN MAGIA)
        // =====================================================
        $idEmpresaCliente = null;
        $idCliente = null;

        if ($request->origen_receptor === 'empresa') {
            $receptor = EmpresasClientes::findOrFail($request->id_receptor);
            $idEmpresaCliente = $receptor->id_empresas_clientes;
        } else {
            $receptor = ClienteAuditoria::findOrFail($request->id_receptor);
            $idCliente = $receptor->id_cliente;
        }

        $rucReceptor = $receptor->ruc;

        return DB::transaction(function () use ($request, $rucEmisor, $idEmpresaCliente, $idCliente, $rucReceptor) {
            // =====================================================
            // PRODUCTOS (TU LÓGICA ORIGINAL, INTACTA)
            // =====================================================
            $productos = Producto::whereIn('id_producto', array_keys($request->productos))->get();

            $total_bruto = 0;
            $peso_total = 0;

            foreach ($productos as $producto) {
                $data = $request->productos[$producto->id_producto];
                $cantidad = (int) $data['cantidad'];
                if ($cantidad <= 0)
                    continue;

                $precio = (float) $data['precio'];
                $peso = (float) ($data['peso'] ?? $producto->peso ?? 0);
                $unidad = $data['unidad'] ?? $producto->unidad_medida;

                $producto->update([
                    'precio_unitario' => $precio,
                    'peso' => $peso,
                    // No se actualiza unidad_medida en el producto;
                    // cada detalle_factura guarda su propia unidad.
                ]);

                Producto::where('nombre', $producto->nombre)
                    ->where('id_producto', '!=', $producto->id_producto)
                    ->update([
                        'peso' => $peso,
                        // Tampoco se propaga unidad_medida a otros productos del mismo nombre.
                    ]);

                $total_bruto += $precio * $cantidad;
                $peso_total += $peso * $cantidad;
            }

            if ($total_bruto <= 0) {
                return back()->with('error', 'No se ingresaron cantidades válidas.');
            }

            // =====================================================
            // PROTECCIÓN INTELIGENTE CONTRA DUPLICADOS (DOBLE-CLICK / DOBLE ENVÍO)
            // =====================================================
            $submittableProducts = [];
            foreach ($request->productos as $pId => $pData) {
                $cant = (int) ($pData['cantidad'] ?? 0);
                if ($cant > 0) {
                    $submittableProducts[(int) $pId] = $cant;
                }
            }
            ksort($submittableProducts);

            $fechaEmisionObj = Carbon::parse($request->fecha_emision);
            $posiblesDuplicados = Factura::with('detalles')
                ->where('ruc_emisor', $rucEmisor)
                ->where('ruc_receptor', $rucReceptor)
                ->whereDate('fecha_emision', $fechaEmisionObj->toDateString())
                ->where('total_bruto', $total_bruto)
                ->where('tipo_moneda', $request->tipo_moneda)
                ->where('created_at', '>=', now()->subSeconds(45))
                ->get();

            foreach ($posiblesDuplicados as $facturaExistente) {
                $existingProducts = $facturaExistente->detalles->pluck('cantidad', 'id_producto')->toArray();
                ksort($existingProducts);

                if ($existingProducts == $submittableProducts) {
                    return back()->withInput()
                        ->with('error', '⚠ Se detectó un envío duplicado de la misma factura. Por favor, verifica en el listado antes de intentar nuevamente.');
                }
            }

            // =====================================================
            // TOTALES
            // =====================================================
            $retencion = $request->agente_retenedor === 'S'
                ? round($total_bruto * 0.03, 2)
                : 0;

            $total_neto = $total_bruto - $retencion;

            $fechaEmision = Carbon::parse($request->fecha_emision);
            $diasCredito = $request->tipo_credito === 'personalizado'
                ? (int) $request->tipo_credito_personalizado
                : (int) $request->tipo_credito;

            $fechaPago = $fechaEmision->copy()->addDays($diasCredito);

            $estado = !empty($request->nro_factura)
                ? 'Facturada'
                : 'Pendiente';

            // =====================================================
            // VALIDAR nro_cotizacion con FormatoCotizaciones2 (v2)
            // Ahora el formato es POR EMISOR, sin filtro de receptor
            $formatoCoti2 = FormatoCotizaciones2::where('id_empresas_clientes_emisor', $request->id_empresa_contexto)->first();
            if ($formatoCoti2 && $request->nro_cotizacion) {
                $regex = '/^' . str_replace('\{N\}', '(\d+)', preg_quote($formatoCoti2->formato_final, '/')) . '$/';
                if (preg_match($regex, $request->nro_cotizacion)) {
                    $existeCoti = Factura::where('nro_cotizacion', $request->nro_cotizacion)
                        ->whereNotIn('estado', ['Anulada'])
                        ->exists();
                    if ($existeCoti) {
                        return back()->withInput()->with('error', 'Nro Cotización "' . $request->nro_cotizacion . '" en uso.');
                    }
                }
            }


            // =====================================================
            // VALIDAR CORRELATIVO ÚNICO (solo si viene de formato)
            // =====================================================
            $queryFormatoReceptor = FormatoNumeroFactura::query();
            if ($idEmpresaCliente) {
                $queryFormatoReceptor->where('id_empresas_clientes', $idEmpresaCliente);
            } else {
                $queryFormatoReceptor->where('id_cliente', $idCliente);
            }
            $formatoDelReceptor = $queryFormatoReceptor->latest('id_formato_numero_factura')->first();
            if (!$formatoDelReceptor) {
                $formatos = FormatoNumeroFactura::all();
                $esDeFormato = false;

                foreach ($formatos as $fmt) {
                    if (empty($fmt->formato_final))
                        continue;
                    $partes = explode('{N}', $fmt->formato_final);
                    $patron = implode('(\d+)', array_map(fn($p) => preg_quote($p, '/'), $partes));
                    if (preg_match('/^' . $patron . '$/', $request->nombre_correlativo)) {
                        $esDeFormato = true;
                        break;
                    }
                }

                if ($esDeFormato) {
                    $existe = Factura::where('nombre_correlativo', $request->nombre_correlativo)
                        ->whereNotIn('estado', ['Anulada'])
                        ->exists();

                    if ($existe) {
                        return back()
                            ->withInput()
                            ->with('error', 'Nro Orden "' . $request->nombre_correlativo . '" en uso.');
                    }
                }
            }
            // =====================================================
            // VALIDAR NUMERO_SOLICITUD ÚNICO
            // =====================================================
            $queryFormatoSolReceptor = FormatoSolicitudes::query();
            if ($idEmpresaCliente) {
                $queryFormatoSolReceptor->where('id_empresas_clientes', $idEmpresaCliente);
            } else {
                $queryFormatoSolReceptor->where('id_cliente', $idCliente);
            }
            $formatoSolDelReceptor = $queryFormatoSolReceptor->latest('id_formato_solicitudes')->first();
            if (!$formatoSolDelReceptor) {
                $formatosSol = FormatoSolicitudes::all();
                $esDeFormatoSol = false;

                foreach ($formatosSol as $fmt) {
                    if (empty($fmt->formato_final))
                        continue;
                    $partes = explode('{N}', $fmt->formato_final);
                    $patron = implode('(\d+)', array_map(fn($p) => preg_quote($p, '/'), $partes));
                    if (preg_match('/^' . $patron . '$/', $request->numero_solicitud)) {
                        $esDeFormatoSol = true;
                        break;
                    }
                }

                if ($esDeFormatoSol) {
                    $existeSol = Factura::where('numero_solicitud', $request->numero_solicitud)
                        ->whereNotIn('estado', ['Anulada'])
                        ->exists();

                    if ($existeSol) {
                        return back()
                            ->withInput()
                            ->with('error', 'Nro Solicitud "' . $request->numero_solicitud . '" en uso.');
                    }
                }
            }


            // =====================================================
            // FACTURA
            // =====================================================
            $factura = Factura::create([
                'id_usuario' => Auth::id(),
                'creado_por' => Auth::id(),
                'id_empresa_cliente' => $idEmpresaCliente,
                'id_cliente' => $idCliente,

                'nro_cotizacion' => $request->nro_cotizacion,
                'fecha_cotizacion' => $request->fecha_cotizacion,
                'nombre_correlativo' => $request->nombre_correlativo,
                'numero_solicitud' => $request->numero_solicitud,
                'fecha_solicitud' => $request->fecha_solicitud,
                'nro_factura' => $request->nro_factura,
                'grr' => $request->grr,
                'grt' => $request->grt,

                'fecha_emision' => $fechaEmision,
                'fecha_traslado' => $request->fecha_traslado,
                'id_programacion' => $request->id_programacion,
                'fecha_pago' => $fechaPago,

                'total_bruto' => $total_bruto,
                'monto_retencion' => $retencion,
                'total_neto' => $total_neto,
                'peso_neto' => $peso_total,

                'estado' => $estado,
                'agente_retenedor' => $request->agente_retenedor,

                // SUNAT
                'tipo_factura' => $request->tipo_factura,
                'tipo_moneda' => $request->tipo_moneda,

                'precio_dolar' => $request->precio_dolar,
                'precio_dolar_factura' => $request->precio_dolar_factura,

                'ruc_emisor' => $rucEmisor,
                'ruc_receptor' => $rucReceptor,
            ]);

            // =====================================================
            // LÓGICA DE CORRELATIVO
            // =====================================================
            $queryFormato = FormatoNumeroFactura::query();
            if ($idEmpresaCliente) {
                $queryFormato->where('id_empresas_clientes', $idEmpresaCliente);
            } else {
                $queryFormato->where('id_cliente', $idCliente);
            }
            $formato = $queryFormato->latest('id_formato_numero_factura')->first();

            if ($formato) {
                $coincideConFormato = false;
                if (!empty($formato->formato_final)) {
                    $partes = explode('{N}', $formato->formato_final);
                    $patron = implode('(\d+)', array_map(fn($p) => preg_quote($p, '/'), $partes));
                    $coincideConFormato = preg_match('/^' . $patron . '$/', $request->nombre_correlativo) ? true : false;
                }

                if (($coincideConFormato || empty($request->nombre_correlativo)) && $estado !== 'Facturada') {
                    $factura->nombre_correlativo = 'PENDIENTE-STORE-' . $factura->id_factura;
                    $factura->save();
                    $this->insertarCorrelativoEnPosicion($idCliente, $idEmpresaCliente, $factura->id_factura);
                } elseif ($estado === 'Facturada' && $coincideConFormato) {
                    $numero = $this->extraerContadorDeCorrelativo($request->nombre_correlativo, $formato);
                    if ($numero !== null && $numero > $formato->contador_actual) {
                        $formato->contador_actual = $numero;
                        $formato->save();
                    }
                }
            }

            // =====================================================
            // LÓGICA DE COTIZACIÓN (v2 — por Emisor)
            if ($formatoCoti2) {
                $coincideConFormatoCoti2 = false;
                if (!empty($formatoCoti2->formato_final)) {
                    $partesCoti2 = explode('{N}', $formatoCoti2->formato_final);
                    $patronCoti2 = implode('(\d+)', array_map(fn($p) => preg_quote($p, '/'), $partesCoti2));
                    $coincideConFormatoCoti2 = preg_match('/^' . $patronCoti2 . '$/', $request->nro_cotizacion) ? true : false;
                }

                if (($coincideConFormatoCoti2 || empty($request->nro_cotizacion)) && $estado !== 'Facturada') {
                    // Asignar automáticamente con reglas de salto y llenado de huecos
                    // Se excluye la factura recién creada para que su nro_cotizacion temporal no interfiera
                    $nroCotiAuto = FormatoCotizaciones2Controller::calcularSiguienteNro($formatoCoti2, $request->fecha_emision, $factura->id_factura);
                    if ($nroCotiAuto === 'FECHA_LLENA') {
                        $factura->delete();
                        return back()->withInput()->with('error', 'No hay números de cotización disponibles para la fecha ' . $request->fecha_emision . '. Todos los huecos están ocupados.');
                    }
                    $factura->nro_cotizacion = $nroCotiAuto;
                    // Actualizar contador
                    $regex2 = '/^' . str_replace('\{N\}', '(\d+)', preg_quote($formatoCoti2->formato_final, '/')) . '$/';
                    if (preg_match($regex2, $nroCotiAuto, $m2)) {
                        $num2 = (int) $m2[1];
                        if ($num2 > $formatoCoti2->contador_actual) {
                            $formatoCoti2->contador_actual = $num2;
                            $formatoCoti2->save();
                        }
                    }
                    $factura->save();
                } elseif ($estado === 'Facturada' && $coincideConFormatoCoti2) {
                    $regex2 = '/^' . str_replace('\{N\}', '(\d+)', preg_quote($formatoCoti2->formato_final, '/')) . '$/';
                    if (preg_match($regex2, $request->nro_cotizacion, $m2)) {
                        $num2 = (int) $m2[1];
                        if ($num2 > $formatoCoti2->contador_actual) {
                            $formatoCoti2->contador_actual = $num2;
                            $formatoCoti2->save();
                        }
                    }
                }
            }

            // =====================================================
            // LÓGICA DE SOLICITUD
            // =====================================================
            $queryFormatoSol = FormatoSolicitudes::query();
            if ($idEmpresaCliente) {
                $queryFormatoSol->where('id_empresas_clientes', $idEmpresaCliente);
            } else {
                $queryFormatoSol->where('id_cliente', $idCliente);
            }
            $formatoSol = $queryFormatoSol->latest('id_formato_solicitudes')->first();

            if ($formatoSol) {
                $coincideConFormatoSol = false;
                if (!empty($formatoSol->formato_final)) {
                    $partesSol = explode('{N}', $formatoSol->formato_final);
                    $patronSol = implode('(\d+)', array_map(fn($p) => preg_quote($p, '/'), $partesSol));
                    $coincideConFormatoSol = preg_match('/^' . $patronSol . '$/', $request->numero_solicitud) ? true : false;
                }

                if (($coincideConFormatoSol || empty($request->numero_solicitud)) && $estado !== 'Facturada') {
                    $factura->numero_solicitud = 'SOLICITUD-PENDIENTE-STORE-' . $factura->id_factura;
                    $factura->save();
                    $this->insertarSolicitudEnPosicion($idCliente, $idEmpresaCliente, $factura->id_factura);
                } elseif ($estado === 'Facturada' && $coincideConFormatoSol) {
                    $numeroSol = $this->extraerContadorDeSolicitud($request->numero_solicitud, $formatoSol);
                    if ($numeroSol !== null && $numeroSol > $formatoSol->contador_actual) {
                        $formatoSol->contador_actual = $numeroSol;
                        $formatoSol->save();
                    }
                }
            }

            // =====================================================
            // DETALLES
            // =====================================================
            foreach ($productos as $producto) {
                $data = $request->productos[$producto->id_producto];
                $cantidad = (int) $data['cantidad'];
                if ($cantidad <= 0)
                    continue;

                DetalleFactura::create([
                    'id_factura' => $factura->id_factura,
                    'id_producto' => $producto->id_producto,
                    'precio_unitario' => $data['precio'],
                    'unidad_medida' => $data['unidad'] ?? ($data['unidad_medida'] ?? null),
                    'cantidad' => $cantidad,
                    'peso_total' => ($data['peso'] ?? 0) * $cantidad,
                    'precio_total' => $data['precio'] * $cantidad,
                ]);
            }

            // =====================================================
            // GENERACIÓN AUTOMÁTICA DE GUÍA
            // =====================================================
            $mesEmision = $fechaEmision->month;
            $anioEmision = $fechaEmision->year;

            // Verificar si la guía automática debe crearse
            // Solo para empresas C1 y clientes de auditoría
            $debeCrearGuiaAutomatica = false;

            if ($idEmpresaCliente) {
                // Si es empresa, debe ser C1
                $empresaReceptor = EmpresasClientes::find($idEmpresaCliente);
                if ($empresaReceptor && $empresaReceptor->estado === 'C1') {
                    $debeCrearGuiaAutomatica = true;
                }
            } elseif ($idCliente) {
                // Si es cliente, es automáticamente válido (es cliente de auditoría)
                $debeCrearGuiaAutomatica = true;
            }

            // =====================================================
            // MONTO GUÍA (NORMALIZADO)
            // =====================================================
            $montoGuia = $total_bruto;

            if ($request->tipo_moneda === 'USD') {
                $rate = ($request->precio_dolar > 0)
                    ? $request->precio_dolar
                    : 1;

                $montoGuia = $total_bruto / $rate;
            }

            // Verificar si ya existe una guía para este receptor en este mes
            if ($debeCrearGuiaAutomatica) {
                $existeGuia = Guia::whereMonth('recepcion', $mesEmision)
                    ->whereYear('recepcion', $anioEmision)
                    ->where(function ($query) use ($idEmpresaCliente, $idCliente) {
                        if ($idEmpresaCliente) {
                            $query->where('id_empresas_clientes', $idEmpresaCliente);
                        } elseif ($idCliente) {
                            $query->where('id_cliente', $idCliente);
                        }
                    })
                    ->exists();

                if (!$existeGuia) {
                    Guia::create([
                        'id_empresas_clientes' => $idEmpresaCliente,
                        'id_cliente' => $idCliente,
                        'id_usuario' => Auth::id(),
                        'moneda' => $request->tipo_moneda,
                        'monto' => $montoGuia,
                        'recepcion' => $fechaEmision,
                        'programado' => 'pendiente',
                        'emitido' => 'pendiente',
                        'grr' => '-',
                        'grt' => '-',
                        'bancarizacion' => '-',
                        'contingencia' => 0,
                        'observaciones' => 'Generada automáticamente por Factura',
                    ]);
                }
            }

            return redirect()->route('facturas.index')
                ->with('success', 'Factura creada correctamente.');
        });
    }
    // !fin store


    public function actualizarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:Facturada,Pendiente,Anulada',
        ]);

        $factura = Factura::findOrFail($id);
        $factura->estado = $request->estado;
        $factura->save();

        $this->insertarCorrelativoEnPosicion($factura->id_cliente, $factura->id_empresa_cliente, $factura->id_factura);
        $emisor = EmpresasClientes::where('ruc', $factura->ruc_emisor)->first();
        $idEmisor = $emisor ? $emisor->id_empresas_clientes : null;
        $this->insertarCorrelativoCotiEnPosicion(
            null,
            $idEmisor,
            $factura->id_factura,
            $factura->id_empresa_cliente,
            $factura->id_cliente
        );
        $this->insertarSolicitudEnPosicion($factura->id_cliente, $factura->id_empresa_cliente, $factura->id_factura);

        // Recalcular contadores para asegurar que reflejen la mayor factura activa
        FormatoNOrdenController::recalcularContador($factura->id_cliente, $factura->id_empresa_cliente);
        FormatoNSolicitudController::recalcularContador($factura->id_cliente, $factura->id_empresa_cliente);
        if ($idEmisor) {
            FormatoCotizaciones2Controller::recalcularContador($idEmisor);
        }

        return back()->with('success', 'Estado actualizado correctamente.');
    }

    public function verFacturasMes(Request $request)
    {
        $mes = $request->input('mes', now()->format('m'));
        $anio = $request->input('anio', now()->format('Y'));

        $userId = auth()->id();
        $tipo = $request->input('tipo', '1');

        // 🔒 Forzar valores permitidos para usuario 35
        if ($userId == 35 && !in_array($tipo, ['2', '3'])) {
            $tipo = '2'; // Valor por defecto
        }

        $inicioMes = Carbon::createFromDate($anio, $mes, 1)->startOfMonth();
        $finMes = Carbon::createFromDate($anio, $mes, 1)->endOfMonth();

        $query = Factura::with(['empresa.ultimaDireccionLlegada', 'cliente.ultimaDireccionLlegada'])
            ->whereBetween('fecha_emision', [$inicioMes, $finMes])
            ->whereIn('estado', ['Facturada', 'Pendiente']);

        // 🔒 Filtro por tipo
        if ($tipo !== 'todos') {
            $query->where('tipo_factura', $tipo);
        } elseif ($userId == 35) {
            // Si es usuario 35 y elige "todos", solo ve tipo 2 y 3
            $query->whereIn('tipo_factura', ['2', '3']);
        }

        $facturas = $query->orderByDesc('fecha_emision')->get();

        $empresas = EmpresasClientes::all()->keyBy('ruc');
        $clientes = ClienteAuditoria::all()->keyBy('ruc');
        $inventarios = Inventario::all();

        // RUCs con plantilla de cotización (emisión)
        $empresasConPlantilla = CotizacionPlantilla::with('empresa')
            ->get()
            ->pluck('empresa.ruc')
            ->filter()
            ->toArray();

        // RUCs con plantilla de solicitud (receptor)
        $empresasConSolicitud = PlantillaReceptor::with(['empresa', 'cliente'])
            ->get()
            ->filter(fn($r) => !empty($r->archivo_solicitud))
            ->map(fn($r) => $r->empresa?->ruc ?? $r->cliente?->ruc)
            ->filter()
            ->unique()
            ->toArray();

        // RUCs con plantilla de orden de compra (receptor)
        $empresasConOrdCompra = PlantillaReceptor::with(['empresa', 'cliente'])
            ->get()
            ->filter(fn($r) => !empty($r->archivo_orden_compra))
            ->map(fn($r) => $r->empresa?->ruc ?? $r->cliente?->ruc)
            ->filter()
            ->unique()
            ->toArray();

        return view('facturas.mes_actual', compact(
            'facturas',
            'empresas',
            'clientes',
            'inventarios',
            'mes',
            'anio',
            'tipo',
            'empresasConPlantilla',
            'empresasConSolicitud',
            'empresasConOrdCompra'
        ));
    }

    public function edit($id)
    {
        $usuario = auth()->user();

        if (
            $usuario->tipo_usuario == 3 &&
            ($usuario->id_area != 7 || !$usuario->puede_editar)
        ) {
            return back()->with('error', 'No tienes permiso para acceder a esta vista.');
        }

        $factura = Factura::with(['detalles.producto', 'cliente', 'empresa'])->findOrFail($id);
        $empresa = EmpresasClientes::where('ruc', $factura->ruc_emisor)->first();
        $precioDolar = PrecioDolar::first();

        // DETERMINAR SI ES CLIENTE O EMPRESA - IMPORTANTE: Corregido
        // Si la factura tiene ruc_receptor, buscamos si es cliente o empresa
        $tipo_factura = 'empresa'; // Por defecto
        $idReceptorSeleccionado = null;

        if ($factura->ruc_receptor) {
            // Buscar si el RUC pertenece a un cliente
            $cliente = ClienteAuditoria::where('ruc', $factura->ruc_receptor)->first();
            if ($cliente) {
                $tipo_factura = 'cliente';
                $idReceptorSeleccionado = $cliente->id_cliente;
            } else {
                // Buscar si el RUC pertenece a una empresa
                $empresaReceptor = EmpresasClientes::where('ruc', $factura->ruc_receptor)->first();
                if ($empresaReceptor) {
                    $tipo_factura = 'empresa';
                    $idReceptorSeleccionado = $empresaReceptor->id_empresas_clientes;
                }
            }
        }

        // También verificar los campos originales por si acaso
        if ($factura->id_cliente) {
            $tipo_factura = 'cliente';
            $idReceptorSeleccionado = $factura->id_cliente;
        } elseif ($factura->id_empresa_cliente) {
            $tipo_factura = 'empresa';
            $idReceptorSeleccionado = $factura->id_empresa_cliente;
        }

        $clientes = ClienteAuditoria::all();
        $empresas = EmpresasClientes::all();

        // Días de crédito
        $diasCredito = Carbon::parse($factura->fecha_emision)->diffInDays(Carbon::parse($factura->fecha_pago));

        $rucReceptor = $factura->ruc_receptor;

        return view('facturas.edit', compact(
            'factura',
            'empresa',
            'precioDolar',
            'tipo_factura',
            'clientes',
            'empresas',
            'diasCredito',
            'rucReceptor',
            'idReceptorSeleccionado'
        ));
    }

    public function update(Request $request, $id)
    {
        $usuario = auth()->user();

        if ($usuario->tipo_usuario == 3 && ($usuario->id_area != 7 || !$usuario->puede_editar)) {
            return redirect()->route('facturas.index')->with('error', 'No tienes permiso para editar.');
        }

        $diasCredito = $request->tipo_credito === 'personalizado'
            ? intval($request->tipo_credito_personalizado)
            : intval($request->tipo_credito);

        $fechaPago = Carbon::parse($request->fecha_emision)->addDays($diasCredito);

        $estado = !empty($request->nro_factura)
            ? 'Facturada'
            : 'Pendiente';

        $factura = Factura::findOrFail($id);
        $oldIdCliente = $factura->id_cliente;
        $oldIdEmpresaCliente = $factura->id_empresa_cliente;
        $oldNroCotizacion = $factura->nro_cotizacion;

        DB::transaction(function () use ($request, $factura, $fechaPago, $estado, $oldIdCliente, $oldIdEmpresaCliente, $oldNroCotizacion) {
            // ACTUALIZA AQUÍ
            $factura->update([
                'nro_cotizacion' => $request->nro_cotizacion,
                'fecha_cotizacion' => $request->fecha_cotizacion,
                'nombre_correlativo' => $request->nombre_correlativo,
                'numero_solicitud' => $request->numero_solicitud,
                'fecha_solicitud' => $request->fecha_solicitud,
                'nro_factura' => $request->nro_factura,
                'grr' => $request->grr,
                'grt' => $request->grt,
                'fecha_traslado' => $request->fecha_traslado,
                'id_programacion' => $request->id_programacion,
                'fecha_emision' => $request->fecha_emision,
                'tipo_moneda' => $request->tipo_moneda,
                'agente_retenedor' => $request->agente_retenedor,
                'total_bruto' => 0,
                'monto_retencion' => 0,
                'total_neto' => 0,
                'peso_neto' => 0,
                'fecha_pago' => $fechaPago,
                'estado' => $estado,
                'id_usuario' => auth()->id(),
                'precio_dolar' => $request->precio_dolar,
                'tipo_factura' => $request->tipo_factura,
                'precio_dolar_factura' => $request->precio_dolar_factura,
            ]);

            // IMPORTANTE: Cambia esto - usa tipo_factura_selector que viene del form
            if ($request->tipo_factura_selector === 'cliente') {
                $factura->id_cliente = $request->id_receptor;
                $factura->id_empresa_cliente = null;
                $factura->ruc_receptor = ClienteAuditoria::find($request->id_receptor)?->ruc;
            } else {
                $factura->id_empresa_cliente = $request->id_receptor;
                $factura->id_cliente = null;
                $factura->ruc_receptor = EmpresasClientes::find($request->id_receptor)?->ruc;
            }

            $factura->save();

            // Limpiar detalles actuales
            DetalleFactura::where('id_factura', $factura->id_factura)->delete();

            $total_bruto = 0;
            $peso_neto = 0;

            foreach ($request->productos as $idProd => $data) {
                $producto = Producto::findOrFail($idProd);
                $cantidad = $data['cantidad'];
                $precio_unitario = $data['precio'];

                $peso_unitario = $data['peso'] ?? 0;
                $unidad = $data['unidad'] ?? $producto->unidad_medida;

                // Actualizar el producto si hay cambios en precio, peso o unidad
                $updateData = [];

                // Precio
                if ($producto->precio_unitario != $precio_unitario) {
                    $updateData['precio_unitario'] = $precio_unitario;
                }

                // Peso
                if ($producto->peso != $peso_unitario) {
                    $updateData['peso'] = $peso_unitario;

                    Producto::where('nombre', $producto->nombre)
                        ->where('id_producto', '!=', $producto->id_producto)
                        ->update(['peso' => $peso_unitario]);
                }

                // Unidad de medida
                if ($unidad && $producto->unidad_medida !== $unidad) {
                    $updateData['unidad_medida'] = $unidad;

                    Producto::where('nombre', $producto->nombre)
                        ->where('id_producto', '!=', $producto->id_producto)
                        ->update(['unidad_medida' => $unidad]);
                }

                if (!empty($updateData)) {
                    $producto->update($updateData);
                }

                $precio_total = $precio_unitario * $cantidad;
                $peso_total = $peso_unitario * $cantidad;

                DetalleFactura::create([
                    'id_factura' => $factura->id_factura,
                    'id_producto' => $idProd,
                    'precio_unitario' => $precio_unitario,
                    'unidad_medida' => $unidad,
                    'cantidad' => $cantidad,
                    'precio_total' => $precio_total,
                    'peso_total' => $peso_total,
                ]);

                $total_bruto += $precio_total;
                $peso_neto += $peso_total;
            }

            $monto_retencion = $request->agente_retenedor === 'S' ? ($total_bruto * 0.03) : 0;
            $total_neto = $total_bruto - $monto_retencion;

            $factura->update([
                'total_bruto' => $total_bruto,
                'monto_retencion' => $monto_retencion,
                'total_neto' => $total_neto,
                'peso_neto' => $peso_neto,
            ]);

            // Verificar si el nombre ingresado coincide con el formato_final
            $queryFormato = FormatoNumeroFactura::query();
            if ($factura->id_empresa_cliente) {
                $queryFormato->where('id_empresas_clientes', $factura->id_empresa_cliente);
            } else {
                $queryFormato->where('id_cliente', $factura->id_cliente);
            }
            $formato = $queryFormato->latest('id_formato_numero_factura')->first();

            if ($formato && !empty($formato->formato_final)) {
                $partes = explode('{N}', $formato->formato_final);
                $patron = implode('(\d+)', array_map(fn($p) => preg_quote($p, '/'), $partes));
                $coincideConFormato = preg_match('/^' . $patron . '$/', $request->nombre_correlativo) ? true : false;

                if (($coincideConFormato || empty($request->nombre_correlativo)) && $factura->estado !== 'Facturada') {
                    $this->reordenarCorrelativosPendientes($factura->id_cliente, $factura->id_empresa_cliente);
                }
            } else {
                if ($factura->estado !== 'Facturada') {
                    $this->reordenarCorrelativosPendientes($factura->id_cliente, $factura->id_empresa_cliente);
                }
            }

            // Lógica de Solicitud en Update
            $queryFormatoSol = FormatoSolicitudes::query();
            if ($factura->id_empresa_cliente) {
                $queryFormatoSol->where('id_empresas_clientes', $factura->id_empresa_cliente);
            } else {
                $queryFormatoSol->where('id_cliente', $factura->id_cliente);
            }
            $formatoSol = $queryFormatoSol->latest('id_formato_solicitudes')->first();
            if ($formatoSol && !empty($formatoSol->formato_final)) {
                $partesSol = explode('{N}', $formatoSol->formato_final);
                $patronSol = implode('(\d+)', array_map(fn($p) => preg_quote($p, '/'), $partesSol));
                $coincideSol = preg_match('/^' . $patronSol . '$/', $request->numero_solicitud) ? true : false;

                if (($coincideSol || empty($request->numero_solicitud)) && $factura->estado !== 'Facturada') {
                    $this->reordenarSolicitudesPendientes($factura->id_cliente, $factura->id_empresa_cliente);
                }
            } else {
                if ($factura->estado !== 'Facturada') {
                    $this->reordenarSolicitudesPendientes($factura->id_cliente, $factura->id_empresa_cliente);
                }
            }

            // Reordenar Cotizaciones (v2)
            $emisorFactura = EmpresasClientes::where('ruc', $factura->ruc_emisor)->first();
            $idEmisor2 = $emisorFactura ? $emisorFactura->id_empresas_clientes : null;

            $formatoCoti2Up = $idEmisor2
                ? FormatoCotizaciones2::where('id_empresas_clientes_emisor', $idEmisor2)->first()
                : null;

            if ($formatoCoti2Up && !empty($formatoCoti2Up->formato_final)) {
                $partesCoti2Up = explode('{N}', $formatoCoti2Up->formato_final);
                $patronCoti2Up = implode('(\d+)', array_map(fn($p) => preg_quote($p, '/'), $partesCoti2Up));
                $coincideCoti2Up = preg_match('/^' . $patronCoti2Up . '$/', $request->nro_cotizacion) ? true : false;

                // Detectar si el usuario cambió manualmente el nro_cotizacion
                $usuarioCambioNroCoti = $request->nro_cotizacion !== $oldNroCotizacion
                    && !empty($request->nro_cotizacion);

                // Detectar si la fecha de emisión cambió
                $fechaEmisionCambio = $request->fecha_emision !== ($factura->getOriginal('fecha_emision') ?? $factura->fecha_emision);

                if ($usuarioCambioNroCoti) {
                    // El usuario cambió manualmente el nro_cotizacion: respetar su valor
                    // Solo actualizar el contador si coincide con el formato
                    if ($coincideCoti2Up) {
                        $regex2Up = '/^' . str_replace('\{N\}', '(\d+)', preg_quote($formatoCoti2Up->formato_final, '/')) . '$/';
                        if (preg_match($regex2Up, $request->nro_cotizacion, $m2Up)) {
                            $num2Up = (int) $m2Up[1];
                            if ($num2Up > $formatoCoti2Up->contador_actual) {
                                $formatoCoti2Up->contador_actual = $num2Up;
                                $formatoCoti2Up->save();
                            }
                        }
                    }
                } elseif (!$usuarioCambioNroCoti && !empty($oldNroCotizacion) && $coincideCoti2Up && !$fechaEmisionCambio) {
                    // El usuario NO cambió el nro_cotizacion y YA tenía uno válido y la fecha NO cambió:
                    // NO hacer nada, mantener el número tal cual está.
                    // (No recalcular para evitar que salte a otro número)
                } elseif (($coincideCoti2Up || empty($request->nro_cotizacion)) && $factura->estado !== 'Facturada') {
                    // El nro_cotizacion estaba vacío, o la fecha cambió: auto-asignar
                    $nroCotiAuto2 = FormatoCotizaciones2Controller::calcularSiguienteNro($formatoCoti2Up, $request->fecha_emision, $factura->id_factura);
                    if ($nroCotiAuto2 === 'FECHA_LLENA') {
                        return back()->withInput()->with('error', 'No hay números de cotización disponibles para la fecha ' . $request->fecha_emision . '. Todos los huecos están ocupados.');
                    }
                    $factura->nro_cotizacion = $nroCotiAuto2;
                    $regex2Up = '/^' . str_replace('\{N\}', '(\d+)', preg_quote($formatoCoti2Up->formato_final, '/')) . '$/';
                    if (preg_match($regex2Up, $nroCotiAuto2, $m2Up)) {
                        $num2Up = (int) $m2Up[1];
                        if ($num2Up > $formatoCoti2Up->contador_actual) {
                            $formatoCoti2Up->contador_actual = $num2Up;
                            $formatoCoti2Up->save();
                        }
                    }
                    $factura->save();
                } elseif ($factura->estado !== 'Facturada' && $coincideCoti2Up) {
                    $regex2Up = '/^' . str_replace('\{N\}', '(\d+)', preg_quote($formatoCoti2Up->formato_final, '/')) . '$/';
                    if (preg_match($regex2Up, $request->nro_cotizacion, $m2Up)) {
                        $num2Up = (int) $m2Up[1];
                        if ($num2Up > $formatoCoti2Up->contador_actual) {
                            $formatoCoti2Up->contador_actual = $num2Up;
                            $formatoCoti2Up->save();
                        }
                    }
                }
            }

            if ($oldIdCliente !== $factura->id_cliente || $oldIdEmpresaCliente !== $factura->id_empresa_cliente) {
                $this->reordenarCorrelativosPendientes($oldIdCliente, $oldIdEmpresaCliente);
                $this->reordenarCorrelativosCotiPendientes(
                    null,
                    $idEmisor2,
                    $factura->id_empresa_cliente,
                    $factura->id_cliente
                );
                $this->reordenarSolicitudesPendientes($oldIdCliente, $oldIdEmpresaCliente);
                FormatoNOrdenController::recalcularContador($oldIdCliente, $oldIdEmpresaCliente);
                FormatoNSolicitudController::recalcularContador($oldIdCliente, $oldIdEmpresaCliente);
            }

            FormatoNOrdenController::recalcularContador($factura->id_cliente, $factura->id_empresa_cliente);
            FormatoNSolicitudController::recalcularContador($factura->id_cliente, $factura->id_empresa_cliente);
            if ($idEmisor2) {
                FormatoCotizaciones2Controller::recalcularContador($idEmisor2);
            }
        });

        return redirect()->route('facturas.index')->with('success', 'Factura actualizada correctamente.');
    }

    public function verFacturasDetalladasPorReceptor($tipo, $id_receptor, Request $request)
    {
        $mes = $request->input('mes') ?? now()->format('m');
        $anio = $request->input('anio') ?? now()->format('Y');

        $inicioMes = \Carbon\Carbon::createFromDate($anio, $mes, 1)->startOfMonth();
        $finMes = \Carbon\Carbon::createFromDate($anio, $mes, 1)->endOfMonth();

        $facturas = Factura::with(['detalles.producto', 'empresa', 'cliente'])
            ->whereBetween('fecha_emision', [$inicioMes, $finMes])
            ->where(function ($query) use ($tipo, $id_receptor) {
                if ($tipo === 'empresa') {
                    $query->where('id_empresa_cliente', $id_receptor);
                } else {
                    $query->where('id_cliente', $id_receptor);
                }
            })
            ->get();

        $nombreReceptor = optional($facturas->first()?->empresa)->razon_social ?? optional($facturas->first()?->cliente)->razon_social ?? 'Receptor';

        $meses_es = [
            '01' => 'Enero',
            '02' => 'Febrero',
            '03' => 'Marzo',
            '04' => 'Abril',
            '05' => 'Mayo',
            '06' => 'Junio',
            '07' => 'Julio',
            '08' => 'Agosto',
            '09' => 'Septiembre',
            '10' => 'Octubre',
            '11' => 'Noviembre',
            '12' => 'Diciembre',
        ];

        return view('facturas.detalle_mes', compact('facturas', 'nombreReceptor', 'mes', 'meses_es', 'anio'));
    }

    public function destroy($id)
    {
        $factura = Factura::with('detalles')->findOrFail($id);

        if ($factura->estado === 'Facturada') {
            return back()->with('error', 'No se puede eliminar una factura ya Facturada.');
        }

        DB::transaction(function () use ($factura) {
            $idCliente = $factura->id_cliente;
            $idEmpresaCliente = $factura->id_empresa_cliente;
            // Eliminar detalles
            $factura->detalles()->delete();

            // Eliminar factura
            $factura->delete();

            // Reorder remaining pendings
            $this->reordenarCorrelativosPendientes($idCliente, $idEmpresaCliente);
            $this->reordenarSolicitudesPendientes($idCliente, $idEmpresaCliente);
            $emisor = EmpresasClientes::where('ruc', $factura->ruc_emisor)->first();
            $idEmisor = $emisor ? $emisor->id_empresas_clientes : null;
            $this->reordenarCorrelativosCotiPendientes(
                null,
                $idEmisor,
                $factura->id_empresa_cliente,
                $factura->id_cliente
            );

            // Recalcular contadores al número máximo de las facturas activas restantes
            FormatoNOrdenController::recalcularContador($idCliente, $idEmpresaCliente);
            FormatoNSolicitudController::recalcularContador($idCliente, $idEmpresaCliente);
            if ($idEmisor) {
                FormatoCotizaciones2Controller::recalcularContador($idEmisor);
            }
        });

        return redirect()->route('facturas.index')->with('success', 'Factura eliminada correctamente.');
    }

    public function verFacturasMensualesProveedor($ruc_emisor, Request $request)
    {
        $proveedor = EmpresasClientes::where('ruc', $ruc_emisor)->firstOrFail();

        $mes = $request->input('mes', now()->format('m'));
        $anio = $request->input('anio', now()->format('Y'));

        $inicioMes = Carbon::createFromDate($anio, $mes, 1)->startOfMonth();
        $finMes = Carbon::createFromDate($anio, $mes, 1)->endOfMonth();

        $facturas = Factura::with(['empresa', 'cliente'])
            ->where('ruc_emisor', $ruc_emisor)
            ->whereBetween('fecha_emision', [$inicioMes, $finMes])
            ->whereIn('estado', ['Facturada', 'Pendiente'])
            ->orderByDesc('fecha_emision')
            ->get();

        $empresas = EmpresasClientes::all()->keyBy('ruc');
        $clientes = ClienteAuditoria::all()->keyBy('ruc');
        $inventarios = Inventario::all();

        return view('facturas.mensual_proveedor', compact(
            'proveedor',
            'facturas',
            'empresas',
            'clientes',
            'inventarios',
            'mes',
            'anio'
        ));
    }

    public function verFacturacionMensual(Request $request)
    {
        $mes = $request->input('mes', now()->format('m'));
        $anio = $request->input('anio', now()->format('Y'));

        $inicioMes = Carbon::createFromDate($anio, $mes, 1)->startOfMonth();
        $finMes = Carbon::createFromDate($anio, $mes, 1)->endOfMonth();

        // ✅ MES ANTERIOR (maneja Enero -> Diciembre del año previo)
        $prev = Carbon::createFromDate($anio, $mes, 1)->subMonth();
        $mesPrev = (int) $prev->format('m');
        $anioPrev = (int) $prev->format('Y');
        $inicioPrev = $prev->copy()->startOfMonth();
        $finPrev = $prev->copy()->endOfMonth();

        $empresas = EmpresasClientes::with('inventarios')
            ->select('id_empresas_clientes', 'razon_social', 'ruc', 'estado')
            ->whereIn('estado', ['A1', 'R1', 'R2'])
            ->where('estado_actual', ['1'])
            ->where(function ($query) {
                $query->whereHas('relacionesOrigen')
                    ->orWhereHas('relacionesDestino');
            })
            ->get()
            ->map(function ($empresa) use ($inicioMes, $finMes, $mes, $anio, $inicioPrev, $finPrev, $mesPrev, $anioPrev) {

                // =======================
                // MES ACTUAL (igual que ya haces)
                // =======================
                $facturasEmitidas = Factura::where('ruc_emisor', $empresa->ruc)
                    ->whereBetween('fecha_emision', [$inicioMes, $finMes])
                    ->whereIn('estado', ['Facturada', 'Pendiente'])
                    ->get();

                $facturasRecibidas = Factura::where('ruc_receptor', $empresa->ruc)
                    ->whereBetween('fecha_emision', [$inicioMes, $finMes])
                    ->whereIn('estado', ['Facturada', 'Pendiente'])
                    ->get();

                $empresa->total_mes = $facturasEmitidas->sum(fn($f) => $f->monto_facturable);
                $empresa->total_recibido_mes = $facturasRecibidas->sum(fn($f) => $f->monto_facturable);

                // IGV guardado en DB (tu campo monto)
                // =======================
                // CRÉDITO FISCAL MES ACTUAL
                // =======================
                $creditoFiscalQuery = CreditoFiscalTotal::where('id_empresas_clientes', $empresa->id_empresas_clientes)
                    ->where('mes', (int) $mes)
                    ->where('anio', (int) $anio);

                // puede ser NULL si no existe registro
                $empresa->credito_fiscal_mes = $creditoFiscalQuery->value('monto');

                // bandera CLARA para la vista
                $empresa->tiene_credito_fiscal = $creditoFiscalQuery->exists();


                // =======================
                // MES ANTERIOR (para “Total a Facturar” anterior)
                // =======================
                $emitidasPrev = Factura::where('ruc_emisor', $empresa->ruc)
                    ->whereBetween('fecha_emision', [$inicioPrev, $finPrev])
                    ->whereIn('estado', ['Facturada', 'Pendiente'])
                    ->get();

                $recibidasPrev = Factura::where('ruc_receptor', $empresa->ruc)
                    ->whereBetween('fecha_emision', [$inicioPrev, $finPrev])
                    ->whereIn('estado', ['Facturada', 'Pendiente'])
                    ->get();

                $totalMesPrev = $emitidasPrev->sum(fn($f) => $f->monto_facturable);
                $totalRecibidoPrev = $recibidasPrev->sum(fn($f) => $f->monto_facturable);

                $creditoFiscalIgvPrev = CreditoFiscalTotal::where('id_empresas_clientes', $empresa->id_empresas_clientes)
                    ->where('mes', (int) $mesPrev)
                    ->where('anio', (int) $anioPrev)
                    ->value('monto') ?? 0;

                $totalCreditoConBasePrev = round(($creditoFiscalIgvPrev / 0.18) + $creditoFiscalIgvPrev, 0);

                $totalFacturarPrev = ($totalMesPrev - $totalRecibidoPrev) - $totalCreditoConBasePrev;

                // ✅ Guardamos el dato para mandarlo al modal
                $empresa->total_facturar_prev = round($totalFacturarPrev, 2);

                // ✅ y una “sugerencia” en IGV si quieres rellenar rápido:
                // Si total a facturar prev es negativo, interpretamos “saldo a favor” como total con IGV
                // IGV del total: total - total/1.18
                $saldoPrevAbs = abs($totalFacturarPrev);
                $empresa->saldo_prev_igv_sugerido = ($totalFacturarPrev < 0)
                    ? round($saldoPrevAbs - ($saldoPrevAbs / 1.18), 2)
                    : 0;

                return $empresa;
            });


        return view('facturas.mes_facturacion', compact(
            'empresas',
            'mes',
            'anio'
        ));
    }

    public function guardarCreditoFiscal(Request $request)
    {
        $data = $request->validate([
            'id_empresas_clientes' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'monto' => 'required|numeric|min:0', // IGV REAL
            'mes' => 'required|integer|between:1,12',
            'anio' => 'required|integer|min:2000',
        ]);


        CreditoFiscalTotal::updateOrCreate(
            [
                'id_empresas_clientes' => $data['id_empresas_clientes'],
                'mes' => $data['mes'],
                'anio' => $data['anio'],
            ],
            [
                'id_usuario' => auth()->id(),
                'monto' => $data['monto'],
            ]
        );

        return redirect()->back()->with('success', 'Crédito fiscal registrado correctamente');
    }

    public function getRelacionesEmpresa(Request $request)
    {
        $idEmpresa = $request->input('id_empresa');
        $mes = $request->input('mes', now()->format('m'));
        $anio = $request->input('anio', now()->format('Y'));

        $inicioMes = Carbon::createFromDate($anio, $mes, 1)->startOfMonth();
        $finMes = Carbon::createFromDate($anio, $mes, 1)->endOfMonth();

        // 1. Encontrar la empresa seleccionada
        $empresaSeleccionada = EmpresasClientes::findOrFail($idEmpresa);

        // 2. Encontrar el "ROOT R2" de esta cadena
        $rootR2 = null;

        if ($empresaSeleccionada->estado == 'R2') {
            $rootR2 = $empresaSeleccionada;
        } elseif ($empresaSeleccionada->estado == 'R1') {
            // Buscar quien es su padre R2
            // R2 -> R1 (Origen -> Destino)
            // Entonces buscamos donde Destino == R1
            $relacion = RelacionFacturacion::where('id_empresa_destino', $empresaSeleccionada->id_empresas_clientes)->first();
            if ($relacion && $relacion->origen->estado == 'R2') {
                $rootR2 = $relacion->origen;
            }
        } elseif ($empresaSeleccionada->estado == 'A1') {
            // A1 -> padre R1 -> abuelo R2
            $relacion1 = RelacionFacturacion::where('id_empresa_destino', $empresaSeleccionada->id_empresas_clientes)->first();
            if ($relacion1 && $relacion1->origen->estado == 'R1') {
                $padreR1 = $relacion1->origen;
                $relacion2 = RelacionFacturacion::where('id_empresa_destino', $padreR1->id_empresas_clientes)->first();
                if ($relacion2 && $relacion2->origen->estado == 'R2') {
                    $rootR2 = $relacion2->origen;
                }
            }
        }

        // Si no encontramos cadena válida (quizás es huérfana), mostramos solo la seleccionada o vacío.
        $empresasParaMostrar = collect([]);
        if ($rootR2) {
            // Bajamos en cascada desde R2
            $empresasParaMostrar->push($rootR2);

            // Buscar hijos R1
            $relsR1 = RelacionFacturacion::where('id_empresa_origen', $rootR2->id_empresas_clientes)->get();
            foreach ($relsR1 as $relR1) {
                $r1 = $relR1->destino;
                if ($r1 && $r1->estado == 'R1') {
                    $empresasParaMostrar->push($r1);

                    // Buscar hijos A1 de este R1
                    $relsA1 = RelacionFacturacion::where('id_empresa_origen', $r1->id_empresas_clientes)->get();
                    foreach ($relsA1 as $relA1) {
                        $a1 = $relA1->destino;
                        if ($a1 && $a1->estado == 'A1') {
                            $empresasParaMostrar->push($a1);
                        }
                    }
                }
            }
        } else {
            // Fallback: solo la seleccionada
            $empresasParaMostrar->push($empresaSeleccionada);
        }

        // 3. Obtener Data Financiera COMPLETA para todas
        // Primero calculamos todo para tener objetos completos disponibles
        $calculatedItems = $empresasParaMostrar->unique('id_empresas_clientes')->map(function ($empresa) use ($inicioMes, $finMes, $mes, $anio) {

            $facturasEmitidas = Factura::where('ruc_emisor', $empresa->ruc)
                ->whereBetween('fecha_emision', [$inicioMes, $finMes])
                ->whereIn('estado', ['Facturada', 'Pendiente'])
                ->get();
            $totalEmitido = $facturasEmitidas->sum(fn($f) => $f->monto_facturable ?? $f->total_neto);

            $facturasRecibidas = Factura::where('ruc_receptor', $empresa->ruc)
                ->whereBetween('fecha_emision', [$inicioMes, $finMes])
                ->whereIn('estado', ['Facturada', 'Pendiente'])
                ->get();
            $totalRecibido = $facturasRecibidas->sum(fn($f) => $f->monto_facturable ?? $f->total_neto);

            $creditoFiscalQuery = CreditoFiscalTotal::where('id_empresas_clientes', $empresa->id_empresas_clientes)
                ->where('mes', (int) $mes)
                ->where('anio', (int) $anio);

            $creditoFiscal = $creditoFiscalQuery->value('monto') ?? 0;
            $tieneCredito = $creditoFiscalQuery->exists();

            $totalCreditoConBase = 0;
            if ($tieneCredito) {
                $totalCreditoConBase = round($creditoFiscal / 0.18 + $creditoFiscal, 0);
            }

            $totalFactura = $totalEmitido - $totalRecibido - $totalCreditoConBase;

            return [
                'id' => $empresa->id_empresas_clientes,
                'razon_social' => $empresa->razon_social,
                'ruc' => $empresa->ruc,
                'total_emitido' => number_format($totalEmitido, 2),
                'total_recibido' => number_format($totalRecibido, 2),
                'credito_fiscal' => $tieneCredito ? number_format($totalCreditoConBase, 2) : 'Sin registrar',
                'tiene_credito' => $tieneCredito,
                'total_factura' => number_format($totalFactura, 2),
                'clase_total' => $totalFactura > 0 ? 'text-red-600 font-bold bg-red-100' : 'text-gray-600 bg-gray-50',
                'inventario_id' => $empresa->inventarios->first()?->id_inventario,
                'estado' => $empresa->estado,
                'empresas_relacionadas' => [] // Se llenará después
            ];
        })->keyBy('id'); // Indexar por ID para búsqueda rápida

        // 4. Estructurar respuesta y asignar relaciones
        $data = [
            'R2' => [],
            'R1' => [],
            'A1' => [],
        ];

        foreach ($calculatedItems as $id => $item) {
            // Si es R1, buscamos sus hijos A1 y adjuntamos EL OBJETO COMPLETO ya calculado
            if ($item['estado'] == 'R1') {
                $childIds = RelacionFacturacion::where('id_empresa_origen', $id)
                    ->pluck('id_empresa_destino');

                $fullChildren = [];
                foreach ($childIds as $childId) {
                    if (isset($calculatedItems[$childId])) {
                        $fullChildren[] = $calculatedItems[$childId];
                    }
                }
                $item['empresas_relacionadas'] = $fullChildren;
            }

            if (isset($data[$item['estado']])) {
                $data[$item['estado']][] = $item;
            }
        }

        return response()->json($data);
    }

    public function extraerContadorDeCorrelativo($nombreCorrelativo, $formato)
    {
        if (!$formato || empty($formato->formato_final))
            return null;

        // Dividir por {N}, preg_quote cada parte, unir con (\d+)
        $partes = explode('{N}', $formato->formato_final);
        $patron = implode('(\d+)', array_map(fn($p) => preg_quote($p, '/'), $partes));

        if (preg_match('/' . $patron . '/', $nombreCorrelativo, $matches) && isset($matches[1])) {
            return (int) $matches[1];
        }
        return null;
    }

    public function extraerContadorDeSolicitud($numeroSolicitud, $formato)
    {
        if (!$formato || empty($formato->formato_final))
            return null;

        $partes = explode('{N}', $formato->formato_final);
        $patron = implode('(\d+)', array_map(fn($p) => preg_quote($p, '/'), $partes));

        if (preg_match('/' . $patron . '/', $numeroSolicitud, $matches) && isset($matches[1])) {
            return (int) $matches[1];
        }
        return null;
    }

    public function generarNombreConContador($formato, $contador)
    {
        $contadorPad = $formato->contador_padding > 0
            ? str_pad($contador, $formato->contador_padding, '0', STR_PAD_LEFT)
            : $contador;

        return str_replace('{N}', $contadorPad, $formato->formato_final);
    }

    /*Reordena los correlativos de todas las facturas Pendientes de un receptor basándose en la fecha de emisión, asegurando que sigan a las Facturadas. */
    public function reordenarCorrelativosPendientes($id_cliente, $id_empresa_cliente)
    {
        $queryFormato = FormatoNumeroFactura::query();
        if ($id_empresa_cliente) {
            $queryFormato->where('id_empresas_clientes', $id_empresa_cliente);
        } else {
            $queryFormato->where('id_cliente', $id_cliente);
        }
        $formato = $queryFormato->latest('id_formato_numero_factura')->first();
        if (!$formato)
            return;
        $formato->refresh();

        // Reutiliza la misma lógica que insertarCorrelativoEnPosicion
        $orden = $this->calcularOrdenCorrelativos($id_cliente, $id_empresa_cliente, $formato);
        if ($orden->isEmpty()) {
            FormatoNOrdenController::recalcularContador($id_cliente, $id_empresa_cliente, $formato);
            return;
        }

        // Pasada 1: temporales para evitar colisiones
        foreach ($orden as $item) {
            $f = $item['factura'];
            $temp = 'PENDIENTE-REORDER-' . $f->id_factura;
            if ($f->nombre_correlativo !== $temp) {
                $f->nombre_correlativo = $temp;
                $f->save();
            }
        }

        // Pasada 2: asignar nombre final
        foreach ($orden as $item) {
            $f = $item['factura'];
            $f->nombre_correlativo = $item['nombre'];
            $f->save();
        }

        FormatoNOrdenController::recalcularContador($id_cliente, $id_empresa_cliente, $formato);
    }

    public function reordenarSolicitudesPendientes($id_cliente, $id_empresa_cliente)
    {
        $queryFormato = FormatoSolicitudes::query();
        if ($id_empresa_cliente) {
            $queryFormato->where('id_empresas_clientes', $id_empresa_cliente);
        } else {
            $queryFormato->where('id_cliente', $id_cliente);
        }
        $formato = $queryFormato->latest('id_formato_solicitudes')->first();
        if (!$formato)
            return;
        $formato->refresh();

        $orden = $this->calcularOrdenSolicitudes($id_cliente, $id_empresa_cliente, $formato);
        if ($orden->isEmpty()) {
            FormatoNSolicitudController::recalcularContador($id_cliente, $id_empresa_cliente, $formato);
            return;
        }

        foreach ($orden as $item) {
            $f = $item['factura'];
            $temp = 'SOLICITUD-REORDER-' . $f->id_factura;
            if ($f->numero_solicitud !== $temp) {
                $f->numero_solicitud = $temp;
                $f->save();
            }
        }

        foreach ($orden as $item) {
            $f = $item['factura'];
            $f->numero_solicitud = $item['nombre'];
            $f->save();
        }

        FormatoNSolicitudController::recalcularContador($id_cliente, $id_empresa_cliente, $formato);
    }

    /** AJAX: Previsualizar el correlativo basado en la fecha y el receptor.*/
    public function previsualizarCorrelativo(Request $request)
    {
        $fechaEmision = $request->input('fecha_emision');
        $idReceptor = $request->input('id_receptor');
        $origenReceptor = $request->input('origen_receptor');
        $idFacturaActual = $request->input('id_factura');

        if (!$fechaEmision || !$idReceptor || !$origenReceptor) {
            return response()->json([
                'nombre_correlativo' => 'Falta información',
                'nro_cotizacion' => 'Falta información'
            ]);
        }

        $id_cliente = ($origenReceptor === 'cliente') ? $idReceptor : null;
        $id_empresa_cliente = ($origenReceptor === 'empresa') ? $idReceptor : null;
        $id_emisor_contexto = $request->input('id_empresa_contexto');

        // PREVISUALIZAR NOMBRE_CORRELATIVO (Nro Orden)
        $queryFormato = FormatoNumeroFactura::query();
        if ($id_empresa_cliente) {
            $queryFormato->where('id_empresas_clientes', $id_empresa_cliente);
        } else {
            $queryFormato->where('id_cliente', $id_cliente);
        }
        $formato = $queryFormato->latest('id_formato_numero_factura')->first();

        $nombrePrevisualizado = 'SIN FORMATO';
        $nombrePrevisualizado = 'SIN FORMATO';
        if ($formato) {
            if ($idFacturaActual && ($facturaReal = Factura::find($idFacturaActual)) && $facturaReal->estado === 'Facturada') {
                $nombrePrevisualizado = $facturaReal->nombre_correlativo;
            } else {
                $falsa = new Factura();
                $falsa->id_factura = $idFacturaActual ?? 999999999;
                $falsa->fecha_emision = $fechaEmision;
                $falsa->estado = 'Pendiente';
                $falsa->id_cliente = $id_cliente;
                $falsa->id_empresa_cliente = $id_empresa_cliente;
                $falsa->nombre_correlativo = 'PENDIENTE-SIM';
                $orden = $this->calcularOrdenCorrelativos($id_cliente, $id_empresa_cliente, $formato, $falsa);
                foreach ($orden as $item) {
                    if ($item['factura']->id_factura == $falsa->id_factura) {
                        $nombrePrevisualizado = $item['nombre'];
                        break;
                    }
                }
            }
        }

        $formatoCoti2Prev = FormatoCotizaciones2::where('id_empresas_clientes_emisor', $id_emisor_contexto)->first();

        $nroCotiPrevisualizado = 'SIN FORMATO';
        if ($formatoCoti2Prev) {
            if ($idFacturaActual && ($facturaReal = Factura::find($idFacturaActual)) && $facturaReal->estado === 'Facturada') {
                $nroCotiPrevisualizado = $facturaReal->nro_cotizacion;
            } else {
                // Calcular el siguiente número según reglas de salto/hueco
                $nroCotiPrevisualizado = FormatoCotizaciones2Controller::calcularSiguienteNro($formatoCoti2Prev, $fechaEmision, $idFacturaActual ? (int)$idFacturaActual : null);
                if ($nroCotiPrevisualizado === 'FECHA_LLENA') {
                    $nroCotiPrevisualizado = 'Números no disponibles';
                }
            }
        }

        // PREVISUALIZAR NUMERO_SOLICITUD
        $queryFormatoSol = FormatoSolicitudes::query();
        if ($id_empresa_cliente) {
            $queryFormatoSol->where('id_empresas_clientes', $id_empresa_cliente);
        } else {
            $queryFormatoSol->where('id_cliente', $id_cliente);
        }
        $formatoSol = $queryFormatoSol->latest('id_formato_solicitudes')->first();

        $solicitudPrevisualizada = 'SIN FORMATO';
        if ($formatoSol) {
            if ($idFacturaActual && ($facturaReal = Factura::find($idFacturaActual)) && $facturaReal->estado === 'Facturada') {
                $solicitudPrevisualizada = $facturaReal->numero_solicitud;
            } else {
                $falsaSol = new Factura();
                $falsaSol->id_factura = $idFacturaActual ?? 999999999;
                $falsaSol->fecha_emision = $fechaEmision;
                $falsaSol->estado = 'Pendiente';
                $falsaSol->id_cliente = $id_cliente;
                $falsaSol->id_empresa_cliente = $id_empresa_cliente;
                $falsaSol->numero_solicitud = 'SOLICITUD-SIM';
                $ordenSol = $this->calcularOrdenSolicitudes($id_cliente, $id_empresa_cliente, $formatoSol, $falsaSol);
                foreach ($ordenSol as $item) {
                    if ($item['factura']->id_factura == $falsaSol->id_factura) {
                        $solicitudPrevisualizada = $item['nombre'];
                        break;
                    }
                }
            }
        }

        return response()->json([
            'nombre_correlativo' => $nombrePrevisualizado,
            'nro_cotizacion' => $nroCotiPrevisualizado,
            'numero_solicitud' => $solicitudPrevisualizada
        ]);
    }

    public function reordenarTodosLosCorrelativos($id_cliente, $id_empresa_cliente)
    {
        $queryFormato = FormatoNumeroFactura::query();
        if ($id_empresa_cliente) {
            $queryFormato->where('id_empresas_clientes', $id_empresa_cliente);
        } else {
            $queryFormato->where('id_cliente', $id_cliente);
        }
        $formato = $queryFormato->latest('id_formato_numero_factura')->first();
        if (!$formato)
            return;
        $formato->refresh();

        $facturas = Factura::query()
            ->when($id_empresa_cliente, fn($q) => $q->where('id_empresa_cliente', $id_empresa_cliente))
            ->when(!$id_empresa_cliente, fn($q) => $q->where('id_cliente', $id_cliente))
            ->whereNotIn('estado', ['Anulada'])
            ->whereNotNull('nombre_correlativo')
            ->where('nombre_correlativo', 'not like', 'PENDIENTE-%')
            ->get()
            ->filter(fn($f) => $this->extraerContadorDeCorrelativo($f->nombre_correlativo, $formato) !== null
                || $f->estado === 'Pendiente')
            ->sortBy(fn($f) => [Carbon::parse($f->fecha_emision)->timestamp, $f->id_factura])
            ->values();

        $prefijosAgrupados = $facturas->groupBy(fn($f) => $this->extraerPrefijo($f->nombre_correlativo, $formato));
        $maxContadorGlobal = 0;

        foreach ($prefijosAgrupados as $prefijo => $grupoFacturas) {
            $contadores = $grupoFacturas
                ->filter(fn($f) => $f->estado === 'Facturada')
                ->map(fn($f) => $this->extraerContadorDeCorrelativo($f->nombre_correlativo, $formato))
                ->filter()
                ->sort()
                ->values();

            $inicioContador = $contadores->isNotEmpty() ? $contadores->last() + 1 : 1;
            $contadoresPendientes = $grupoFacturas
                ->filter(fn($f) => $f->estado === 'Pendiente')
                ->map(fn($f) => $this->extraerContadorDeCorrelativo($f->nombre_correlativo, $formato))
                ->filter()
                ->sort()
                ->values();

            if ($contadoresPendientes->isNotEmpty() && $contadoresPendientes->first() > $inicioContador) {
                // Si el inicio actual es 1 pero tienen 354, y no hay facturadas, respetamos 354
                if ($contadores->isEmpty()) {
                    $inicioContador = $contadoresPendientes->first();
                }
            }

            $contador = $inicioContador - 1;

            foreach ($grupoFacturas as $f) {
                $contador++;
                $nuevoNombre = $this->generarNombreConContador($formato, $contador);
                if ($f->nombre_correlativo !== $nuevoNombre) {
                    $f->nombre_correlativo = $nuevoNombre;
                    $f->save();
                }
            }

            if ($contador > $maxContadorGlobal) {
                $maxContadorGlobal = $contador;
            }
        }

        if ($maxContadorGlobal !== $formato->contador_actual) {
            $formato->contador_actual = $maxContadorGlobal;
            $formato->save();
        }
    }

    private function extraerPrefijo($nombreCorrelativo, $formato)
    {
        if (!$formato || empty($formato->formato_final))
            return null;

        $patron = str_replace('{N}', '(\d+)', preg_quote($formato->formato_final, '/'));
        if (preg_match('/' . $patron . '/', $nombreCorrelativo, $matches) && isset($matches[1])) {
            return str_replace($matches[1], '', $nombreCorrelativo);
        }
        return null;
    }

    private function calcularOrdenCorrelativos($id_cliente, $id_empresa_cliente, $formato, $facturaSimulada = null)
    {
        $facturas = Factura::query()
            ->when($id_empresa_cliente, fn($q) => $q->where('id_empresa_cliente', $id_empresa_cliente))
            ->when(!$id_empresa_cliente, fn($q) => $q->where('id_cliente', $id_cliente))
            ->whereNotIn('estado', ['Anulada'])
            ->whereNotNull('nombre_correlativo')
            ->get()
            ->filter(fn($f) => str_starts_with($f->nombre_correlativo, 'PENDIENTE-')
                || $this->extraerContadorDeCorrelativo($f->nombre_correlativo, $formato) !== null);

        if ($facturaSimulada) {
            $facturas = $facturas->where('id_factura', '!=', $facturaSimulada->id_factura);
            $facturas = $facturas->concat([$facturaSimulada]);
        }

        // Facturadas = anclas fijas, sus números no se tocan
        $facturadas = $facturas->where('estado', 'Facturada');
        $numerosFacturadas = $facturadas
            ->map(fn($f) => $this->extraerContadorDeCorrelativo($f->nombre_correlativo, $formato))
            ->filter()
            ->values();
        $numerosOcupados = $numerosFacturadas->toArray();

        // Pendientes ordenadas por fecha
        $pendientes = $facturas->where('estado', '!=', 'Facturada')
            ->sortBy(fn($f) => [Carbon::parse($f->fecha_emision)->timestamp, $f->id_factura])
            ->values();

        // Base = Número siguiente a la última Facturada, o el inicio del bloque pendiente actual
        $maxFacturada = $numerosFacturadas->isNotEmpty() ? $numerosFacturadas->max() : 0;
        if ($formato && $formato->contador_actual > $maxFacturada) {
            $maxFacturada = $formato->contador_actual;
        }
        $numerosPendientesActuales = $pendientes
            ->map(fn($f) => $this->extraerContadorDeCorrelativo($f->nombre_correlativo, $formato))
            ->filter()
            ->sort()
            ->values();

        $base = $maxFacturada + 1;
        if ($numerosPendientesActuales->isNotEmpty()) {
            if ($maxFacturada === 0 || $numerosPendientesActuales->first() < $base) {
                $base = $numerosPendientesActuales->first();
            }
        } elseif ($maxFacturada === 0) {
            $base = 1;
        }

        // Generar números disponibles: secuenciales desde base, saltando los ocupados por Facturadas
        $numerosDisponibles = collect();
        $num = $base;
        while ($numerosDisponibles->count() < $pendientes->count()) {
            if (!in_array($num, $numerosOcupados)) {
                $numerosDisponibles->push($num);
            }
            $num++;
        }

        $resultado = collect();

        // Facturadas con su número actual intacto
        foreach ($facturadas as $f) {
            $c = $this->extraerContadorDeCorrelativo($f->nombre_correlativo, $formato);
            $resultado->push([
                'factura' => $f,
                'numero' => $c,
                'nombre' => $f->nombre_correlativo,
            ]);
        }

        // Pendientes toman los números disponibles in orden de fecha
        foreach ($pendientes as $i => $f) {
            $numero = $numerosDisponibles->get($i);
            $resultado->push([
                'factura' => $f,
                'numero' => $numero,
                'nombre' => $this->generarNombreConContador($formato, $numero),
            ]);
        }

        return $resultado;
    }

    private function calcularOrdenSolicitudes($id_cliente, $id_empresa_cliente, $formato, $facturaSimulada = null)
    {
        $facturas = Factura::query()
            ->when($id_empresa_cliente, fn($q) => $q->where('id_empresa_cliente', $id_empresa_cliente))
            ->when(!$id_empresa_cliente, fn($q) => $q->where('id_cliente', $id_cliente))
            ->whereNotIn('estado', ['Anulada'])
            ->whereNotNull('numero_solicitud')
            ->get()
            ->filter(fn($f) => str_starts_with($f->numero_solicitud, 'SOLICITUD-')
                || $this->extraerContadorDeSolicitud($f->numero_solicitud, $formato) !== null);

        if ($facturaSimulada) {
            $facturas = $facturas->where('id_factura', '!=', $facturaSimulada->id_factura);
            $facturas = $facturas->concat([$facturaSimulada]);
        }

        $facturadas = $facturas->where('estado', 'Facturada');
        $numerosFacturadas = $facturadas
            ->map(fn($f) => $this->extraerContadorDeSolicitud($f->numero_solicitud, $formato))
            ->filter()
            ->values();

        $numerosOcupados = $numerosFacturadas->toArray();
        $pendientes = $facturas->where('estado', '!=', 'Facturada')
            ->sortBy(fn($f) => [Carbon::parse($f->fecha_emision)->timestamp, $f->id_factura])
            ->values();

        $maxFacturada = $numerosFacturadas->isNotEmpty() ? $numerosFacturadas->max() : 0;
        if ($formato && $formato->contador_actual > $maxFacturada) {
            $maxFacturada = $formato->contador_actual;
        }
        $numerosPendientesActuales = $pendientes
            ->map(fn($f) => $this->extraerContadorDeSolicitud($f->numero_solicitud, $formato))
            ->filter()
            ->sort()
            ->values();

        $base = $maxFacturada + 1;
        if ($numerosPendientesActuales->isNotEmpty()) {
            if ($maxFacturada === 0 || $numerosPendientesActuales->first() < $base) {
                $base = $numerosPendientesActuales->first();
            }
        } elseif ($maxFacturada === 0) {
            $base = 1;
        }

        $numerosDisponibles = collect();
        $num = $base;
        while ($numerosDisponibles->count() < $pendientes->count()) {
            if (!in_array($num, $numerosOcupados)) {
                $numerosDisponibles->push($num);
            }
            $num++;
        }

        $resultado = collect();

        foreach ($facturadas as $f) {
            $c = $this->extraerContadorDeSolicitud($f->numero_solicitud, $formato);
            $resultado->push([
                'factura' => $f,
                'numero' => $c,
                'nombre' => $f->numero_solicitud,
            ]);
        }

        foreach ($pendientes as $i => $f) {
            $numero = $numerosDisponibles->get($i);
            $resultado->push([
                'factura' => $f,
                'numero' => $numero,
                'nombre' => $this->generarNombreConContador($formato, $numero),
            ]);
        }

        return $resultado;
    }

    public function insertarCorrelativoEnPosicion($id_cliente, $id_empresa_cliente, $idFacturaNueva)
    {
        $queryFormato = FormatoNumeroFactura::query();
        if ($id_empresa_cliente) {
            $queryFormato->where('id_empresas_clientes', $id_empresa_cliente);
        } else {
            $queryFormato->where('id_cliente', $id_cliente);
        }
        $formato = $queryFormato->latest('id_formato_numero_factura')->first();
        if (!$formato)
            return;
        $formato->refresh();

        $orden = $this->calcularOrdenCorrelativos($id_cliente, $id_empresa_cliente, $formato);
        if ($orden->isEmpty())
            return;

        // PASADA 1: poner todas en PENDIENTE- para evitar colisiones de unique
        foreach ($orden as $item) {
            $f = $item['factura'];
            $temp = 'PENDIENTE-REORDER-' . $f->id_factura;
            if ($f->nombre_correlativo !== $temp) {
                $f->nombre_correlativo = $temp;
                $f->save();
            }
        }

        // PASADA 2: asignar nombre final
        $maxContador = 0;
        foreach ($orden as $item) {
            $f = $item['factura'];
            $f->nombre_correlativo = $item['nombre'];
            $f->save();

            if ($item['numero'] > $maxContador) {
                $maxContador = $item['numero'];
            }
        }

        if ($maxContador !== $formato->contador_actual) {
            $formato->contador_actual = $maxContador;
            $formato->save();
        }
    }

    public function insertarSolicitudEnPosicion($id_cliente, $id_empresa_cliente, $idFacturaNueva)
    {
        $queryFormato = FormatoSolicitudes::query();
        if ($id_empresa_cliente) {
            $queryFormato->where('id_empresas_clientes', $id_empresa_cliente);
        } else {
            $queryFormato->where('id_cliente', $id_cliente);
        }
        $formato = $queryFormato->latest('id_formato_solicitudes')->first();
        if (!$formato)
            return;
        $formato->refresh();

        $orden = $this->calcularOrdenSolicitudes($id_cliente, $id_empresa_cliente, $formato);
        if ($orden->isEmpty())
            return;

        foreach ($orden as $item) {
            $f = $item['factura'];
            $temp = 'SOLICITUD-REORDER-' . $f->id_factura;
            if ($f->numero_solicitud !== $temp) {
                $f->numero_solicitud = $temp;
                $f->save();
            }
        }

        $maxContador = 0;
        foreach ($orden as $item) {
            $f = $item['factura'];
            $f->numero_solicitud = $item['nombre'];
            $f->save();
            if ($item['numero'] > $maxContador) {
                $maxContador = $item['numero'];
            }
        }

        if ($maxContador !== $formato->contador_actual) {
            $formato->contador_actual = $maxContador;
            $formato->save();
        }
    }

    public function checkCorrelativoUnico(Request $request)
    {
        $nombreCorrelativo = $request->input('nombre_correlativo');
        $idFacturaActual = $request->input('id_factura'); // para edición

        if (!$nombreCorrelativo) {
            return response()->json(['unico' => true]);
        }

        // Verificar si coincide con algún formato_final registrado
        $formatos = FormatoNumeroFactura::all();
        $esDeFormato = false;

        foreach ($formatos as $formato) {
            if (empty($formato->formato_final))
                continue;

            $partes = explode('{N}', $formato->formato_final);
            $patron = implode('(\d+)', array_map(fn($p) => preg_quote($p, '/'), $partes));

            if (preg_match('/^' . $patron . '$/', $nombreCorrelativo)) {
                $esDeFormato = true;
                break;
            }
        }

        // Si no coincide con ningún formato, no validar unicidad
        if (!$esDeFormato) {
            return response()->json(['unico' => true]);
        }

        // Si coincide con un formato, verificar que no esté duplicado
        $query = Factura::where('nombre_correlativo', $nombreCorrelativo)
            ->whereNotIn('estado', ['Anulada']);

        if ($idFacturaActual) {
            $query->where('id_factura', '!=', $idFacturaActual);
        }

        $existe = $query->exists();

        return response()->json([
            'unico' => !$existe,
            'mensaje' => $existe ? "Nro  {$nombreCorrelativo} en uso." : '',
        ]);
    }

    public function checkCorrelativoCotiUnico(Request $request)
    {
        $nroCoti = $request->input('nro_cotizacion');
        $idFacturaActual = $request->input('id_factura');
        $idEmisorContexto = $request->input('id_empresa_contexto');

        if (!$nroCoti) {
            return response()->json(['unico' => true]);
        }

        // Verificar con formato v2
        $rucEmisor2 = null;
        if ($idEmisorContexto) {
            $rucEmisor2 = EmpresasClientes::find($idEmisorContexto)?->ruc;
        }

        $formatoCoti2Check = $idEmisorContexto
            ? FormatoCotizaciones2::where('id_empresas_clientes_emisor', $idEmisorContexto)->first()
            : null;

        $esDeFormato2 = false;
        if ($formatoCoti2Check && !empty($formatoCoti2Check->formato_final)) {
            $partes2 = explode('{N}', $formatoCoti2Check->formato_final);
            $patron2 = implode('(\d+)', array_map(fn($p) => preg_quote($p, '/'), $partes2));
            if (preg_match('/^' . $patron2 . '$/', $nroCoti)) {
                $esDeFormato2 = true;
            }
        }

        if (!$esDeFormato2) {
            // Verificar con formato v1 (legado)
            $formatos = FormatoCotizaciones::all();
            $esDeFormato = false;
            foreach ($formatos as $formato) {
                if (empty($formato->formato_final_coti)) continue;
                $partes = explode('{N}', $formato->formato_final_coti);
                $patron = implode('(\d+)', array_map(fn($p) => preg_quote($p, '/'), $partes));
                if (preg_match('/^' . $patron . '$/', $nroCoti)) { $esDeFormato = true; break; }
            }
            if (!$esDeFormato) {
                return response()->json(['unico' => true]);
            }
        }

        $query = Factura::where('nro_cotizacion', $nroCoti)
            ->whereNotIn('estado', ['Anulada']);

        if ($rucEmisor2) {
            $query->where('ruc_emisor', $rucEmisor2);
        }

        if ($idFacturaActual) {
            $query->where('id_factura', '!=', $idFacturaActual);
        }

        $existe = $query->exists();

        return response()->json([
            'unico'   => !$existe,
            'mensaje' => $existe ? "Nro Cotización {$nroCoti} en uso." : '',
        ]); 
    }

    /* Helpers for Nro Cotización (Automatic Correlatives) */

    public function extraerContadorDeCorrelativoCoti($nroCoti, $formato)
    {
        if (!$formato || empty($formato->formato_final_coti))
            return null;
        $partes = explode('{N}', $formato->formato_final_coti);
        $patron = implode('(\d+)', array_map(fn($p) => preg_quote($p, '/'), $partes));
        if (preg_match('/' . $patron . '/', $nroCoti, $matches) && isset($matches[1])) {
            return (int) $matches[1];
        }
        return null;
    }

    public function generarNombreConContadorCoti($formato, $contador)
    {
        $contadorPad = $formato->contador_padding_coti > 0
            ? str_pad($contador, $formato->contador_padding_coti, '0', STR_PAD_LEFT)
            : $contador;
        return str_replace('{N}', $contadorPad, $formato->formato_final_coti);
    }

    public function calcularOrdenCorrelativosCoti($id_cliente, $id_empresa_cliente, $formato, $facturaSimulada = null, $id_receptor_empresa = null, $id_receptor_cliente = null)
    {
        $rucEmisor = null;
        if ($id_empresa_cliente) {
            $rucEmisor = EmpresasClientes::find($id_empresa_cliente)?->ruc;
        }

        $facturas = Factura::query()
            ->where('ruc_emisor', $rucEmisor)
            ->whereNotIn('estado', ['Anulada'])
            ->whereNotNull('nro_cotizacion')
            ->when($id_receptor_empresa, fn($q) => $q->where('id_empresa_cliente', $id_receptor_empresa))
            ->when($id_receptor_cliente, fn($q) => $q->where('id_cliente', $id_receptor_cliente))
            ->get()
            ->filter(fn($f) => str_starts_with($f->nro_cotizacion, 'PENDIENTE-')
                || $this->extraerContadorDeCorrelativoCoti($f->nro_cotizacion, $formato) !== null);

        if ($facturaSimulada) {
            $facturas = $facturas->where('id_factura', '!=', $facturaSimulada->id_factura);
            $facturas = $facturas->concat([$facturaSimulada]);
        }

        $facturadas = $facturas->where('estado', 'Facturada');
        $numerosFacturadas = $facturadas
            ->map(fn($f) => $this->extraerContadorDeCorrelativoCoti($f->nro_cotizacion, $formato))
            ->filter()
            ->values();

        $numerosOcupados = $numerosFacturadas->toArray();

        $pendientes = $facturas->where('estado', '!=', 'Facturada')
            ->sortBy(fn($f) => [Carbon::parse($f->fecha_emision)->timestamp, $f->id_factura])
            ->values();

        $maxFacturada = $numerosFacturadas->isNotEmpty() ? $numerosFacturadas->max() : 0;
        if ($formato && $formato->contador_actual_coti > $maxFacturada) {
            $maxFacturada = $formato->contador_actual_coti;
        }

        $numerosPendientesActuales = $pendientes
            ->map(fn($f) => $this->extraerContadorDeCorrelativoCoti($f->nro_cotizacion, $formato))
            ->filter()
            ->sort()
            ->values();

        $base = $maxFacturada + 1;
        if ($numerosPendientesActuales->isNotEmpty()) {
            if ($maxFacturada === 0 || $numerosPendientesActuales->first() < $base) {
                $base = $numerosPendientesActuales->first();
            }
        } elseif ($maxFacturada === 0) {
            $base = 1;
        }

        $numerosDisponibles = collect();
        $num = $base;
        while ($numerosDisponibles->count() < $pendientes->count()) {
            if (!in_array($num, $numerosOcupados)) {
                $numerosDisponibles->push($num);
            }
            $num++;
        }

        $resultado = collect();
        foreach ($facturadas as $f) {
            $c = $this->extraerContadorDeCorrelativoCoti($f->nro_cotizacion, $formato);
            $resultado->push([
                'factura' => $f,
                'numero' => $c,
                'nombre' => $f->nro_cotizacion,
            ]);
        }
        foreach ($pendientes as $i => $f) {
            $numero = $numerosDisponibles->get($i);
            $resultado->push([
                'factura' => $f,
                'numero' => $numero,
                'nombre' => $this->generarNombreConContadorCoti($formato, $numero),
            ]);
        }
        return $resultado;
    }

    public function insertarCorrelativoCotiEnPosicion($id_cliente, $id_empresa_cliente, $idFacturaNueva, $id_receptor_empresa = null, $id_receptor_cliente = null)
    {
        $queryFormato = FormatoCotizaciones::query();
        if ($id_empresa_cliente) {
            $queryFormato->where('id_empresas_clientes_emisor', $id_empresa_cliente);
        } else {
            $queryFormato->where('id_cliente', $id_cliente);
        }
        if ($id_receptor_empresa) {
            $queryFormato->where('id_empresas_clientes', $id_receptor_empresa);
        } elseif ($id_receptor_cliente) {
            $queryFormato->where('id_cliente', $id_receptor_cliente);
        }
        $formato = $queryFormato->latest('id_formato_cotizaciones')->first();
        if (!$formato)
            return;
        $formato->refresh();

        $orden = $this->calcularOrdenCorrelativosCoti($id_cliente, $id_empresa_cliente, $formato, null, $id_receptor_empresa, $id_receptor_cliente);
        if ($orden->isEmpty())
            return;

        foreach ($orden as $item) {
            $f = $item['factura'];
            $temp = 'PENDIENTE-REORDER-COTI-' . $f->id_factura;
            if ($f->nro_cotizacion !== $temp) {
                $f->nro_cotizacion = $temp;
                $f->save();
            }
        }

        $maxContador = 0;
        foreach ($orden as $item) {
            $f = $item['factura'];
            $f->nro_cotizacion = $item['nombre'];
            $f->save();
            if ($item['numero'] > $maxContador) {
                $maxContador = $item['numero'];
            }
        }

        if ($maxContador !== $formato->contador_actual_coti) {
            $formato->contador_actual_coti = $maxContador;
            $formato->save();
        }
    }

    public function reordenarCorrelativosCotiPendientes($id_cliente, $id_empresa_cliente, $id_receptor_empresa = null, $id_receptor_cliente = null)
    {
        $queryFormato = FormatoCotizaciones::query();
        if ($id_empresa_cliente) {
            $queryFormato->where('id_empresas_clientes_emisor', $id_empresa_cliente);
        } else {
            $queryFormato->where('id_cliente', $id_cliente);
        }
        if ($id_receptor_empresa) {
            $queryFormato->where('id_empresas_clientes', $id_receptor_empresa);
        } elseif ($id_receptor_cliente) {
            $queryFormato->where('id_cliente', $id_receptor_cliente);
        }
        $formato = $queryFormato->latest('id_formato_cotizaciones')->first();
        if (!$formato)
            return;
        $formato->refresh();

        $orden = $this->calcularOrdenCorrelativosCoti($id_cliente, $id_empresa_cliente, $formato, null, $id_receptor_empresa, $id_receptor_cliente);
        if ($orden->isEmpty())
            return;

        foreach ($orden as $item) {
            $f = $item['factura'];
            $temp = 'PENDIENTE-REORDER-COTI-' . $f->id_factura;
            if ($f->nro_cotizacion !== $temp) {
                $f->nro_cotizacion = $temp;
                $f->save();
            }
        }

        $maxContador = 0;
        foreach ($orden as $item) {
            $f = $item['factura'];
            $f->nro_cotizacion = $item['nombre'];
            $f->save();
            if ($item['numero'] > $maxContador) {
                $maxContador = $item['numero'];
            }
        }

        if ($maxContador !== $formato->contador_actual_coti) {
            $formato->contador_actual_coti = $maxContador;
            $formato->save();
        }
    }

    public function checkSolicitudUnico(Request $request)
    {
        $numeroSolicitud = $request->input('numero_solicitud');
        $idFacturaActual = $request->input('id_factura');

        if (!$numeroSolicitud) {
            return response()->json(['unico' => true]);
        }

        $formatos = FormatoSolicitudes::all();
        $esDeFormato = false;

        foreach ($formatos as $formato) {
            if (empty($formato->formato_final))
                continue;

            $partes = explode('{N}', $formato->formato_final);
            $patron = implode('(\d+)', array_map(fn($p) => preg_quote($p, '/'), $partes));

            if (preg_match('/^' . $patron . '$/', $numeroSolicitud)) {
                $esDeFormato = true;
                break;
            }
        }

        if (!$esDeFormato) {
            return response()->json(['unico' => true]);
        }

        $query = Factura::where('numero_solicitud', $numeroSolicitud)
            ->whereNotIn('estado', ['Anulada']);

        if ($idFacturaActual) {
            $query->where('id_factura', '!=', $idFacturaActual);
        }

        $existe = $query->exists();

        return response()->json([
            'unico' => !$existe,
            'mensaje' => $existe ? "Nro de Solicitud {$numeroSolicitud} en uso." : '',
        ]);
    }

    public function todos(Request $request)
    {
        $query = Factura::with(['empresa', 'cliente']);
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tipo_factura')) {
            $tipoMap = [
                'Clientes' => 1,
                'R1→A1' => 2,
                'R2→R1' => 3,
            ];
            $id = $tipoMap[$request->tipo_factura] ?? null;
            if ($id) {
                $query->where('tipo_factura', $id);
            }
        }

        if ($request->filled('anio')) {
            $query->whereYear('fecha_emision', $request->anio);
        }

        if ($request->filled('mes')) {
            $query->whereMonth('fecha_emision', $request->mes);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('ruc_emisor', 'like', "%{$buscar}%")
                    ->orWhere('ruc_receptor', 'like', "%{$buscar}%")
                    ->orWhere('nro_factura', 'like', "%{$buscar}%")
                    ->orWhere('nombre_correlativo', 'like', "%{$buscar}%")
                    ->orWhere('numero_solicitud', 'like', "%{$buscar}%")
                    ->orWhereHas('empresa', function ($sq) use ($buscar) {
                        $sq->where('razon_social', 'like', "%{$buscar}%");
                    })
                    ->orWhereHas('cliente', function ($sq) use ($buscar) {
                        $sq->where('razon_social', 'like', "%{$buscar}%");
                    })
                    ->orWhereIn('ruc_emisor', function ($sq) use ($buscar) {
                        $sq->select('ruc')->from('empresas_clientes')->where('razon_social', 'like', "%{$buscar}%");
                    });
            });
        }

        $facturas = $query->orderByDesc('fecha_emision')->paginate(100)->appends($request->all());
        $empresas = EmpresasClientes::select('ruc', 'razon_social')->get()->keyBy('ruc');
        $clientes = ClienteAuditoria::select('ruc', 'razon_social')->get()->keyBy('ruc');
        $inventarios = Inventario::select('id_inventario', 'id_empresas_cliente', 'nombre_codigo')
            ->with('empresa:id_empresas_clientes,razon_social')
            ->get();
        $usuariosAuditoria = User::select('id', 'name', 'puede_editar')
            ->where('tipo_usuario', 3)
            ->where('id_area', 7)
            ->where('estado', 1)
            ->get();
        $empresasConPlantilla = CotizacionPlantilla::with('empresa')
            ->get()
            ->pluck('empresa.ruc')
            ->filter()
            ->toArray();

        $empresasConSolicitud = PlantillaReceptor::with(['empresa', 'cliente'])
            ->get()
            ->filter(fn($r) => !empty($r->archivo_solicitud))
            ->map(fn($r) => $r->empresa?->ruc ?? $r->cliente?->ruc)
            ->filter()
            ->unique()
            ->toArray();

        $empresasConOrdCompra = PlantillaReceptor::with(['empresa', 'cliente'])
            ->get()
            ->filter(fn($r) => !empty($r->archivo_orden_compra))
            ->map(fn($r) => $r->empresa?->ruc ?? $r->cliente?->ruc)
            ->filter()
            ->unique()
            ->toArray();

        $anios = Factura::selectRaw('YEAR(fecha_emision) as anio')->distinct()->orderByDesc('anio')->pluck('anio');
        $mesesArray = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        return view('facturas.index', compact(
            'facturas',
            'usuariosAuditoria',
            'empresas',
            'clientes',
            'inventarios',
            'empresasConPlantilla',
            'empresasConSolicitud',
            'empresasConOrdCompra',
            'anios',
            'mesesArray'
        ));
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids) || !is_array($ids)) {
            return response()->json(['success' => false, 'message' => 'No se seleccionaron facturas.'], 400);
        }
        $facturas = Factura::whereIn('id_factura', $ids)->get();
        foreach ($facturas as $f) {
            if ($f->estado !== 'Anulada') {
                return response()->json(['success' => false, 'message' => "La factura #{$f->id_factura} no está anulada y no puede ser eliminada masivamente."], 400);
            }
        }

        DB::transaction(function () use ($facturas) {
            $receptoresAfectados = [];
            foreach ($facturas as $f) {
                $receptorKey = $f->id_empresa_cliente ? "emp_{$f->id_empresa_cliente}" : "cli_{$f->id_cliente}";
                if (!isset($receptoresAfectados[$receptorKey])) {
                    $receptoresAfectados[$receptorKey] = [
                        'id_cliente' => $f->id_cliente,
                        'id_empresa_cliente' => $f->id_empresa_cliente,
                        'ruc_emisor' => $f->ruc_emisor
                    ];
                }
                $f->detalles()->delete();
                $f->delete();
            }

            foreach ($receptoresAfectados as $re) {
                $this->reordenarCorrelativosPendientes($re['id_cliente'], $re['id_empresa_cliente']);
                $this->reordenarSolicitudesPendientes($re['id_cliente'], $re['id_empresa_cliente']);
                $emisor = EmpresasClientes::where('ruc', $re['ruc_emisor'])->first();
                $idEmisor = $emisor ? $emisor->id_empresas_clientes : null;
                $this->reordenarCorrelativosCotiPendientes(
                    null,
                    $idEmisor,
                    $re['id_empresa_cliente'],
                    $re['id_cliente']
                );
            }
        });
        return response()->json(['success' => true, 'message' => 'Facturas eliminadas correctamente.']);
    }
    public function exportCuentas(Request $request)
    {
        $estado = $request->input('estado', '1');
        $sede = $request->input('sede', 'Trujillo');

        $query = CuentaEmpresa::with('empresa')
            ->join('empresas_clientes', 'cuentas_empresas.id_empresa_cliente', '=', 'empresas_clientes.id_empresas_clientes')
            ->select('cuentas_empresas.*')
            ->orderBy('empresas_clientes.razon_social', 'asc');

        if ($estado !== 'ambas') {
            $query->where('cuentas_empresas.estado', $estado);
        }

        if ($sede !== 'Ambas') {
            $query->where('cuentas_empresas.sede', $sede);
        }

        $cuentas = $query->get();

        $fecha = now()->format('d_m_Y');
        $nombreArchivo = "CUENTAS_EMPRESAS_DESCARGADO_{$fecha}.xlsx";

        return Excel::download(new CuentasEmpresasExport($cuentas), $nombreArchivo);
    }

    public function getProgramacionModal(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', 'all'); // 'all' o un número de mes (1-12)
        $selectedChofer = $request->get('selected_chofer');
        $selectedDate = $request->get('selected_date'); // YYYY-MM-DD

        // Cargamos todas las programaciones de este año
        $todasProgramaciones = \App\Models\ProgramacionTransporte::with(['chofer.empresa'])
            ->whereYear('fecha_programacion', $year)
            ->get();

        // Obtener todos los choferes activos
        $choferes = \App\Models\Chofer::with('empresa')->where('estado', 1)->get();

        // Mapear programaciones a un lookup rápido
        $progLookup = [];
        foreach ($todasProgramaciones as $p) {
            $monthNum = (int)$p->fecha_programacion->month;
            $progLookup["{$p->id_chofer}-{$monthNum}"] = $p;
        }

        $allMonths = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $monthsMap = [];
        if ($month && $month !== 'all') {
            $mVal = (int)$month;
            if (isset($allMonths[$mVal])) {
                $monthsMap[$mVal] = $allMonths[$mVal];
            }
        } else {
            $monthsMap = $allMonths;
        }

        // Cargar feriados de este año
        $feriados = \App\Models\Feriado::whereYear('fecha_feriado', $year)
            ->pluck('fecha_feriado')
            ->map(function($date) {
                return Carbon::parse($date)->format('Y-m-d');
            })
            ->toArray();

        $dayOfWeekMap = [
            0 => 'DOMINGO',
            1 => 'LUNES',
            2 => 'MARTES',
            3 => 'MIÉRCOLES',
            4 => 'JUEVES',
            5 => 'VIERNES',
            6 => 'SÁBADO',
        ];

        // Construir calendario de días
        $calendar = [];
        foreach ($monthsMap as $mNum => $mName) {
            $daysInMonth = Carbon::createFromDate($year, $mNum, 1)->daysInMonth;
            $days = [];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $date = Carbon::createFromDate($year, $mNum, $d);
                $dateStr = $date->format('Y-m-d');
                $isFeriado = in_array($dateStr, $feriados);
                $days[] = [
                    'numero' => $d,
                    'nombre' => $dayOfWeekMap[$date->dayOfWeek] . ($isFeriado ? ' (FERIADO)' : ''),
                    'es_domingo' => $date->dayOfWeek === 0 || $isFeriado,
                    'es_feriado' => $isFeriado,
                ];
            }
            $calendar[$mNum] = [
                'name' => $mName,
                'days' => $days,
                'count' => $daysInMonth
            ];
        }

        return view('facturas.partials.programacion_grid', compact(
            'choferes',
            'progLookup',
            'calendar',
            'year',
            'month',
            'selectedChofer',
            'selectedDate'
        ))->render();
    }
}