<?php

namespace App\Http\Controllers;

use App\Models\ControlDeContratos;
use App\Models\Planilla;
use App\Models\EmpresasClientes;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ControlDeContratosController extends Controller
{
    // Obtener los contratos de los trabajadores de una empresa
    public function getPorEmpresa($id_empresa)
    {
        // Sincronizar estados activos/inactivos antes de consultar
        ControlDeContratos::where('estado', 'ACTIVO')
            ->whereNotNull('fecha_fin')
            ->where('fecha_fin', '<', Carbon::now()->format('Y-m-d'))
            ->update(['estado' => 'INACTIVO']);

        // Auto-recuperación: Reactivar contratos vigentes que fueron erróneamente inactivados
        $today = Carbon::now()->format('Y-m-d');
        $subquery = ControlDeContratos::selectRaw('MAX(id_control) as id_control')
            ->groupBy('id_planilla')
            ->pluck('id_control');

        ControlDeContratos::where('estado', 'INACTIVO')
            ->whereIn('id_control', $subquery)
            ->where(function($q) use ($today) {
                $q->whereNull('fecha_fin')
                  ->orWhere('fecha_fin', '>=', $today);
            })
            ->whereIn('id_planilla', function($q) use ($today) {
                $q->select('id_planilla')
                  ->from('planillas')
                  ->where(function($sq) use ($today) {
                      $sq->whereNull('fecha_cese')
                        ->orWhere('fecha_cese', '>=', $today);
                  });
            })
            ->update(['estado' => 'ACTIVO']);

        $empresa = EmpresasClientes::findOrFail($id_empresa);

        // Obtener trabajadores de la empresa con sus respectivos controles de contratos
        $trabajadores = Planilla::where('id_empresas_clientes', $id_empresa)
            ->with(['contratosControl' => function ($query) {
                $query->orderBy('fecha_inicio', 'asc')->orderBy('id_control', 'asc');
            }])
            ->orderBy('nombre_trabajador', 'asc')
            ->get();

        return response()->json([
            'empresa' => [
                'razon_social' => $empresa->razon_social,
                'ruc' => $empresa->ruc,
            ],
            'trabajadores' => $trabajadores->map(function ($t) {
                return [
                    'id_planilla' => $t->id_planilla,
                    'dni' => $t->dni,
                    'nombre_trabajador' => $t->nombre_trabajador,
                    'fecha_cese' => $t->fecha_cese ? $t->fecha_cese->format('Y-m-d') : null,
                    'contratos' => $t->contratosControl->map(function ($c) use ($t) {
                        $es_indeterminado = is_null($c->fecha_fin) || ($c->detalle && str_contains($c->detalle, '<!-- indet -->'));

                        return [
                            'id_control' => $c->id_control,
                            'tipo_doc' => $c->tipo_doc,
                            'modalidad' => $c->modalidad,
                            'sueldo' => $c->sueldo,
                            'cargo' => $c->cargo ?? $t->cargo,
                            'fecha_inicio' => $c->fecha_inicio ? $c->fecha_inicio->format('Y-m-d') : null,
                            'fecha_fin' => $c->fecha_fin ? $c->fecha_fin->format('Y-m-d') : null,
                            'detalle' => trim(str_replace('<!-- indet -->', '', $c->detalle)),
                            'estado' => $c->estado,
                            'fecha_fin_modificada_cese' => $c->fecha_fin_modificada_cese,
                            'es_indeterminado' => $es_indeterminado,
                        ];
                    }),
                ];
            })
        ]);
    }

    // Sugerir datos para el siguiente contrato (tipo doc y fecha inicio)
    public function siguienteContrato($id_planilla)
    {
        $planilla = Planilla::findOrFail($id_planilla);
        $count = ControlDeContratos::where('id_planilla', $id_planilla)->count();

        $ultimoContrato = ControlDeContratos::where('id_planilla', $id_planilla)
            ->orderBy('fecha_inicio', 'desc')
            ->orderBy('id_control', 'desc')
            ->first();

        // Determinar tipo de documento y sugerencias
        $sugerencia_adenda = '';
        $sugerencia_contrato = '';

        if ($count === 0) {
            $siguiente_tipo_doc = 'CONTRATO';
            $sugerencia_contrato = 'CONTRATO';
            $fecha_sugerida_inicio = $planilla->fecha_ingreso ? $planilla->fecha_ingreso->format('Y-m-d') : Carbon::now()->format('Y-m-d');
            $fecha_min_inicio = $planilla->fecha_ingreso ? $planilla->fecha_ingreso->format('Y-m-d') : null;
        } else {
            // Obtener todos los documentos del trabajador ordenados cronológicamente
            $documentos = ControlDeContratos::where('id_planilla', $id_planilla)
                ->orderBy('fecha_inicio', 'asc')
                ->orderBy('id_control', 'asc')
                ->get();

            // Encontrar el último contrato registrado y contar los contratos
            $ultimoContratoIndex = -1;
            $numContratos = 0;
            foreach ($documentos as $index => $doc) {
                if (strpos(strtoupper($doc->tipo_doc), 'CONTRATO') === 0) {
                    $ultimoContratoIndex = $index;
                    $numContratos++;
                }
            }

            if ($numContratos === 0) {
                $sugerencia_contrato = 'CONTRATO 2';
                // Si no hay contratos, contamos todas las adendas
                $adendasDesdeUltimoContrato = $documentos->filter(function($doc) {
                    return strpos(strtoupper($doc->tipo_doc), 'ADENDA') === 0;
                })->count();
            } else {
                $sugerencia_contrato = 'CONTRATO ' . ($numContratos + 1);
                // Contar cuántas adendas hay después del último contrato
                $adendasDesdeUltimoContrato = 0;
                for ($i = $ultimoContratoIndex + 1; $i < count($documentos); $i++) {
                    if (strpos(strtoupper($documentos[$i]->tipo_doc), 'ADENDA') === 0) {
                        $adendasDesdeUltimoContrato++;
                    }
                }
            }

            $sugerencia_adenda = 'ADENDA ' . str_pad($adendasDesdeUltimoContrato + 1, 2, '0', STR_PAD_LEFT);
            
            // Por defecto, sugerir la adenda
            $siguiente_tipo_doc = $sugerencia_adenda;

            // Sugerir fecha de inicio: día siguiente al fin del último contrato
            if ($ultimoContrato && $ultimoContrato->fecha_fin) {
                $fecha_sugerida_inicio = $ultimoContrato->fecha_fin->addDay()->format('Y-m-d');
                $fecha_min_inicio = $fecha_sugerida_inicio;
            } elseif ($ultimoContrato) {
                // Si el último es indeterminado o no tiene fecha fin, se sugiere el día siguiente a su inicio
                $fecha_sugerida_inicio = $ultimoContrato->fecha_inicio->addDay()->format('Y-m-d');
                $fecha_min_inicio = $fecha_sugerida_inicio;
            } else {
                $fecha_sugerida_inicio = Carbon::now()->format('Y-m-d');
                $fecha_min_inicio = null;
            }
        }

        // Determinar si ya es indeterminado
        $isIndeterminado = ($ultimoContrato && (is_null($ultimoContrato->fecha_fin) || ($ultimoContrato->detalle && str_contains($ultimoContrato->detalle, '<!-- indet -->'))));

        $puede_agregar = true;
        $mensaje = '';

        if ($isIndeterminado) {
            $puede_agregar = false;
            $mensaje = 'El trabajador ya se encuentra en estado INDETERMINADO, no se pueden agregar más contratos o adendas.';
        } elseif ($ultimoContrato && is_null($ultimoContrato->fecha_fin)) {
            $puede_agregar = false;
            $mensaje = 'No se puede agregar una adenda o contrato si el anterior no tiene una fecha de fin establecida.';
        }

        return response()->json([
            'trabajador' => [
                'nombre_trabajador' => $planilla->nombre_trabajador,
                'dni' => $planilla->dni,
                'cargo' => $planilla->cargo,
            ],
            'siguiente_tipo_doc' => $siguiente_tipo_doc,
            'sugerencia_adenda' => $sugerencia_adenda,
            'sugerencia_contrato' => $sugerencia_contrato,
            'fecha_sugerida_inicio' => $fecha_sugerida_inicio,
            'fecha_min_inicio' => $fecha_min_inicio,
            'puede_agregar' => $puede_agregar,
            'mensaje' => $mensaje,
            'must_be_indeterminado' => $isIndeterminado,
            'sueldo_sugerido' => $ultimoContrato ? $ultimoContrato->sueldo : $planilla->sueldo,
        ]);
    }

    public function store(Request $request)
    {
        $id_planilla = $request->id_planilla;
        $esIndeterminadoNuevo = $request->boolean('es_indeterminado');

        $existingContracts = ControlDeContratos::where('id_planilla', $id_planilla)
            ->orderBy('fecha_inicio', 'asc')
            ->orderBy('id_control', 'asc')
            ->get();

        $planilla = Planilla::findOrFail($id_planilla);
        $ultimoContrato = $existingContracts->last();

        // Validación: no se puede agregar si el anterior no tiene fecha_fin y no es indeterminado, ni si ya es indeterminado
        $isIndeterminado = ($ultimoContrato && (is_null($ultimoContrato->fecha_fin) || ($ultimoContrato->detalle && str_contains($ultimoContrato->detalle, '<!-- indet -->'))));

        if ($isIndeterminado) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'fecha_inicio' => ['El trabajador ya se encuentra en estado INDETERMINADO, no se pueden agregar más contratos o adendas.']
                ]
            ], 422);
        }

        if ($ultimoContrato && is_null($ultimoContrato->fecha_fin)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'fecha_fin' => ['No se puede agregar una adenda o contrato si el anterior no tiene una fecha de fin establecida.']
                ]
            ], 422);
        }



        // Reglas de validación: fecha_fin es obligatoria a menos que sea indeterminado
        $rules = [
            'id_planilla' => 'required|exists:planillas,id_planilla',
            'tipo_doc' => 'nullable|string|max:50',
            'modalidad' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'sueldo' => 'required|numeric|min:0',
            'cargo' => 'required|string|max:150',
            'detalle' => 'nullable|string',
        ];

        if ($esIndeterminadoNuevo) {
            $rules['fecha_fin'] = 'nullable|date|after_or_equal:fecha_inicio';
        } else {
            $rules['fecha_fin'] = 'required|date|after_or_equal:fecha_inicio';
        }

        $request->validate($rules);

        $fecha_inicio = Carbon::parse($request->fecha_inicio);
        $fecha_fin = $request->fecha_fin ? Carbon::parse($request->fecha_fin) : null;

        // Determinar tipo de documento
        $tipo_doc = $request->tipo_doc ? mb_strtoupper($request->tipo_doc) : null;
        if (!$tipo_doc) {
            $count = ControlDeContratos::where('id_planilla', $id_planilla)->count();
            $tipo_doc = ($count === 0) ? 'CONTRATO' : 'ADENDA ' . str_pad($count, 2, '0', STR_PAD_LEFT);
        }

        $detalle = $request->detalle;
        if ($esIndeterminadoNuevo) {
            $detalle = $detalle ? $detalle . ' <!-- indet -->' : '<!-- indet -->';
        }

        // Determinar estado
        $estado = 'ACTIVO';
        if (!$esIndeterminadoNuevo && $fecha_fin && $fecha_fin->isPast() && !$fecha_fin->isToday()) {
            $estado = 'INACTIVO';
        }

        // Si el trabajador ya fue cesado (fecha_cese registrada en el pasado)
        if ($planilla->fecha_cese && $planilla->fecha_cese->isPast()) {
            $estado = 'INACTIVO';
        }

        // Desactivar todos los contratos/adendas anteriores de este trabajador
        ControlDeContratos::where('id_planilla', $id_planilla)
            ->update(['estado' => 'INACTIVO']);

        $contrato = ControlDeContratos::create([
            'id_planilla' => $id_planilla,
            'tipo_doc' => $tipo_doc,
            'modalidad' => mb_strtoupper($request->modalidad),
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'sueldo' => $request->sueldo,
            'cargo' => mb_strtoupper($request->cargo),
            'detalle' => $detalle,
            'estado' => $estado,
            'fecha_fin_modificada_cese' => false,
        ]);

        // Actualizar el sueldo y cargo en el listado general (tabla planillas)
        $planilla->update([
            'sueldo' => $request->sueldo,
            'cargo' => mb_strtoupper($request->cargo),
        ]);

        return response()->json([
            'success' => true,
            'message' => $esIndeterminadoNuevo 
                ? 'Control de contrato agregado correctamente como INDETERMINADO.' 
                : 'Control de contrato agregado correctamente.',
            'contrato' => $contrato,
        ]);
    }

    // Eliminar un control de contrato
    public function destroy($id)
    {
        $contrato = ControlDeContratos::findOrFail($id);
        $id_planilla = $contrato->id_planilla;

        $planilla = \App\Models\Planilla::findOrFail($id_planilla);
        if ($planilla->fecha_cese) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el contrato o adenda de un trabajador cesado.'
            ], 422);
        }

        // Validar que sea el contrato/adenda más reciente de este trabajador
        $ultimoContrato = ControlDeContratos::where('id_planilla', $id_planilla)
            ->orderBy('fecha_inicio', 'desc')
            ->orderBy('id_control', 'desc')
            ->first();

        if ($ultimoContrato && $ultimoContrato->id_control !== $contrato->id_control) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se puede eliminar el contrato o adenda más reciente de este trabajador.'
            ], 422);
        }

        $contrato->delete();

        // Reactivar el nuevo último contrato si existe y no ha vencido
        $nuevoUltimo = ControlDeContratos::where('id_planilla', $id_planilla)
            ->orderBy('fecha_inicio', 'desc')
            ->orderBy('id_control', 'desc')
            ->first();

        if ($nuevoUltimo) {
            $nuevoEstado = 'ACTIVO';
            if ($nuevoUltimo->fecha_fin && $nuevoUltimo->fecha_fin->isPast() && !$nuevoUltimo->fecha_fin->isToday()) {
                $nuevoEstado = 'INACTIVO';
            }
            $nuevoUltimo->update(['estado' => $nuevoEstado]);
            
            // Revertir sueldo y cargo al del nuevo último contrato
            $planilla->update([
                'sueldo' => $nuevoUltimo->sueldo,
                'cargo' => $nuevoUltimo->cargo,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Control de contrato eliminado correctamente.'
        ]);
    }

    // Actualizar un control de contrato (solo si es el más reciente)
    public function update(Request $request, $id)
    {
        $contrato = ControlDeContratos::findOrFail($id);
        $id_planilla = $contrato->id_planilla;
        $planilla = Planilla::findOrFail($id_planilla);

        // Validar que sea el contrato/adenda más reciente de este trabajador
        $ultimoContrato = ControlDeContratos::where('id_planilla', $id_planilla)
            ->orderBy('fecha_inicio', 'desc')
            ->orderBy('id_control', 'desc')
            ->first();

        if ($ultimoContrato->id_control !== $contrato->id_control) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se puede editar el contrato o adenda más reciente de este trabajador.'
            ], 422);
        }

        $esIndeterminadoNuevo = $request->boolean('es_indeterminado');

        // Buscar el penúltimo contrato (el que era anterior a este)
        $penultimoContrato = ControlDeContratos::where('id_planilla', $id_planilla)
            ->where('id_control', '!=', $id)
            ->orderBy('fecha_inicio', 'desc')
            ->orderBy('id_control', 'desc')
            ->first();



        $rules = [
            'tipo_doc' => 'required|string|max:50',
            'modalidad' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'sueldo' => 'required|numeric|min:0',
            'cargo' => 'required|string|max:150',
            'detalle' => 'nullable|string',
        ];

        if ($esIndeterminadoNuevo) {
            $rules['fecha_fin'] = 'nullable|date|after_or_equal:fecha_inicio';
        } else {
            $rules['fecha_fin'] = 'required|date|after_or_equal:fecha_inicio';
        }

        $request->validate($rules);

        $fecha_inicio = Carbon::parse($request->fecha_inicio);
        $fecha_fin = $request->fecha_fin ? Carbon::parse($request->fecha_fin) : null;

        $detalle = $request->detalle;
        if ($esIndeterminadoNuevo) {
            $detalle = $detalle ? $detalle . ' <!-- indet -->' : '<!-- indet -->';
        }

        // Determinar estado
        $estado = 'ACTIVO';
        if (!$esIndeterminadoNuevo && $fecha_fin && $fecha_fin->isPast() && !$fecha_fin->isToday()) {
            $estado = 'INACTIVO';
        }

        // Si el trabajador ya fue cesado
        if ($planilla->fecha_cese && $planilla->fecha_cese->isPast()) {
            $estado = 'INACTIVO';
        }

        $contrato->update([
            'tipo_doc' => mb_strtoupper($request->tipo_doc),
            'modalidad' => mb_strtoupper($request->modalidad),
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'sueldo' => $request->sueldo,
            'cargo' => mb_strtoupper($request->cargo),
            'detalle' => $detalle,
            'estado' => $estado,
        ]);

        // Actualizar el sueldo y cargo en el listado general (tabla planillas)
        $planilla->update([
            'sueldo' => $request->sueldo,
            'cargo' => mb_strtoupper($request->cargo),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Control de contrato actualizado correctamente.',
            'contrato' => $contrato,
        ]);
    }
}
