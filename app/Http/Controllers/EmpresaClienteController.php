<?php

namespace App\Http\Controllers;

use App\Models\AfpEmpresaCliente;
use App\Models\CalendarioVencimiento;
use App\Models\Cargo;
use App\Models\ComprasEmpresaCliente;
use App\Models\CotizacionRecepcion;
use App\Models\EmpresasClientes;
use App\Models\PdtEmpresaCliente;
use App\Models\PdtplameEmpresaCliente;
use App\Models\RecepcionDocumento;
use App\Models\SireEmpresaCliente;
use App\Models\VentasEmpresaCliente;
use App\Models\Gerente;
use App\Models\Socios;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;



class EmpresaClienteController extends Controller
{
    public function index()
    {
        // Empresas activas o suspendidas
        $empresas_clientes = EmpresasClientes::whereIn('estado_actual', [1])->get();

        // Mes de declaración (mes anterior - Agosto 2025 si estamos en Septiembre 2025)
        $mesDeclaracion = now()->subMonth()->format('Y-m');
        $mesVencimiento = now()->format('Y-m');

        // Buscar calendarios para el mes de declaración (mes anterior)
        $calendarioSIRE = CalendarioVencimiento::where('tipo', 'SIRE')
            ->where('mes', $mesDeclaracion)
            ->where('estado', 1)
            ->first();

        $calendarioPDT = CalendarioVencimiento::where('tipo', 'PDT')
            ->where('mes', $mesDeclaracion)
            ->where('estado', 1)
            ->first();

        // Si no hay calendario para el mes de declaración, buscar el más reciente
        if (!$calendarioSIRE) {
            $calendarioSIRE = CalendarioVencimiento::where('tipo', 'SIRE')
                ->where('estado', 1)
                ->orderBy('mes', 'desc')
                ->first();
        }

        if (!$calendarioPDT) {
            $calendarioPDT = CalendarioVencimiento::where('tipo', 'PDT')
                ->where('estado', 1)
                ->orderBy('mes', 'desc')
                ->first();
        }

        // Convertir JSON a arrays
        $fechasVencimientoSIRE = $calendarioSIRE ? json_decode($calendarioSIRE->fechas, true) : [];
        $fechasVencimientoPDT = $calendarioPDT ? json_decode($calendarioPDT->fechas, true) : [];

        // Obtener mes y año ACTUAL (cuando se registran los datos)
        $mesActual = now()->month;
        $anioActual = now()->year;

        // Verificar registros previos para el mes ACTUAL
        foreach ($empresas_clientes as $cliente) {
            // Validación SIRE - buscar en mes ACTUAL
            $cliente->existeRegistroSire = SireEmpresaCliente::where('id_empresas_clientes_sire', $cliente->id_empresas_clientes)
                ->whereMonth('fecha', $mesActual)
                ->whereYear('fecha', $anioActual)
                ->exists();

            // Validación PDT - buscar en mes ACTUAL
            $cliente->existeRegistroPDT = PdtEmpresaCliente::where('id_empresas_clientes_pdt', $cliente->id_empresas_clientes)
                ->whereMonth('fecha', $mesActual)
                ->whereYear('fecha', $anioActual)
                ->exists();

            // Validación Compras - buscar en mes ACTUAL
            $cliente->existeRegistroCompras = ComprasEmpresaCliente::where('id_empresas_clientes_compras', $cliente->id_empresas_clientes)
                ->whereMonth('fecha', $mesActual)
                ->whereYear('fecha', $anioActual)
                ->exists();

            // Validación Ventas - buscar en mes ACTUAL
            $cliente->existeRegistroVentas = VentasEmpresaCliente::where('id_empresas_clientes_ventas', $cliente->id_empresas_clientes)
                ->whereMonth('fecha', $mesActual)
                ->whereYear('fecha', $anioActual)
                ->exists();
        }

        // Períodos para la vista
        $periodoDeclaracion = Carbon::parse($mesDeclaracion)->translatedFormat('F-Y');
        $periodoVencimiento = $mesVencimiento;

        return view('empresas_clientes.index', compact(
            'empresas_clientes',
            'fechasVencimientoPDT',
            'fechasVencimientoSIRE',
            'periodoDeclaracion',
            'periodoVencimiento'
        ));
    }

    public function create()
    {
        return view('empresas_clientes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'estado' => 'required|string|max:50',
            'razon_social' => 'required|string|max:255',
            'ruc' => 'required|string|size:11|regex:/^\d+$/|unique:empresas_clientes,ruc,' . $request->id_empresas_clientes . ',id_empresas_clientes',
            'sol_usuario' => 'required|string',
            'sol_clave' => 'required|string',
            'sol2_usuario' => 'nullable|string',
            'sol2_clave' => 'nullable|string',
            'detraccion' => 'required|string|max:10',
            'retencion' => 'required|string|max:10',
            'rubro' => 'nullable|string',
            'afp_usuario' => 'nullable|string',
            'afp_clave' => 'nullable|string',
            'bn_usuario' => 'nullable|string',
            'bn_clave' => 'nullable|string',
            'clave_extranet' => 'nullable|string',
            'estado_actual' => 'required|in:0,1,2,3',
            'fecha_ingreso' => 'nullable|date',
            'mes_inicio_declaracion' => 'nullable|string|max:255',
            'mes_inicio_planilla' => 'nullable|string|max:255',
        ], [
            'ruc.unique' => 'RUC ya registrado en el sistema.',
        ]);

        $data = $request->all();
        $data['estado_recursos'] = $request->estado;

        EmpresasClientes::create($data);

