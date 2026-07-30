<?php

namespace App\Http\Controllers;

use App\Models\RecepcionDocumento;
use App\Models\EmpresasClientes;
use Illuminate\Http\Request;

class RecepcionDocumentoController extends Controller
{
    // Guardar los datos del formulario
    public function store(Request $request)
    {
        $request->validate([
            'id_empresas_clientes' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'estado' => 'required|in:solicitud,recepcion,entrega',
            'tipo_contacto' => 'nullable|in:WHATSAPP,LLAMADA',
            'tipo_documento' => 'nullable|in:DIGITAL,FISICO',
            'observacion' => 'nullable|string',
            'fecha' => 'required|date_format:Y-m', // Validar que el formato sea Y-m
            'opcion' => 'nullable|in:Si,No,N/A', // Validar la nueva columna
        ]);

        // Convertir el valor de fecha a Y-m-01 (primer día del mes)
        $fechaCompleta = $request->fecha . '-01';

        // Obtener el último registro de la empresa para el período seleccionado
        $ultimoRegistro = RecepcionDocumento::where('id_empresas_clientes', $request->id_empresas_clientes)
            ->whereDate('fecha', '>=', $fechaCompleta)
            ->whereDate('fecha', '<=', \Carbon\Carbon::parse($fechaCompleta)->endOfMonth())
            ->orderBy('created_at', 'desc')
            ->first();

        // Verificar si el último registro es una entrega
        if ($ultimoRegistro && $ultimoRegistro->estado === 'entrega') {
            $nombreEmpresa = $ultimoRegistro->empresaCliente->razon_social;
            $nombrePeriodo = ucfirst(\Carbon\Carbon::parse($fechaCompleta)->isoFormat('MMMM YYYY'));
            return redirect()->back()
                ->with('error', "Ya has registrado solicitudes, recepción y entrega para la empresa $nombreEmpresa en el período $nombrePeriodo.");
        }

        $datos = [
            'id_empresas_clientes' => $request->id_empresas_clientes,
            'estado' => $request->estado,
            'observacion' => $request->observacion,
            'fecha' => $fechaCompleta, // Guardar la fecha en formato Y-m-d
            'opcion' => $request->opcion, // Guardar la opción seleccionada
        ];

        // Lógica para cada estado
        if ($request->estado === 'solicitud') {
            $datos['tipo_contacto'] = $request->tipo_contacto;
            $datos['tipo_documento'] = null;
        } elseif ($request->estado === 'recepcion') {
            $datos['tipo_documento'] = $request->tipo_documento;
            $datos['tipo_contacto'] = null;
        } elseif ($request->estado === 'entrega') {
            $datos['tipo_contacto'] = null;
            $datos['tipo_documento'] = null;
        }

        RecepcionDocumento::create($datos);

        return redirect()->back()->with('success', 'Acción registrada correctamente.');
    }

    // Mostrar el historial de recepción de documentos

    public function verRegistros($idEmpresa)
    {
        // Obtener la empresa
        $empresa = EmpresasClientes::find($idEmpresa);
        if (!$empresa) {
            return redirect()->back()->with('error', 'Empresa no encontrada.');
        }

        // Obtener todos los registros de la empresa
        $todosRegistros = RecepcionDocumento::where('id_empresas_clientes', $idEmpresa)
            ->orderBy('created_at', 'desc')
            ->get();

        // Obtener los últimos 3 registros de SOLICITUD
        $registrosSolicitud = RecepcionDocumento::where('id_empresas_clientes', $idEmpresa)
            ->where('estado', 'solicitud')
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->get();

        // Obtener el último registro de RECEPCIÓN
        $ultimaRecepcion = RecepcionDocumento::where('id_empresas_clientes', $idEmpresa)
            ->where('estado', 'recepcion')
            ->orderBy('created_at', 'desc')
            ->first();

        // Obtener el último registro de ENTREGA
        $ultimaEntrega = RecepcionDocumento::where('id_empresas_clientes', $idEmpresa)
            ->where('estado', 'entrega')
            ->orderBy('created_at', 'desc')
            ->first();

        // Lógica para determinar el estado de los botones
        $botonSolicitudActivo = false;
        $botonRecepcionActivo = false;
        $botonEntregaActivo = false;

        if ($todosRegistros->count() > 0) {
            $ultimoRegistro = $todosRegistros->first();

            // Si el último registro es una entrega
            if ($ultimoRegistro->estado === 'entrega') {
                $botonSolicitudActivo = true;
            }
            // Si el último registro es una recepción
            elseif ($ultimoRegistro->estado === 'recepcion') {
                $botonEntregaActivo = true;
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
                    $botonSolicitudActivo = true;
                    $botonRecepcionActivo = true;
                }
                // Si hay 3 solicitudes consecutivas
                else {
                    $botonRecepcionActivo = true;
                }
            }
        } else {
            // Si no hay registros, activar solo el botón de solicitud
            $botonSolicitudActivo = true;
        }

