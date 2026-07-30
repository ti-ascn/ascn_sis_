<?php

namespace App\Http\Controllers;

use App\Models\EmpresasClientes;
use App\Models\Planilla;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlanillaController extends Controller
{
    // Vista principal: listado de empresas
    public function index(Request $request)
    {
        $query = EmpresasClientes::where(function ($q) {
            $q->where('estado_actual', 1)
                ->where('planilla', 1)
                ->whereIn('estado_recursos', ['A1', 'R1']);
        })
            ->orWhere(function ($q) {
                //SE AGREGAN LAS EMPRESAS QUE NO SEAN A1 Y R1 DE ACUERDO A LO QUE SE REQUIERA EN EL SISTEMA, POR AHORA SOLO ESTA EL 33
                $q->whereIn('id_empresas_clientes', [33, 63]);
            });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('razon_social', 'like', "%{$search}%")
                    ->orWhere('ruc', 'like', "%{$search}%");
            });
        }

        $empresas = $query->with('gerenteActivo')
            ->withCount(['planillas as activos_count' => function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNull('fecha_cese')
                        ->orWhere('fecha_cese', '>', now()->format('Y-m-d'));
                });
            }])
            ->orderBy('razon_social')
            ->get();
        $trabajadores = Planilla::leftJoin('empresas_clientes', 'planillas.id_empresas_clientes', '=', 'empresas_clientes.id_empresas_clientes')
            ->select(
                'empresas_clientes.razon_social as empresa',
                'planillas.dni',
                'planillas.nombre_trabajador',
                'planillas.fecha_ingreso',
                'planillas.fecha_cese as salida',
                'planillas.cargo',
                'planillas.sueldo',
                'planillas.referido',
                'planillas.observaciones'
            )
            ->orderBy('planillas.fecha_cese') // activos primero (null arriba)
            ->get();

        // Sincronizar estados activos/inactivos antes de consultar
        \App\Models\ControlDeContratos::where('estado', 'ACTIVO')
            ->whereNotNull('fecha_fin')
            ->where('fecha_fin', '<', now()->format('Y-m-d'))
            ->update(['estado' => 'INACTIVO']);

        // Auto-recuperación: Reactivar contratos vigentes que fueron erróneamente inactivados
        $today = now()->format('Y-m-d');
        $subquery = \App\Models\ControlDeContratos::selectRaw('MAX(id_control) as id_control')
            ->groupBy('id_planilla')
            ->pluck('id_control');

        \App\Models\ControlDeContratos::where('estado', 'INACTIVO')
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

        // Contratos por vencer este mes (último contrato de cada trabajador activo)
        $latestContractIds = \App\Models\ControlDeContratos::selectRaw('MAX(id_control) as id_control')
            ->whereIn('id_planilla', function($q) use ($today) {
                $q->select('id_planilla')
                  ->from('planillas')
                  ->where(function($sq) use ($today) {
                      $sq->whereNull('fecha_cese')
                        ->orWhere('fecha_cese', '>=', $today);
                  });
            })
            ->groupBy('id_planilla')
            ->pluck('id_control');

        $vencimientos = \App\Models\ControlDeContratos::whereIn('id_control', $latestContractIds)
            ->whereNotNull('fecha_fin')
            ->whereYear('fecha_fin', now()->year)
            ->whereMonth('fecha_fin', now()->month)
            ->with(['planilla.empresaCliente'])
            ->get();

        return view('planillas.index', compact('empresas', 'trabajadores', 'vencimientos'));
    }

    // Listar trabajadores de una empresa
    public function trabajadores($id_empresas_clientes)
    {
        $trabajadores = Planilla::where('id_empresas_clientes', $id_empresas_clientes)
            ->orderBy('nombre_trabajador')
            ->get()
            ->map(function ($t) {
                return [
                    'id_planilla' => $t->id_planilla,
                    'dni' => $t->dni,
                    'nombre_trabajador' => $t->nombre_trabajador,
                    'fecha_ingreso' => $t->fecha_ingreso?->format('Y-m-d'),
                    'fecha_cese' => $t->fecha_cese?->format('Y-m-d'),
                    'cargo' => $t->cargo,
                    'sueldo' => $t->sueldo,
                    'referido' => $t->referido,
                    'observaciones' => $t->observaciones,
                    'archivo_dni' => $t->archivo_dni ? asset($t->archivo_dni) : null,
                    'firma_digital' => $t->firma_digital ? asset($t->firma_digital) : null,
                ];
            });

        return response()->json(['trabajadores' => $trabajadores]);
    }

    // Conteo de trabajadores por empresa
    public function conteo()
    {
        $empresas = EmpresasClientes::with('planillas')
            ->where('estado_actual', 1)
            ->whereIn('estado', ['A1', 'R1'])
            ->orderBy('razon_social')
            ->get()
            ->map(function ($empresa) {
                return [
                    'id' => $empresa->id_empresas_clientes,
                    'razon_social' => $empresa->razon_social,
                    'estado' => $empresa->estado,
                    'trabajadores' => $empresa->planillas->map(fn($t) => [
                        'fecha_cese' => $t->fecha_cese?->format('Y-m-d'),
                    ]),
                ];
            });

        return response()->json(['empresas' => $empresas]);
    }

    // Agregar trabajador
    public function store(Request $request)
    {
        $request->validate([
            'id_empresas_clientes' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'dni' => [
                'required',
                'string',
                'max:20',
                Rule::unique('planillas', 'dni')->where(function ($q) use ($request) {
                    return $q->where('id_empresas_clientes', $request->id_empresas_clientes);
                }),
            ],
            'nombre_trabajador' => 'required|string|max:200',
            'fecha_ingreso' => 'required|date',
            'cargo' => 'required|string|max:150',
            'sueldo' => 'required|numeric|min:0',
            'referido' => 'nullable|string|max:200',
            'observaciones' => 'nullable|string',
            'archivo_dni' => 'nullable|file|extensions:pdf|max:5120',
            'firma_digital' => 'nullable|image|max:5120',
            'modalidad' => 'required|string|max:255',
            'fecha_fin' => 'required|date|after_or_equal:fecha_ingreso',
            'detalle' => 'nullable|string',
        ], [
            'dni.unique' => 'El DNI ingresado ya se encuentra registrado para esta empresa.',
        ]);

        $data = $request->only([
            'id_empresas_clientes',
            'dni',
            'nombre_trabajador',
            'fecha_ingreso',
            'cargo',
            'sueldo',
            'referido',
            'observaciones',
            'modalidad',
            'fecha_fin',
            'detalle',
        ]);

        // Convertir a mayúsculas
        $data['nombre_trabajador'] = mb_strtoupper($data['nombre_trabajador']);
        $data['cargo'] = mb_strtoupper($data['cargo']);
        if (isset($data['referido'])) {
            $data['referido'] = mb_strtoupper($data['referido']);
        }

        if ($request->hasFile('archivo_dni')) {
            $nombreLimpio = strtoupper(Str::slug($data['nombre_trabajador'], '_'));
            $extension = $request->file('archivo_dni')->getClientOriginalExtension();
            $idEmpresa = $request->id_empresas_clientes;
            $filename = "{$idEmpresa}_{$request->dni}_{$nombreLimpio}_DNI." . strtolower($extension);
            $request->file('archivo_dni')->move(public_path('archivos_planillas/dni'), $filename);
            $data['archivo_dni'] = 'archivos_planillas/dni/' . $filename;
        }

        if ($request->hasFile('firma_digital')) {
            $nombreLimpio = strtoupper(Str::slug($data['nombre_trabajador'], '_'));
            $extension = $request->file('firma_digital')->getClientOriginalExtension();
            $idEmpresa = $request->id_empresas_clientes;
            $filename = "{$idEmpresa}_{$request->dni}_{$nombreLimpio}_FIRMA." . strtolower($extension);
            $request->file('firma_digital')->move(public_path('archivos_planillas/firmas'), $filename);
            $data['firma_digital'] = 'archivos_planillas/firmas/' . $filename;
        }

        $planilla = Planilla::create($data);

        // Crear automáticamente el primer contrato (CONTRATO)
        $fecha_inicio = \Carbon\Carbon::parse($planilla->fecha_ingreso);
        $fecha_fin = \Carbon\Carbon::parse($request->fecha_fin);
        $detalle_contrato = $request->detalle;

        \App\Models\ControlDeContratos::create([
            'id_planilla' => $planilla->id_planilla,
            'tipo_doc' => 'CONTRATO',
            'modalidad' => mb_strtoupper($request->modalidad ?? 'CONTRATO DE TRABAJO'),
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'detalle' => $detalle_contrato,
            'estado' => 'ACTIVO',
            'fecha_fin_modificada_cese' => false,
        ]);

        return response()->json([
            'message' => 'Trabajador agregado correctamente.',
            'planilla' => $planilla,
        ]);
    }

    // Editar trabajador
    public function update(Request $request, $id)
    {
        $planilla = Planilla::findOrFail($id);

        $request->validate([
            'dni' => [
                'required',
                'string',
                'max:20',
                Rule::unique('planillas', 'dni')->where(function ($q) use ($planilla) {
                    return $q->where('id_empresas_clientes', $planilla->id_empresas_clientes);
                })->ignore($planilla->id_planilla, 'id_planilla'),
            ],
            'nombre_trabajador' => 'required|string|max:200',
            'fecha_ingreso' => 'required|date',
            'fecha_cese' => 'nullable|date|after_or_equal:fecha_ingreso',
            'cargo' => 'required|string|max:150',
            'sueldo' => 'required|numeric|min:0',
            'referido' => 'nullable|string|max:200',
            'observaciones' => 'nullable|string',
            'archivo_dni' => 'nullable|file|extensions:pdf|max:5120',
            'firma_digital' => 'nullable|image|max:5120',
        ], [
            'dni.unique' => 'El DNI ingresado ya se encuentra registrado para esta empresa.',
        ]);

        $data = $request->only([
            'dni',
            'nombre_trabajador',
            'fecha_ingreso',
            'fecha_cese',
            'cargo',
            'sueldo',
            'referido',
            'observaciones',
        ]);

        // Convertir a mayúsculas
        $data['nombre_trabajador'] = mb_strtoupper($data['nombre_trabajador']);
        $data['cargo'] = mb_strtoupper($data['cargo']);
        if (isset($data['referido'])) {
            $data['referido'] = mb_strtoupper($data['referido']);
        }

        if ($request->hasFile('archivo_dni')) {
            if ($planilla->archivo_dni) {
                if (File::exists(public_path($planilla->archivo_dni))) {
                    File::delete(public_path($planilla->archivo_dni));
                }
                if (Storage::disk('public')->exists($planilla->archivo_dni)) {
                    Storage::disk('public')->delete($planilla->archivo_dni);
                }
            }
            $nombreLimpio = strtoupper(Str::slug($data['nombre_trabajador'], '_'));
            $extension = $request->file('archivo_dni')->getClientOriginalExtension();
            $idEmpresa = $planilla->id_empresas_clientes;
            $filename = "{$idEmpresa}_{$request->dni}_{$nombreLimpio}_DNI." . strtolower($extension);
            $request->file('archivo_dni')->move(public_path('archivos_planillas/dni'), $filename);
            $data['archivo_dni'] = 'archivos_planillas/dni/' . $filename;
        }

        if ($request->hasFile('firma_digital')) {
            if ($planilla->firma_digital) {
                if (File::exists(public_path($planilla->firma_digital))) {
                    File::delete(public_path($planilla->firma_digital));
                }
                if (Storage::disk('public')->exists($planilla->firma_digital)) {
                    Storage::disk('public')->delete($planilla->firma_digital);
                }
            }
            $nombreLimpio = strtoupper(Str::slug($data['nombre_trabajador'], '_'));
            $extension = $request->file('firma_digital')->getClientOriginalExtension();
            $idEmpresa = $planilla->id_empresas_clientes;
            $filename = "{$idEmpresa}_{$request->dni}_{$nombreLimpio}_FIRMA." . strtolower($extension);
            $request->file('firma_digital')->move(public_path('archivos_planillas/firmas'), $filename);
            $data['firma_digital'] = 'archivos_planillas/firmas/' . $filename;
        }

        $oldFechaCese = $planilla->fecha_cese;
        $planilla->update($data);

        $newFechaCese = $request->fecha_cese;
        if ($newFechaCese && $newFechaCese != ($oldFechaCese ? $oldFechaCese->format('Y-m-d') : null)) {
            $latestContract = \App\Models\ControlDeContratos::where('id_planilla', $planilla->id_planilla)
                ->orderBy('fecha_inicio', 'desc')
                ->orderBy('id_control', 'desc')
                ->first();

            if ($latestContract) {
                // Determinar si el último contrato es o era indeterminado
                $isLatestIndeterminado = is_null($latestContract->fecha_fin) 
                    || ($latestContract->detalle && str_contains($latestContract->detalle, '<!-- indet -->'));

                if ($isLatestIndeterminado) {
                    // Para indeterminados: se pone en fecha_fin, sin amarillo, sin cambiar detalle
                    $latestContract->fecha_fin = $newFechaCese;
                    $latestContract->fecha_fin_modificada_cese = false;
                    $latestContract->estado = 'INACTIVO';
                    $latestContract->save();
                } else {
                    // Si el contrato ya había sido modificado por cese anteriormente,
                    // primero recuperamos el original de la etiqueta para no pisarlo
                    $pattern = '/\[Fecha fin original\(([^)]+)\) fecha fin modificada\(([^)]+)\)\]\s*/';
                    $originalFin = $latestContract->fecha_fin;
                    $originalFinFmt = $originalFin ? $originalFin->format('d/m/Y') : 'INDETERMINADO';

                    if ($latestContract->fecha_fin_modificada_cese && preg_match($pattern, $latestContract->detalle, $matches)) {
                        $originalFinFmt = $matches[1];
                        if ($originalFinFmt === 'INDETERMINADO') {
                            $originalFin = null;
                        } else {
                            try {
                                $originalFin = \Carbon\Carbon::createFromFormat('d/m/Y', $originalFinFmt);
                            } catch (\Exception $e) {
                                $originalFin = null;
                            }
                        }
                        // Removemos la etiqueta vieja del detalle
                        $latestContract->detalle = preg_replace($pattern, '', $latestContract->detalle);
                    }

                    if (!$originalFin || $originalFin->format('Y-m-d') != $newFechaCese) {
                        $newFinFmt = \Carbon\Carbon::parse($newFechaCese)->format('d/m/Y');

                        $latestContract->fecha_fin = $newFechaCese;
                        $latestContract->fecha_fin_modificada_cese = true;
                        $latestContract->estado = 'INACTIVO';

                        $bracketText = "[Fecha fin original({$originalFinFmt}) fecha fin modificada({$newFinFmt})]";
                        $oldDetalle = $latestContract->detalle;
                        $latestContract->detalle = $oldDetalle ? $bracketText . ' ' . $oldDetalle : $bracketText;
                        $latestContract->save();
                    }
                }
            }
        } elseif (empty($newFechaCese) && $oldFechaCese) {
            // Se eliminó la fecha de cese, restaurar contrato a su estado original
            $latestContract = \App\Models\ControlDeContratos::where('id_planilla', $planilla->id_planilla)
                ->orderBy('fecha_inicio', 'desc')
                ->orderBy('id_control', 'desc')
                ->first();

            if ($latestContract) {
                // Primero check: era indeterminado?
                $isLatestIndeterminado = ($latestContract->detalle && str_contains($latestContract->detalle, '<!-- indet -->'));

                if ($isLatestIndeterminado) {
                    $latestContract->fecha_fin = null;
                    $latestContract->fecha_fin_modificada_cese = false;
                    $latestContract->estado = 'ACTIVO';
                    $latestContract->save();
                } elseif ($latestContract->fecha_fin_modificada_cese) {
                    $pattern = '/\[Fecha fin original\(([^)]+)\) fecha fin modificada\(([^)]+)\)\]\s*/';
                    if (preg_match($pattern, $latestContract->detalle, $matches)) {
                        $originalFinFmt = $matches[1];
                        $restoredFin = null;

                        if ($originalFinFmt !== 'INDETERMINADO') {
                            try {
                                $restoredFin = \Carbon\Carbon::createFromFormat('d/m/Y', $originalFinFmt);
                            } catch (\Exception $e) {
                                $restoredFin = null;
                            }
                        }

                        // Restaurar fecha_fin y desactivar flag de cese
                        $latestContract->fecha_fin = $restoredFin;
                        $latestContract->fecha_fin_modificada_cese = false;

                        // Limpiar la etiqueta de corchetes del detalle
                        $latestContract->detalle = trim(preg_replace($pattern, '', $latestContract->detalle));

                        // Recalcular estado del contrato
                        $estado = 'ACTIVO';
                        if ($restoredFin && $restoredFin->isPast() && !$restoredFin->isToday()) {
                            $estado = 'INACTIVO';
                        }
                        $latestContract->estado = $estado;

                        $latestContract->save();
                    }
                }
            }
        }

        $planilla->refresh();

        return response()->json([
            'message' => 'Trabajador actualizado correctamente.',
            'planilla' => [
                'id_planilla' => $planilla->id_planilla,
                'dni' => $planilla->dni,
                'nombre_trabajador' => $planilla->nombre_trabajador,
                'fecha_ingreso' => $planilla->fecha_ingreso?->format('Y-m-d'),
                'fecha_cese' => $planilla->fecha_cese?->format('Y-m-d'),
                'cargo' => $planilla->cargo,
                'sueldo' => $planilla->sueldo,
                'referido' => $planilla->referido,
                'observaciones' => $planilla->observaciones,
                'archivo_dni' => $planilla->archivo_dni ? asset($planilla->archivo_dni) : null,
                'firma_digital' => $planilla->firma_digital ? asset($planilla->firma_digital) : null,
            ],
        ]);
    }

    // Eliminar un trabajador
    public function destroy($id)
    {
        $planilla = Planilla::findOrFail($id);
        
        if ($planilla->archivo_dni) {
            if (File::exists(public_path($planilla->archivo_dni))) {
                File::delete(public_path($planilla->archivo_dni));
            }
            if (Storage::disk('public')->exists($planilla->archivo_dni)) {
                Storage::disk('public')->delete($planilla->archivo_dni);
            }
        }
        if ($planilla->firma_digital) {
            if (File::exists(public_path($planilla->firma_digital))) {
                File::delete(public_path($planilla->firma_digital));
            }
            if (Storage::disk('public')->exists($planilla->firma_digital)) {
                Storage::disk('public')->delete($planilla->firma_digital);
            }
        }

        $planilla->delete();

        return response()->json(['message' => 'Trabajador eliminado correctamente.']);
    }

    // Eliminar TODOS los trabajadores de una empresa
    public function destroyTodos($id_empresas_clientes)
    {
        Planilla::where('id_empresas_clientes', $id_empresas_clientes)->delete();

        return response()->json(['message' => 'Todos los trabajadores eliminados correctamente.']);
    }

    //Tiene que ver con listar （；´д｀）ゞ
    public function listar()
    {
        $trabajadores = Planilla::select(
            'planillas.*',
            'empresas_clientes.razon_social as empresa'
        )
            ->join('empresas_clientes', 'planillas.id_empresas_clientes', '=', 'empresas_clientes.id_empresas_clientes')
            ->get();

        return view('planillas.listar', compact('trabajadores'));
    }

    // Retornar la vista parcial de alertas de vencimientos
    public function vencimientosAlert()
    {
        // Sincronizar estados activos/inactivos antes de consultar
        \App\Models\ControlDeContratos::where('estado', 'ACTIVO')
            ->whereNotNull('fecha_fin')
            ->where('fecha_fin', '<', now()->format('Y-m-d'))
            ->update(['estado' => 'INACTIVO']);

        // Auto-recuperación: Reactivar contratos vigentes que fueron erróneamente inactivados
        $today = now()->format('Y-m-d');
        $subquery = \App\Models\ControlDeContratos::selectRaw('MAX(id_control) as id_control')
            ->groupBy('id_planilla')
            ->pluck('id_control');

        \App\Models\ControlDeContratos::where('estado', 'INACTIVO')
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

        $latestContractIds = \App\Models\ControlDeContratos::selectRaw('MAX(id_control) as id_control')
            ->whereIn('id_planilla', function($q) use ($today) {
                $q->select('id_planilla')
                  ->from('planillas')
                  ->where(function($sq) use ($today) {
                      $sq->whereNull('fecha_cese')
                        ->orWhere('fecha_cese', '>=', $today);
                  });
            })
            ->groupBy('id_planilla')
            ->pluck('id_control');

        $vencimientos = \App\Models\ControlDeContratos::whereIn('id_control', $latestContractIds)
            ->whereNotNull('fecha_fin')
            ->whereYear('fecha_fin', now()->year)
            ->whereMonth('fecha_fin', now()->month)
            ->with(['planilla.empresaCliente'])
            ->get();

        return view('planillas.partials.vencimientos_alert', compact('vencimientos'));
    }

    // Obtener los datos de vencimientos en JSON para el filtro mensual
    public function vencimientosData(Request $request)
    {
        // Sincronizar antes de consultar
        \App\Models\ControlDeContratos::where('estado', 'ACTIVO')
            ->whereNotNull('fecha_fin')
            ->where('fecha_fin', '<', now()->format('Y-m-d'))
            ->update(['estado' => 'INACTIVO']);

        // Auto-recuperación
        $today = now()->format('Y-m-d');
        $subquery = \App\Models\ControlDeContratos::selectRaw('MAX(id_control) as id_control')
            ->groupBy('id_planilla')
            ->pluck('id_control');

        \App\Models\ControlDeContratos::where('estado', 'INACTIVO')
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

        // Filtrar por mes (formato YYYY-MM)
        // Por defecto es el mes actual
        $periodo = $request->input('periodo', now()->format('Y-m'));
        $parts = explode('-', $periodo);
        $year = $parts[0] ?? now()->year;
        $month = $parts[1] ?? now()->month;

        $latestContractIds = \App\Models\ControlDeContratos::selectRaw('MAX(id_control) as id_control')
            ->whereIn('id_planilla', function($q) use ($today) {
                $q->select('id_planilla')
                  ->from('planillas')
                  ->where(function($sq) use ($today) {
                      $sq->whereNull('fecha_cese')
                        ->orWhere('fecha_cese', '>=', $today);
                  });
            })
            ->groupBy('id_planilla')
            ->pluck('id_control');

        $vencimientos = \App\Models\ControlDeContratos::whereIn('id_control', $latestContractIds)
            ->whereNotNull('fecha_fin')
            ->whereYear('fecha_fin', $year)
            ->whereMonth('fecha_fin', $month)
            ->with(['planilla.empresaCliente'])
            ->get();

        $hoy = \Carbon\Carbon::today();

        $data = $vencimientos->map(function ($c) use ($hoy) {
            $fechaFin = \Carbon\Carbon::parse($c->fecha_fin);
            $diff = $hoy->diffInDays($fechaFin, false);

            if ($diff < 0) {
                $texto_vence = 'Venció hace ' . abs($diff) . ' días';
                $badge_class = 'bg-red-150 text-red-800 border-red-200';
                $color = 'red';
            } elseif ($diff == 0) {
                $texto_vence = 'VENCE HOY';
                $badge_class = 'bg-red-600 text-white border-red-700 animate-pulse';
                $color = 'red';
            } elseif ($diff <= 7) {
                $texto_vence = 'Vence en ' . $diff . ' días';
                $badge_class = 'bg-red-100 text-red-800 border-red-200';
                $color = 'red';
            } elseif ($diff <= 15) {
                $texto_vence = 'Vence en ' . $diff . ' días';
                $badge_class = 'bg-amber-100 text-amber-800 border-amber-200';
                $color = 'amber';
            } else {
                $texto_vence = 'Vence en ' . $diff . ' días';
                $badge_class = 'bg-blue-100 text-blue-800 border-blue-200';
                $color = 'blue';
            }

            return [
                'id_control' => $c->id_control,
                'tipo_doc' => $c->tipo_doc,
                'modalidad' => $c->modalidad,
                'fecha_fin' => $c->fecha_fin->format('d/m/Y'),
                'dias_restantes' => $diff,
                'texto_vence' => $texto_vence,
                'badge_class' => $badge_class,
                'color' => $color,
                'planilla' => [
                    'id_planilla' => $c->planilla->id_planilla,
                    'nombre_trabajador' => $c->planilla->nombre_trabajador,
                    'dni' => $c->planilla->dni,
                    'cargo' => $c->planilla->cargo,
                    'id_empresa' => $c->planilla->id_empresas_clientes,
                    'empresa_nombre' => $c->planilla->empresaCliente->razon_social ?? '—',
                ]
            ];
        });

        return response()->json(['vencimientos' => $data]);
    }
}