        return redirect()->route('empresas_clientes.index')
            ->with('success', 'Empresa Cliente creada exitosamente.');
    }



    public function edit($id_empresas_clientes)
    {
        $empresaCliente = EmpresasClientes::findOrFail($id_empresas_clientes);
        return view('empresas_clientes.edit', compact('empresaCliente'));
    }



    public function update(Request $request, $id_empresas_clientes)
    {
        $validated = $request->validate([
            'estado' => 'required|string|max:50',
            'razon_social' => 'required|string|max:255',
            'ruc' => 'required|string|size:11',
            'sol_usuario' => 'required|string|max:255',
            'sol_clave' => 'required|string|max:255',
            'sol2_usuario' => 'nullable|string|max:255',
            'sol2_clave' => 'nullable|string|max:255',
            'detraccion' => 'nullable|string|max:10',
            'retencion' => 'nullable|string|max:10',
            'rubro' => 'nullable|string|max:255',
            'afp_usuario' => 'nullable|string|max:255',
            'afp_clave' => 'nullable|string|max:255',
            'bn_usuario' => 'nullable|string|max:255',
            'bn_clave' => 'nullable|string|max:255',
            'clave_extranet' => 'nullable|string|max:255',
            'fecha_ingreso' => 'nullable|date',
            'mes_inicio_declaracion' => 'nullable|string|max:255',
            'mes_inicio_planilla' => 'nullable|string|max:255',
            'estado_actual' => 'required|in:0,1,2,3', // t amo jefecito
        ]);

        $empresaCliente = EmpresasClientes::findOrFail($id_empresas_clientes);
        $empresaCliente->update($validated);
        // estado_recursos no se toca en update, conserva el valor original del store
        return redirect()->route('empresas_clientes.index')->with('success', 'Cliente actualizado con éxito.');
    }



    public function destroy($id)
    {
        $empresaCliente = EmpresasClientes::findOrFail($id);
        $empresaCliente->delete();
        return redirect()->route('empresas_clientes.index')->with('success', 'Cliente eliminado con éxito.');
    }

    public function filtrarNoPreliquidacion(Request $request)
    {
        $anio = $request->input('anio');

        // Obtener empresas clientes que están activas/suspendidas
        $empresas_sin_preliquidacion = EmpresasClientes::whereIn('estado_actual', [1, 2])
            ->get()
            ->filter(function ($cliente) use ($anio) {
                // Buscar preliquidación del año específico
                $preliquidacion = $cliente->preliquidaciones->where('anio', $anio)->first();
                if (!$preliquidacion) {
                    return true; // No tiene preliquidación
                }
                if (!$preliquidacion->determinacionRenta) {
                    return true; // Tiene preliquidación pero falta determinación
                }
                return false; // Ya tiene ambas, no mostrar
            });

        return view('empresas_clientes.filtrar_no_preliquidacion', compact('empresas_sin_preliquidacion', 'anio'));
    }

    public function activar($id_empresas_clientes)
    {
        $empresaCliente = EmpresasClientes::findOrFail($id_empresas_clientes);
        $empresaCliente->estado_actual = 1; // Activo
        $empresaCliente->fecha_retiro = null;
        $empresaCliente->save();

        return redirect()->back()->with('success', 'Cliente activado correctamente.');
    }

    public function retirar($id_empresas_clientes)
    {
        $empresaCliente = EmpresasClientes::findOrFail($id_empresas_clientes);
        $empresaCliente->estado_actual = 0; // Retirado
        $empresaCliente->fecha_retiro = now();
        $empresaCliente->save();

        return redirect()->back()->with('success', 'Cliente retirado correctamente.');
    }

    public function suspender($id_empresas_clientes)
    {
        $empresaCliente = EmpresasClientes::findOrFail($id_empresas_clientes);
        $empresaCliente->estado_actual = 2; // Suspendido
        $empresaCliente->fecha_retiro = null;
        $empresaCliente->save();

        return redirect()->back()->with('success', 'Cliente suspendido correctamente.');
    }

    public function pendiente($id_empresas_clientes)
    {
        $empresaCliente = EmpresasClientes::findOrFail($id_empresas_clientes);
        $empresaCliente->estado_actual = 3; // Pendiente
        $empresaCliente->fecha_retiro = null;
        $empresaCliente->save();

        return redirect()->back()->with('success', 'Cliente puesto en pendiente correctamente.');
    }

    public function filtrarPorMes(Request $request)
    {
        $users = User::whereHas('area', function ($query) {
            $query->where('nombre', 'CONTABILIDAD');
        })
            ->whereIn('tipo_usuario', [2, 3])
            ->where('estado', 1)
            ->get();

        $mes = $request->input('mes', date('F'));
        $anio = $request->input('anio', date('Y'));

        $empresas = EmpresasClientes::whereIn('estado_actual', [1])
            ->with([
                'registrosCompras' => function ($query) use ($mes, $anio) {
                    $query->whereMonth('fecha', date('m', strtotime($mes)))
                        ->whereYear('fecha', $anio);
                },
                'registrosVentas' => function ($query) use ($mes, $anio) {
                    $query->whereMonth('fecha', date('m', strtotime($mes)))
                        ->whereYear('fecha', $anio);
                },
                'registrosSire' => function ($query) use ($mes, $anio) {
                    $query->whereMonth('fecha', date('m', strtotime($mes)))
                        ->whereYear('fecha', $anio);
                },
                'registrosPdt' => function ($query) use ($mes, $anio) {
                    $query->whereMonth('fecha', date('m', strtotime($mes)))
                        ->whereYear('fecha', $anio);
                }
            ])->get();

        $meses = [
            'January' => 'Enero',
            'February' => 'Febrero',
            'March' => 'Marzo',
            'April' => 'Abril',
            'May' => 'Mayo',
            'June' => 'Junio',
            'July' => 'Julio',
            'August' => 'Agosto',
            'September' => 'Septiembre',
            'October' => 'Octubre',
            'November' => 'Noviembre',
            'December' => 'Diciembre',
        ];

        function contarEstados($registros, $campo)
        {
            return [
                'si' => $registros->where($campo, 'si')->count(),
                'no' => $registros->where($campo, 'no')->count(),
                'na' => $registros->where($campo, 'na')->count(),
                'sin_registro' => $registros->whereNull($campo)->count(),
                'total' => $registros->count(),
            ];
        }

        $conteoResumen = [
            'sire' => contarEstados($empresas->pluck('registrosSire')->flatten(), 'opcion_si_no') + [
                'sin_registro_total' => $empresas->filter(fn($e) => $e->registrosSire->isEmpty())->count()
            ],
            'pdt' => contarEstados($empresas->pluck('registrosPdt')->flatten(), 'opcion_si_no') + [
                'sin_registro_total' => $empresas->filter(fn($e) => $e->registrosPdt->isEmpty())->count()
            ],
            'compras' => contarEstados($empresas->pluck('registrosCompras')->flatten(), 'registro') + [
                'sin_registro_total' => $empresas->filter(fn($e) => $e->registrosCompras->isEmpty())->count()
            ],
            'ventas' => contarEstados($empresas->pluck('registrosVentas')->flatten(), 'registro') + [
                'sin_registro_total' => $empresas->filter(fn($e) => $e->registrosVentas->isEmpty())->count()
            ],
        ];


        return view('empresas_clientes.empresas_clientes_por_mes', compact('empresas', 'meses', 'mes', 'users', 'conteoResumen', 'anio'));
    }

    public function detalles($id, $mes)
    {
        // Obtener la empresa
        $empresa = EmpresasClientes::findOrFail($id);

        // Convertir mes a número si es un nombre en inglés
        $mes = date('m', strtotime($mes));

        // Cargar relaciones y filtrar por mes
        $empresa->load([
            'registrosSire' => function ($query) use ($mes) {
                $query->whereMonth('fecha', $mes);
            },
            'registrosPdt' => function ($query) use ($mes) {
                $query->whereMonth('fecha', $mes);
            },
            'registrosCompras' => function ($query) use ($mes) {
                $query->whereMonth('fecha', $mes);
            },
            'registrosVentas' => function ($query) use ($mes) {
                $query->whereMonth('fecha', $mes);
            }
        ]);


        // dd($empresa);
        // Retornar la vista de detalles
        return view('empresas_clientes.detalles', compact('empresa', 'mes'));
    }

    public function all()
    {
        // Obtener todas las empresas clientes
        $empresas_clientes = EmpresasClientes::all();

        // Pasar los datos a la vista
        return view('empresas_clientes.all', compact('empresas_clientes'));
    }

    public function afp_plame()
    {
        // Filtrar empresas clientes con estado_actual en 1 o 2 Y planilla=true
        $empresas_clientes = EmpresasClientes::whereIn('estado_actual', [1, 2])
            ->where('planilla', 1)
            ->get();

        // Mes de declaración (mes anterior - Agosto 2025 si estamos en Septiembre 2025)
        $mesDeclaracion = now()->subMonth()->format('Y-m');

        // Mes de vencimiento (mes actual - Septiembre 2025)
        $mesVencimiento = now()->format('Y-m');

        // Buscar calendario PDT para el mes de declaración (mes anterior)
        $calendarioPDT = CalendarioVencimiento::where('tipo', 'PDT')
            ->where('mes', $mesDeclaracion)
            ->where('estado', 1)
            ->first();

        // Si no hay calendario para el mes de declaración, buscar el más reciente
        if (!$calendarioPDT) {
            $calendarioPDT = CalendarioVencimiento::where('tipo', 'PDT')
                ->where('estado', 1)
                ->orderBy('mes', 'desc')
                ->first();
        }

        // Convertir JSON a array
        $fechasVencimientoPDT = $calendarioPDT ? json_decode($calendarioPDT->fechas, true) : [];

        // Obtener mes y año ACTUAL (cuando se registran los datos)
        $mesActual = now()->month;
        $anioActual = now()->year;

        // Verificar registros previos para el mes ACTUAL
        foreach ($empresas_clientes as $cliente) {
            // Validación AFP - buscar en mes ACTUAL
            $cliente->existeRegistroAfp = AfpEmpresaCliente::where('id_empresas_clientes_afp', $cliente->id_empresas_clientes)
                ->whereMonth('fecha', $mesActual)
                ->whereYear('fecha', $anioActual)
                ->exists();

            // Validación PDT Plame - buscar en mes ACTUAL
            $cliente->existeRegistroPdtPlame = PdtplameEmpresaCliente::where('id_empresas_clientes_pdtplame', $cliente->id_empresas_clientes)
                ->whereMonth('fecha', $mesActual)
                ->whereYear('fecha', $anioActual)
                ->exists();
        }

        // Períodos para la vista
        $periodoDeclaracion = Carbon::parse($mesDeclaracion)->translatedFormat('F-Y');
        $periodoVencimiento = $mesVencimiento;
        // 7 de cada mes vence
        $vencimientoAFP = '07/' . now()->format('m/Y');


        return view('empresas_clientes.afp_plame', compact(
            'empresas_clientes',
            'fechasVencimientoPDT',
            'vencimientoAFP',
            'periodoDeclaracion',
            'periodoVencimiento'
        ));
    }

    public function filtrarPorMesAfpPdtplame(Request $request)
    {
        $users = User::whereHas('area', function ($query) {
            $query->where('nombre', 'RR.HH');
        })
            ->whereIn('tipo_usuario', [3])
            ->where('estado', 1)
            ->get();

        // Obtener el mes seleccionado
        $mes = $request->input('mes');
        if ($mes === null) {
            $mes = $request->input('mes', date('F'));
        }

        $anio = $request->input('anio', date('Y'));

        // Obtener empresas con planilla=true y estado_actual 1 o 2
        $empresas = EmpresasClientes::whereIn('estado_actual', [1, 2])
            ->where('planilla', 1) // Filtro por planilla=true
            ->with([
                'registrosAfp' => function ($query) use ($mes, $anio) {
                    $query->whereMonth('fecha', date('m', strtotime($mes)))
                        ->whereYear('fecha', $anio);
                },
                'registrosPdtplame' => function ($query) use ($mes, $anio) {
                    $query->whereMonth('fecha', date('m', strtotime($mes)))
                        ->whereYear('fecha', $anio);
                }
            ])->get();

        $meses = [
            'January' => 'Enero',
            'February' => 'Febrero',
            'March' => 'Marzo',
            'April' => 'Abril',
            'May' => 'Mayo',
            'June' => 'Junio',
            'July' => 'Julio',
            'August' => 'Agosto',
            'September' => 'Septiembre',
            'October' => 'Octubre',
            'November' => 'Noviembre',
            'December' => 'Diciembre',
        ];

        return view('empresas_clientes.empresas_clientes_afp_pdtplame_por_mes', compact('empresas', 'meses', 'mes', 'users', 'anio'));
    }

    public function mostrarDetallesAfpPdtplame($id, $mes)
    {
        $empresa = EmpresasClientes::with([
            'registrosAfp' => function ($query) use ($mes) {
                $query->whereMonth('fecha', date('m', strtotime($mes)))
                    ->whereYear('fecha', date('Y'));
            },
            'registrosPdtplame' => function ($query) use ($mes) {
                $query->whereMonth('fecha', date('m', strtotime($mes)))
                    ->whereYear('fecha', date('Y'));
            }
        ])->findOrFail($id);

        return view('empresas_clientes.detalles_afp_pdtplame', compact('empresa', 'mes'));
    }

    public function editarAfp($id)
    {
        $empresaCliente = EmpresasClientes::findOrFail($id);
        return view('empresas_clientes.editar_afp', compact('empresaCliente'));
    }

    public function actualizarAfp(Request $request, $id)
    {
        $request->validate([
            'afp_usuario' => 'nullable|string|max:255',
            'afp_clave' => 'nullable|string|max:255',
            'mes_inicio_planilla' => 'nullable|date_format:Y-m',
            'estado_recursos' => 'nullable|string|max:50',
        ]);

        $empresaCliente = EmpresasClientes::findOrFail($id);
        $empresaCliente->update($request->only('afp_usuario', 'afp_clave', 'mes_inicio_planilla', 'estado_recursos'));

        return redirect()->route('empresas_clientes.afp_plame')->with('success', 'Datos de AFP actualizados con éxito.');
    }

    public function recepcionDocumentos()
    {
        // Filtrar empresas con estado "C1" y estado_actual 1
        $empresas = EmpresasClientes::where('estado', 'C1')
            ->where('estado_actual', 1)
            ->get();

        // Para cada empresa, obtener los últimos registros y determinar el estado de los botones
        foreach ($empresas as $empresa) {
            // Obtener todos los registros de la empresa
            $todosRegistros = RecepcionDocumento::where('id_empresas_clientes', $empresa->id_empresas_clientes)
                ->orderBy('created_at', 'desc')
                ->get();

            // Inicializar estados de los botones
            $empresa->botonSolicitudActivo = false;
            $empresa->botonRecepcionActivo = false;
            $empresa->botonEntregaActivo = false;

            // Lógica para determinar el estado de los botones
            if ($todosRegistros->count() > 0) {
                $ultimoRegistro = $todosRegistros->first();

                // Si el último registro es una entrega
                if ($ultimoRegistro->estado === 'entrega') {
                    $empresa->botonSolicitudActivo = true;
                }
                // Si el último registro es una recepción
                elseif ($ultimoRegistro->estado === 'recepcion') {
                    $empresa->botonEntregaActivo = true;
                }
                // Si el último registro es una solicitud
                elseif ($ultimoRegistro->estado === 'solicitud') {
                    // Contar solicitudes consecutivas
                    $conteoSolicitudesConsecutivas = 0;
                    foreach ($todosRegistros as $registro) {
                        if ($registro->estado === 'solicitud') {
                            $conteoSolicitudesConsecutivas++;
                        } else {
                            break; // Romper el bucle si no es una solicitud
                        }
                    }

                    // Si hay menos de 3 solicitudes consecutivas
                    if ($conteoSolicitudesConsecutivas < 3) {
                        $empresa->botonSolicitudActivo = true;
                        $empresa->botonRecepcionActivo = true;
                    }
                    // Si hay 3 solicitudes consecutivas
                    else {
                        $empresa->botonRecepcionActivo = true;
                    }
                }
            } else {
                // Si no hay registros, activar solo el botón de solicitud
                $empresa->botonSolicitudActivo = true;
            }
        }

        return view('empresas_clientes.recepcion_documentos', compact('empresas'));
    }

    public function toggleRecepcion($id)
    {
        $empresa = EmpresasClientes::findOrFail($id);

        if (!in_array(auth()->user()->tipo_usuario, [1, 2, 3])) {
            abort(403, 'No autorizado.');
        }

        $empresa->manejar_recepcion = !$empresa->manejar_recepcion;
        $empresa->save();

        return redirect()->back()->with('success', 'Estado de recepción actualizado.');
    }

    // Método para mostrar la vista de gestión de planilla
    public function gestionPlanilla()
    {
        $empresas_clientes = EmpresasClientes::whereIn('estado_actual', [1, 2])
            ->orderBy('razon_social')
            ->get();

        return view('empresas_clientes.gestion_planilla', compact('empresas_clientes'));
    }

    // Método para activar planilla
    public function activarPlanilla($id)
    {
        $empresa = EmpresasClientes::findOrFail($id);
        $empresa->planilla = true;
        $empresa->save();

        return redirect()->route('empresas_clientes.gestion_planilla')
            ->with('success', 'Planilla activada correctamente para ' . $empresa->razon_social);
    }

    // Método para desactivar planilla
    public function desactivarPlanilla($id)
    {
        $empresa = EmpresasClientes::findOrFail($id);
        $empresa->planilla = false;
        $empresa->save();

        return redirect()->route('empresas_clientes.gestion_planilla')
            ->with('success', 'Planilla desactivada correctamente para ' . $empresa->razon_social);
    }

    public function empresasClientesAuditoria()
    {
        $estadosPermitidos = ['A1', 'R1', 'R2', 'I1'];
        //SON IDS EXTRAS DE ESTADO C1 QUE PIDE EL AREA DE AUDITORIA QUE DESEA VERLOS TMB SUS DATOS DE LAS EMPRESAS
        $idsAdicionales = [63, 22, 47, 45, 3, 43, 70];

        $empresas_clientes = EmpresasClientes::with([
            'direcciones' => function ($query) {
                $query->where('estado', 1); // Solo direcciones activas
            },
            'inventarios',
            'cuentas' => function ($query) {
                $query->where('estado', 1)->orderBy('tipo')->orderBy('nombre');
            }
        ])
            ->where(function ($query) use ($estadosPermitidos, $idsAdicionales) {
                $query->whereIn('estado', $estadosPermitidos)
                    ->orWhereIn('id_empresas_clientes', $idsAdicionales);
            })
            ->get();

        return view('empresas_clientes.empresas_clientes_auditoria', compact('empresas_clientes'));
    }
    public function empresasClientesC1()
    {
        $empresas_clientes = EmpresasClientes::where('estado', 'C1')
            ->where('estado_actual', 1)
            ->get();

        return view('empresas_clientes.empresas_c1', compact('empresas_clientes'));
    }




    public function editarContacto($id)
    {
        $empresa = EmpresasClientes::findOrFail($id);
        return view('empresas_clientes.editar_contacto', compact('empresa'));
    }

    public function actualizarContacto(Request $request, $id)
    {
        $request->validate([
            'contacto' => 'nullable|string|max:255',
            'info_cuentas' => 'nullable|string',
            'correo' => 'nullable|string|email|max:255',
            'nro_celular' => 'nullable|string|max:50',
            'pagina_web' => 'nullable|string',
        ]);

        $empresa = EmpresasClientes::findOrFail($id);
        $empresa->contacto = $request->contacto;
        $empresa->info_cuentas = $request->info_cuentas;
        $empresa->correo = $request->correo;
        $empresa->nro_celular = $request->nro_celular;
        $empresa->pagina_web = $request->pagina_web;
        $empresa->save();

        return redirect()->route('empresas_clientes_auditoria.all')
            ->with('success', 'Datos actualizados correctamente.');
    }

    public function toggleEstado($id)
    {
        $empresa = EmpresasClientes::findOrFail($id);
        $empresa->estado_actual = $empresa->estado_actual == 1 ? 2 : 1;
        $empresa->save();

        return response()->json([
            'success' => true,
            'nuevo_estado' => $empresa->estado_actual
        ]);
    }

    public function recepcionPanel()
    {
        // Empresas activas y estado C1
        $empresas = EmpresasClientes::where('estado', 'C1')
            ->whereIn('estado_actual', [1, 2, 0])
            ->get();

        foreach ($empresas as $empresa) {

            // Últimos movimientos de recepción
            $empresa->ultRecepcion = RecepcionDocumento::where('id_empresas_clientes', $empresa->id_empresas_clientes)
                ->orderBy('id_recepcion', 'desc')
                ->first();

            // Cotizaciones recientes del área de recepción
            $empresa->cotizaciones = CotizacionRecepcion::where('id_empresa_cliente', $empresa->id_empresas_clientes)
                ->orderBy('created_at', 'desc')
                ->take(1) //PUEDE TRAER MAS PERO UNO NOMA
                ->get();

            // Cargos recientes
            $empresa->cargos = Cargo::where('id_empresa_cliente', $empresa->id_empresas_clientes)
                ->orderBy('created_at', 'desc')
                ->take(1) //PUEDE TRAER MAS PERO UNO NOMA
                ->get();
        }

        return view('empresas_clientes.recepcion_panel', compact('empresas'));
    }

    public function create_gerente_socios($empresaId)
    {
        $empresa = EmpresasClientes::findOrFail($empresaId);

        // Obtener gerente y socios actuales para mostrar el contexto
        $gerenteActual = Gerente::where('id_empresas_clientes', $empresaId)
            ->where('estado', 'ACTIVO')
            ->first();

        $sociosActivos = Socios::where('id_empresas_clientes', $empresaId)
            ->where('estado', 'ACTIVO')
            ->get();

        return view('gerente_socios.form', [
            'empresa' => $empresa,
            'modo' => 'create',
            'gerente' => null,
            'socios' => collect(),
            'gerenteActual' => $gerenteActual,
            'sociosActivos' => $sociosActivos,
        ]);
    }

    public function store_gerente_socios(Request $request)
    {
        $request->validate([
            'id_empresas_clientes' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'nombre' => 'nullable|string|max:50',
            'fecha_acta' => 'nullable|date',
            'fecha_sunat' => 'nullable|date',
            'fecha_cambio' => 'nullable|date',
            'observacion_gerente' => 'nullable|string',
            'socios' => 'nullable|array',
            'socios.*.nombre' => 'required_with:socios|string|max:50',
            'socios.*.porcentaje' => 'required_with:socios|numeric|min:0|max:100',
            'socios.*.fecha_cambio' => 'nullable|date',
            'observaciones' => 'nullable|string',
            'estado' => 'nullable|string',
        ]);

        // Validar que al menos se proporcione gerente O socios
        if (empty($request->nombre) && empty($request->socios)) {
            return redirect()->back()
                ->withErrors(['error' => 'Debe proporcionar al menos un gerente o socios.'])
                ->withInput();
        }

        DB::transaction(function () use ($request) {

            //*Si vienen los datos desde la vista LISTAR, que los guarde como inactivos
            if ($request->estado == 'INACTIVO') {
                $estado = 'INACTIVO';
            } else {
                $estado = 'ACTIVO';
            }
            /* =========================
               🔥 DESACTIVAR HISTORIAL (solo si se crea nuevo gerente o socios)
            ========================== */

            if ($request->estado != 'INACTIVO') {
                if (!empty($request->nombre)) {
                    Gerente::where('id_empresas_clientes', $request->id_empresas_clientes)
                        ->where('estado', 'ACTIVO')
                        ->update(['estado' => 'INACTIVO']);
                }

                if (!empty($request->socios)) {
                    Socios::where('id_empresas_clientes', $request->id_empresas_clientes)
                        ->where('estado', 'ACTIVO')
                        ->update(['estado' => 'INACTIVO']);
                }
            }

            /* ======================
            GUARDAR GERENTE (solo si se proporciona nombre)
            ====================== */
            if (!empty($request->nombre)) {
                Gerente::create([
                    'id_empresas_clientes' => $request->id_empresas_clientes,
                    'nombre' => $request->nombre,
                    'fecha_acta' => $request->fecha_acta,
                    'fecha_sunat' => $request->fecha_sunat,
                    'estado' => $estado,
                    'fecha_cambio' => $request->fecha_cambio,
                    'id_usuario' => auth()->id(),
                    'observacion_gerente' => $request->observacion_gerente,
                ]);
            }

            /* ======================
            GUARDAR SOCIOS (solo si se proporciona array)
            ====================== */
            if (!empty($request->socios)) {
                if ($request->input('validar_porcentaje', 'false') == 'false' && !empty($request->socios)) {
                    foreach ($request->socios as $socio) {
                        Socios::create([
                            'id_empresas_clientes' => $request->id_empresas_clientes,
                            'nombre' => $socio['nombre'],
                            'porcentaje' => $socio['porcentaje'],
                            'estado' => $estado,
                            'fecha_cambio' => $socio['fecha_cambio'] ?? now(),
                            'id_usuario' => auth()->id(),
                            'observacion' => $request->observaciones,
                        ]);
                    }
                } else {
                    // Validar que la suma de los porcentajes sea 100%
                    $total = collect($request->socios)->sum('porcentaje');
                    abort_if($total != 100, 422, 'El porcentaje total debe ser 100%');
                    if (!empty($request->socios)) {
                        foreach ($request->socios as $socio) {
                            Socios::create([
                                'id_empresas_clientes' => $request->id_empresas_clientes,
                                'nombre' => $socio['nombre'],
                                'porcentaje' => $socio['porcentaje'],
                                'estado' => $estado,
                                'fecha_cambio' => $socio['fecha_cambio'] ?? now(),
                                'id_usuario' => auth()->id(),
                                'observacion' => $request->observaciones,
                            ]);
                        }
                    }
                }
            }
        });
        if ($request->estado == 'INACTIVO') {
            return redirect()
                ->route('gerente_socios.listar', $request->id_empresas_clientes)
                ->with('success', 'Gerente y socios registrados correctamente.');
        } else {
            return redirect()
                ->route('gerente_socios.index')
                ->with('success', 'Gerente y socios registrados correctamente.');
        }
    }

    public function listarGerenteSocios($id)
    {
        $empresa = EmpresasClientes::findOrFail($id);
        // Gerentes
        $gerenteActual = Gerente::where('id_empresas_clientes', $id)
            ->where('estado', 'ACTIVO')
            ->first();
        $gerentesInactivos = Gerente::where('id_empresas_clientes', $id)
            ->where('estado', 'INACTIVO')
            ->orderBy('fecha_cambio', 'desc')
            ->get();
        // Socios
        $sociosActivos = Socios::where('id_empresas_clientes', $id)
            ->where('estado', 'ACTIVO')
            ->get();
        $sociosInactivos = Socios::where('id_empresas_clientes', $id)
            ->where('estado', 'INACTIVO')
            ->orderBy('fecha_cambio', 'desc')
            ->get();

        return view('gerente_socios.listar', compact(
            'empresa',
            'gerenteActual',
            'gerentesInactivos',
            'sociosActivos',
            'sociosInactivos'
        ));
    }

    public function indexGerenteSocios()
    {
        $empresas = EmpresasClientes::where('estado', 'A1')
            ->where('estado_actual', '1')
            ->with(['gerenteActivo', 'sociosActivos'])
            ->get();

        return view('gerente_socios.index', compact('empresas'));
    }

    public function editGerenteSocios($empresaId)
    {
        $empresa = EmpresasClientes::with([
            'gerenteActivo',
            'sociosActivos'
        ])->findOrFail($empresaId);

        return view('gerente_socios.form', [
            'empresa' => $empresa,
            'modo' => 'edit',
            'gerente' => $empresa->gerenteActivo,
            'socios' => $empresa->sociosActivos,
            'gerenteActual' => $empresa->gerenteActivo,
            'sociosActivos' => $empresa->sociosActivos,
        ]);
    }

    public function updateGerenteSocios(Request $request, $empresaId)
    {
        $request->validate([
            'nombre' => 'nullable|string|max:50',
            'fecha_acta' => 'nullable|date',
            'fecha_sunat' => 'nullable|date',
            'fecha_cambio' => 'nullable|date',
            'observacion_gerente' => 'nullable|string',
            'socios' => 'nullable|array',
            'socios.*.nombre' => 'required_with:socios|string|max:50',
            'socios.*.porcentaje' => 'required_with:socios|numeric|min:0|max:100',
            'socios.*.id_socios' => 'nullable|exists:socios,id_socios',
            'socios.*.fecha_cambio' => 'nullable|date',
            'observaciones' => 'nullable|string',
        ]);

        // Validar que al menos se proporcione gerente O socios
        if (empty($request->nombre) && empty($request->socios)) {
            return redirect()->back()
                ->withErrors(['error' => 'Debe proporcionar al menos un gerente o socios.'])
                ->withInput();
        }

        DB::transaction(function () use ($request, $empresaId) {

            /* =========================
            GERENTE (solo si se proporciona nombre)
            ========================== */
            if (!empty($request->nombre)) {
                $gerente = Gerente::where('id_empresas_clientes', $empresaId)
                    ->where('estado', 'ACTIVO')
                    ->first();

                if ($gerente) {
                    // Actualiza el gerente existente
                    $gerente->update([
                        'nombre' => $request->nombre,
                        'fecha_acta' => $request->fecha_acta,
                        'fecha_sunat' => $request->fecha_sunat,
                        'fecha_cambio' => $request->fecha_cambio,
                        'observacion_gerente' => $request->observacion_gerente,
                        'id_usuario' => auth()->id(),
                    ]);
                } else {
                    // Si no existía, lo creamos
                    Gerente::create([
                        'id_empresas_clientes' => $empresaId,
                        'nombre' => $request->nombre,
                        'fecha_acta' => $request->fecha_acta,
                        'fecha_sunat' => $request->fecha_sunat,
                        'fecha_cambio' => $request->fecha_cambio,
                        'estado' => 'ACTIVO',
                        'id_usuario' => auth()->id(),
                        'observacion_gerente' => $request->observacion_gerente,
                    ]);
                }
            }

            /* =========================
            SOCIOS (solo si se proporciona array)
            ========================== */
            if (!empty($request->socios)) {
                // Tomamos los IDs recibidos para actualizar o eliminar
                $idsRecibidos = collect($request->socios)->pluck('id_socios')->filter();

                // Inactivar socios que no vienen en el request
                Socios::where('id_empresas_clientes', $empresaId)
                    ->where('estado', 'ACTIVO')
                    ->whereNotIn('id_socios', $idsRecibidos)
                    ->update(['estado' => 'INACTIVO']);

                // Crear o actualizar socios
                foreach ($request->socios as $socio) {
                    if (!empty($socio['id_socios'])) {
                        // Actualiza socio existente
                        Socios::where('id_socios', $socio['id_socios'])->update([
                            'nombre' => $socio['nombre'],
                            'porcentaje' => $socio['porcentaje'],
                            'fecha_cambio' => $socio['fecha_cambio'] ?? null,
                            'observacion' => $request->observaciones,
                            'id_usuario' => auth()->id(),
                        ]);
                    } else {
                        // Crear nuevo socio
                        Socios::create([
                            'id_empresas_clientes' => $empresaId,
                            'nombre' => $socio['nombre'],
                            'porcentaje' => $socio['porcentaje'],
                            'estado' => 'ACTIVO',
                            'fecha_cambio' => $socio['fecha_cambio'] ?? now(),
                            'id_usuario' => auth()->id(),
                            'observacion' => $request->observaciones,
                        ]);
                    }
                }
            }
        });

        return redirect()
            ->route('gerente_socios.index')
            ->with('success', 'Gerente y socios actualizados correctamente');
    }


    public function updateObservaciones(Request $request, $empresaId)
    {
        $request->validate([
            'observacion_gerente' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $empresaId) {
            // Actualizar Gerente Activo
            Gerente::where('id_empresas_clientes', $empresaId)
                ->where('estado', 'ACTIVO')
                ->update(['observacion_gerente' => $request->observacion_gerente]);

            // Actualizar Socios Activos
            Socios::where('id_empresas_clientes', $empresaId)
                ->where('estado', 'ACTIVO')
                ->update(['observacion' => $request->observaciones]);
        });

        return redirect()->back()
            ->with('success', 'Observaciones actualizadas correctamente.');
    }

    public function cambiarEstado(Request $request, $id)
    {
        // Validar que el ID del gerente exista
        $gerente = Gerente::findOrFail($id);

        // Obtener el gerente actual usando el ID proporcionado por el formulario
        $gerenteActual = Gerente::findOrFail($request->gerenteActual);
        if ($request->estado == 'ACTIVO') {
            $gerente->estado = 'ACTIVO';
            $gerenteActual->estado = 'INACTIVO';
        }
        $gerente->save();
        $gerenteActual->save();
        return redirect()->back()->with('success', 'Estado del gerente cambiado correctamente.');
    }

    public function activarSocios(Request $request)
    {
        // Obtener los socios seleccionados y sus porcentajes
        $sociosSeleccionados = $request->input('socios_seleccionados', []);
        $porcentajes = $request->input('porcentaje', []);
        $empresaId = $request->input('id_empresas_clientes'); // Obtener el ID de la empresa

        // Paso 1: Desactivar todos los socios de la empresa que no estén seleccionados
        $sociosDeLaEmpresa = Socios::where('id_empresas_clientes', $empresaId)->get();

        foreach ($sociosDeLaEmpresa as $socio) {
            // Si el socio NO está seleccionado, desactivarlo
            if (!in_array($socio->id_socios, $sociosSeleccionados)) {
                $socio->estado = 'INACTIVO';
                $socio->save();
            }
        }
        // Paso 2: Activar los socios seleccionados
        foreach ($sociosSeleccionados as $idSocio) {
            $socio = Socios::find($idSocio);
            if ($socio) {
                // Activar el socio seleccionado
                $socio->estado = 'ACTIVO';

                // Actualizar el porcentaje si es necesario
                $socio->porcentaje = $porcentajes[$idSocio] ?? $socio->porcentaje;

                $socio->save();
            }
        }

        // Redirigir con un mensaje de éxito
        return redirect()->back()->with('success', 'Socios activados y desactivados correctamente.');
    }

    public function gerentesJson($empresaId)
    {
        $empresa = EmpresasClientes::findOrFail($empresaId);

        $activo = Gerente::where('id_empresas_clientes', $empresaId)
            ->where('estado', 'ACTIVO')
            ->first();

        $inactivos = Gerente::where('id_empresas_clientes', $empresaId)
            ->where('estado', 'INACTIVO')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'activo' => $activo ? [
                'id_gerente' => $activo->id_gerente,
                'nombre' => $activo->nombre,
                'fecha_acta' => $activo->fecha_acta,
                'fecha_sunat' => $activo->fecha_sunat,
                'fecha_cambio' => $activo->fecha_cambio,
                'archivo_firma' => $activo->archivo_firma ? asset($activo->archivo_firma) : null,
                'archivo_dni' => $activo->archivo_dni ? asset($activo->archivo_dni) : null,
            ] : null,
            'inactivos' => $inactivos->map(fn($g) => [
                'id_gerente' => $g->id_gerente,
                'nombre' => $g->nombre,
                'fecha_acta' => $g->fecha_acta,
                'fecha_sunat' => $g->fecha_sunat,
                'fecha_cambio' => $g->fecha_cambio,
                'archivo_firma' => $g->archivo_firma ? asset($g->archivo_firma) : null,
                'archivo_dni' => $g->archivo_dni ? asset($g->archivo_dni) : null,
            ]),
        ]);
    }

    public function subirArchivosGerente(Request $request, $gerenteId)
    {
        if (auth()->user()->tipo_usuario != 1 && !in_array(optional(auth()->user()->area)->nombre, ['LEGAL', 'ADMINISTRACION'])) {
            return response()->json(['success' => false, 'message' => 'No tiene permisos para realizar esta acción.'], 403);
        }

        try {
            $request->validate([
                'archivo_firma' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
                'archivo_dni' => 'nullable|file|extensions:pdf|max:5120',
            ], [
                'archivo_firma.mimes' => 'La firma debe ser una imagen (JPG o PNG).',
                'archivo_dni.mimes' => 'El DNI debe ser un archivo PDF.',
                'archivo_firma.max' => 'La firma no debe superar los 5 MB.',
                'archivo_dni.max' => 'El DNI no debe superar los 5 MB.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        }

        $gerente = Gerente::findOrFail($gerenteId);
        $idEmpresa = $gerente->id_empresas_clientes;
        $rucEmpresa = EmpresasClientes::findOrFail($idEmpresa)->ruc;

        // Nombre del gerente normalizado: sin tildes, espacios → guiones bajos, mayúsculas
        $nombreGerente = strtoupper(
            preg_replace(
                '/\s+/',
                '_',
                trim(
                    iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $gerente->nombre)
                )
            )
        );

        // Crear carpetas si no existen
        if (!file_exists(public_path('gerentes/firmas'))) {
            mkdir(public_path('gerentes/firmas'), 0755, true);
        }
        if (!file_exists(public_path('gerentes/dnis'))) {
            mkdir(public_path('gerentes/dnis'), 0755, true);
        }

        if ($request->hasFile('archivo_firma')) {
            // Eliminar archivo anterior si existe
            if ($gerente->archivo_firma) {
                $rutaAnterior = public_path($gerente->archivo_firma);
                if (file_exists($rutaAnterior)) {
                    unlink($rutaAnterior);
                }
            }

            $file = $request->file('archivo_firma');
            $extension = $file->getClientOriginalExtension();

            // Formato: IDEMPRESA_RUCEMPRESA_ARCHIVO_FIRMA_NOMBRE_GERENTE.ext
            $nombreArchivo = $idEmpresa . '_' . $rucEmpresa . '_ARCHIVO_FIRMA_' . $nombreGerente . '.' . $extension;

            $file->move(public_path('gerentes/firmas'), $nombreArchivo);
            $gerente->archivo_firma = 'gerentes/firmas/' . $nombreArchivo;
        }

        if ($request->hasFile('archivo_dni')) {
            // Eliminar archivo anterior si existe
            if ($gerente->archivo_dni) {
                $rutaAnterior = public_path($gerente->archivo_dni);
                if (file_exists($rutaAnterior)) {
                    unlink($rutaAnterior);
                }
            }

            $file = $request->file('archivo_dni');

            // Formato: IDEMPRESA_RUCEMPRESA_ARCHIVO_DNI_NOMBRE_GERENTE.pdf
            $nombreArchivo = $idEmpresa . '_' . $rucEmpresa . '_ARCHIVO_DNI_' . $nombreGerente . '.pdf';

            $file->move(public_path('gerentes/dnis'), $nombreArchivo);
            $gerente->archivo_dni = 'gerentes/dnis/' . $nombreArchivo;
        }

        $gerente->save();

        return response()->json(['success' => true, 'message' => 'Archivos subidos correctamente.']);
    }
}