        return view('recepcion_documentos.registros', compact(
            'todosRegistros',
            'empresa',
            'registrosSolicitud',
            'ultimaRecepcion',
            'ultimaEntrega',
            'botonSolicitudActivo',
            'botonRecepcionActivo',
            'botonEntregaActivo'
        ));
    }

    public function index()
    {
        // Obtener todos los registros con la relación de empresa
        $documentos = RecepcionDocumento::with('empresaCliente')->orderBy('created_at', 'desc')->get();

        // Agregar la columna UD calculada
        foreach ($documentos as $documento) {
            $documento->ud = substr($documento->empresaCliente->ruc, -1); // Último dígito del RUC
        }

        return view('recepcion_documentos.index', compact('documentos'));
    }

    public function edit($id)
    {
        // Buscar el registro por su ID
        $registro = RecepcionDocumento::find($id);

        // Verificar si el registro existe
        if (!$registro) {
            return redirect()->back()->with('error', 'Registro no encontrado.');
        }

        // Obtener la empresa asociada al registro
        $empresa = EmpresasClientes::find($registro->id_empresas_clientes);

        // Cargar la vista de edición con los datos del registro y la empresa
        return view('recepcion_documentos.edit', compact('registro', 'empresa'));
    }

    public function update(Request $request, $id)
    {
        // Validar los datos del formulario
        $request->validate([
            'tipo_contacto' => 'nullable|in:WHATSAPP,LLAMADA',
            'tipo_documento' => 'nullable|in:DIGITAL,FISICO',
            'observacion' => 'nullable|string',
            'fecha' => 'required|date_format:Y-m', // Validar que el formato sea Y-m
            'opcion' => 'nullable|in:Si,No,N/A', // Validar la nueva columna
        ]);

        // Convertir el valor de fecha a Y-m-01 (primer día del mes)
        $fechaCompleta = $request->fecha . '-01';

        // Buscar el registro por su ID
        $registro = RecepcionDocumento::find($id);

        // Verificar si el registro existe
        if (!$registro) {
            return redirect()->back()->with('error', 'Registro no encontrado.');
        }

        // Actualizar los campos del registro
        $registro->update([
            'tipo_contacto' => $request->tipo_contacto,
            'tipo_documento' => $request->tipo_documento,
            'observacion' => $request->observacion,
            'fecha' => $fechaCompleta, // Guardar la fecha en formato Y-m-d
            'opcion' => $request->opcion, // Actualizar la opción seleccionada
        ]);

        // Redirigir con un mensaje de éxito
        return redirect()->route('recepcion.documentos.registros_mes_actual', $registro->id_empresas_clientes)
            ->with('success', 'Registro actualizado correctamente.');
    }

    public function verPorPeriodo(Request $request)
    {
        $request->validate([
            'id_empresas_clientes' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'fecha' => 'required|date_format:Y-m',
        ]);

        $empresa = EmpresasClientes::findOrFail($request->id_empresas_clientes);
        $fecha = $request->fecha . '-01'; // Convertir a primer día del mes

        $documentos = RecepcionDocumento::where('id_empresas_clientes', $empresa->id_empresas_clientes)
            ->whereDate('fecha', '>=', $fecha)
            ->whereDate('fecha', '<=', \Carbon\Carbon::parse($fecha)->endOfMonth())
            ->get();

        return view('recepcion_documentos.por_periodo', compact('documentos', 'empresa', 'fecha'));
    }

    public function empresasC1(Request $request)
    {
        // Validar el período seleccionado
        $request->validate([
            'fecha' => 'required|date_format:Y-m',
        ]);

        // Obtener el período seleccionado
        $fecha = $request->fecha . '-01'; // Convertir a primer día del mes

        // Obtener todas las empresas con estado "C1"
        $empresas = EmpresasClientes::where('estado', 'C1')
            ->where('estado_actual', 1)
            ->where('manejar_recepcion', 1)
            ->get();

        // Array para almacenar los datos de cada empresa
        $datosEmpresas = [];

        foreach ($empresas as $empresa) {
            // Obtener los registros de la empresa para el período seleccionado
            $registros = RecepcionDocumento::where('id_empresas_clientes', $empresa->id_empresas_clientes)
                ->whereDate('fecha', '>=', $fecha)
                ->whereDate('fecha', '<=', \Carbon\Carbon::parse($fecha)->endOfMonth())
                ->orderBy('fecha', 'asc')
                ->get();

            // Inicializar variables para las solicitudes, recepción y entrega
            $solicitudes = [];
            $recepcion = null;
            $entrega = null;

            // Recorrer los registros y clasificarlos
            foreach ($registros as $registro) {
                if ($registro->estado === 'solicitud') {
                    $solicitudes[] = $registro->opcion ?? '-'; // Usar "opcion" o "-" si no hay
                } elseif ($registro->estado === 'recepcion') {
                    $recepcion = $registro->opcion ?? 'SIN REGISTRO';
                } elseif ($registro->estado === 'entrega') {
                    $entrega = $registro->opcion ?? 'SIN REGISTRO';
                }
            }

            // Determinar si hay una recepción registrada
            $hayRecepcion = !is_null($recepcion) && $recepcion !== 'SIN REGISTRO';

            // Asegurar que haya 3 solicitudes
            while (count($solicitudes) < 3) {
                // Si hay recepción, rellenar con "-". De lo contrario, con "SIN REGISTRO"
                $solicitudes[] = $hayRecepcion ? '-' : 'SIN REGISTRO';
            }

            // Ordenar las solicitudes de la más antigua a la más reciente
            $solicitudes = array_slice($solicitudes, -3); // Tomar las últimas 3

            // Agregar los datos de la empresa al array
            $datosEmpresas[] = [
                'ud' => substr($empresa->ruc, -1), // Último dígito del RUC
                'empresa' => $empresa->razon_social,
                'solicitud_1' => $solicitudes[0] ?? ($hayRecepcion ? '-' : 'SIN REGISTRO'),
                'solicitud_2' => $solicitudes[1] ?? ($hayRecepcion ? '-' : 'SIN REGISTRO'),
                'solicitud_3' => $solicitudes[2] ?? ($hayRecepcion ? '-' : 'SIN REGISTRO'),
                'recepcion' => $recepcion ?? 'SIN REGISTRO',
                'entrega' => $entrega ?? 'SIN REGISTRO',
            ];
        }

        // Retornar la vista con los datos
        return view('recepcion_documentos.empresas_c1', compact('datosEmpresas', 'fecha'));
    }

}