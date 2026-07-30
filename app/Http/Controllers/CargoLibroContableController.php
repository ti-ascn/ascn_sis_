<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\CargoLibroContable;
use App\Models\EstatusLibroContable;
use App\Models\Libro;
use App\Models\EmpresasClientes;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PDF;

class CargoLibroContableController extends Controller
{
    public function index($id_estatus)
    {
        $cargos = CargoLibroContable::with('usuario')->where('id_estatus_libros_contables', $id_estatus)->orderBy('fecha', 'desc')->get();
        $estatus = EstatusLibroContable::findOrFail($id_estatus);
        return response()->json([
            'cargos' => $cargos,
            'estatus' => $estatus,
        ]);
    }

    public function show($id)
    {
        $cargo = CargoLibroContable::findOrFail($id);
        return response()->json([
            'success' => true,
            'cargo' => $cargo,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_estatus_libros_contables' => 'required|exists:estatus_libros_contables,id_estatus_libros',
            'tipo' => 'required|in:recepcion,entrega',
            'nro_cargo' => 'nullable|string',
            'fecha' => 'required|date',
            'archivo' => 'nullable|file|max:10240',
            'libros_seleccionados' => 'nullable|array',
            'nro_libro' => 'nullable|array',
            'folio_libro' => 'nullable|array',
            'libro_cargo_recepcion' => 'nullable|array',
            'libro_cargo_recepcion.*.nombre' => 'nullable|string|max:255',
            'libro_cargo_recepcion.*.nro_libro' => 'nullable|string|max:100',
            'libro_cargo_recepcion.*.folio_libro' => 'nullable|string|max:100',
        ]);

        $idsLibrosSeleccionados = $request->libros_seleccionados ?? [];
        if (empty($idsLibrosSeleccionados) && empty($request->libro_cargo_recepcion)) {
            return response()->json(['success' => false, 'message' => 'Debe seleccionar al menos un libro o agregar un libro adicional.'], 422);
        }

        $estatus = EstatusLibroContable::findOrFail($request->id_estatus_libros_contables);
        $detalles = $estatus->detalles_libros ?? [];

        $librosCargo = [];
        $nroLibroCargo = [];
        $folioLibroCargo = [];

        foreach ($idsLibrosSeleccionados as $idLibro) {
            $librosCargo[] = (int) $idLibro;
            if (isset($request->nro_libro[$idLibro])) {
                $nroLibroCargo[$idLibro] = $request->nro_libro[$idLibro];
            }
            if (isset($request->folio_libro[$idLibro])) {
                $folioLibroCargo[$idLibro] = $request->folio_libro[$idLibro];
            }
            if ($request->tipo === 'recepcion') {
                if (!isset($detalles[$idLibro])) {
                    $detalles[$idLibro] = [];
                }
                $detalles[$idLibro]['entregado_por_cliente'] = 'si';
            } else if ($request->tipo === 'entrega') {
                if (!isset($detalles[$idLibro])) {
                    $detalles[$idLibro] = [];
                }
                $detalles[$idLibro]['entregado_al_cliente'] = 'si';
            }
        }

        // Procesar libros de recepción adicionales (texto libre)
        $libroCargosRecepcion = [];
        if ($request->has('libro_cargo_recepcion') && is_array($request->libro_cargo_recepcion)) {
            foreach ($request->libro_cargo_recepcion as $lcr) {
                $nombre = trim($lcr['nombre'] ?? '');
                if ($nombre !== '') {
                    $libroCargosRecepcion[] = [
                        'nombre'     => $nombre,
                        'nro_libro'  => trim($lcr['nro_libro'] ?? ''),
                        'folio_libro'=> trim($lcr['folio_libro'] ?? ''),
                    ];
                }
            }
        }

        $data = [
            'tipo' => $request->tipo,
            'nro_cargo' => $request->nro_cargo,
            'id_estatus_libros_contables' => $request->id_estatus_libros_contables,
            'libros' => $librosCargo,
            'nro_libro' => $nroLibroCargo,
            'folio_libro' => $folioLibroCargo,
            'libro_cargo_recepcion' => !empty($libroCargosRecepcion) ? $libroCargosRecepcion : null,
            'fecha' => date('Y-m-d H:i:s', strtotime($request->fecha)),
            'id_usuario' => Auth::id(),
        ];

        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $empresa = $estatus->empresaCliente;
            $razon = Str::slug($empresa->razon_social ?? 'empresa');
            $timestamp = now()->format('Ymd_His');
            $tipoStr = strtoupper($request->tipo);
            $nombreFinal = "CARGO_{$tipoStr}_LIBROS_{$razon}_{$timestamp}." . $file->getClientOriginalExtension();
            $subcarpeta = 'archivos_libros';
            $carpeta = public_path($subcarpeta);
            if (!is_dir($carpeta)) {
                mkdir($carpeta, 0755, true);
            }
            $file->move($carpeta, $nombreFinal);
            $data['archivo'] = "{$subcarpeta}/{$nombreFinal}";
        }

        $cargo = CargoLibroContable::create($data);

        // Update the main estatus record with the new book details
        $estatus->detalles_libros = $detalles;
        $estatus->save();

        return response()->json(['success' => true, 'cargo' => $cargo, 'message' => 'Cargo creado exitosamente.']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tipo' => 'required|in:recepcion,entrega',
            'nro_cargo' => 'nullable|string',
            'fecha' => 'required|date',
            'archivo' => 'nullable|file|max:10240',
            'libros_seleccionados' => 'nullable|array',
            'nro_libro' => 'nullable|array',
            'folio_libro' => 'nullable|array',
            'libro_cargo_recepcion' => 'nullable|array',
            'libro_cargo_recepcion.*.nombre' => 'nullable|string|max:255',
            'libro_cargo_recepcion.*.nro_libro' => 'nullable|string|max:100',
            'libro_cargo_recepcion.*.folio_libro' => 'nullable|string|max:100',
        ]);

        $idsLibrosSeleccionados = $request->libros_seleccionados ?? [];
        if (empty($idsLibrosSeleccionados) && empty($request->libro_cargo_recepcion)) {
            return response()->json(['success' => false, 'message' => 'Debe seleccionar al menos un libro o agregar un libro adicional.'], 422);
        }

        $cargo = CargoLibroContable::findOrFail($id);
        $estatus = EstatusLibroContable::findOrFail($cargo->id_estatus_libros_contables);
        $detalles = $estatus->detalles_libros ?? [];

        // 1. Revertir estados de libros asignados previamente en este cargo
        $oldLibros = $cargo->libros ?? [];
        $oldTipo = $cargo->tipo;
        foreach ($oldLibros as $idLibro) {
            if ($oldTipo === 'recepcion') {
                if (isset($detalles[$idLibro])) {
                    $detalles[$idLibro]['entregado_por_cliente'] = 'no';
                }
            } else if ($oldTipo === 'entrega') {
                if (isset($detalles[$idLibro])) {
                    $detalles[$idLibro]['entregado_al_cliente'] = 'no';
                }
            }
        }

        // 2. Procesar nuevos libros seleccionados y actualizar detalles en estatus
        $librosCargo = [];
        $nroLibroCargo = [];
        $folioLibroCargo = [];

        foreach ($idsLibrosSeleccionados as $idLibro) {
            $librosCargo[] = (int) $idLibro;
            if (isset($request->nro_libro[$idLibro])) {
                $nroLibroCargo[$idLibro] = $request->nro_libro[$idLibro];
            }
            if (isset($request->folio_libro[$idLibro])) {
                $folioLibroCargo[$idLibro] = $request->folio_libro[$idLibro];
            }
            if ($request->tipo === 'recepcion') {
                if (!isset($detalles[$idLibro])) {
                    $detalles[$idLibro] = [];
                }
                $detalles[$idLibro]['entregado_por_cliente'] = 'si';
            } else if ($request->tipo === 'entrega') {
                if (!isset($detalles[$idLibro])) {
                    $detalles[$idLibro] = [];
                }
                $detalles[$idLibro]['entregado_al_cliente'] = 'si';
            }
        }

        // 3. Procesar libros adicionales (libro_cargo_recepcion)
        $libroCargosRecepcion = [];
        if ($request->has('libro_cargo_recepcion') && is_array($request->libro_cargo_recepcion)) {
            foreach ($request->libro_cargo_recepcion as $lcr) {
                $nombre = trim($lcr['nombre'] ?? '');
                if ($nombre !== '') {
                    $libroCargosRecepcion[] = [
                        'nombre'     => $nombre,
                        'nro_libro'  => trim($lcr['nro_libro'] ?? ''),
                        'folio_libro'=> trim($lcr['folio_libro'] ?? ''),
                    ];
                }
            }
        }

        // 4. Actualizar campos del cargo
        $cargo->tipo = $request->tipo;
        $cargo->nro_cargo = $request->nro_cargo;
        $cargo->libros = $librosCargo;
        $cargo->nro_libro = $nroLibroCargo;
        $cargo->folio_libro = $folioLibroCargo;
        $cargo->libro_cargo_recepcion = !empty($libroCargosRecepcion) ? $libroCargosRecepcion : null;
        $cargo->fecha = date('Y-m-d H:i:s', strtotime($request->fecha));

        if ($request->hasFile('archivo')) {
            if ($cargo->archivo && file_exists(public_path($cargo->archivo))) {
                unlink(public_path($cargo->archivo));
            }

            $file = $request->file('archivo');
            $empresa = $estatus->empresaCliente;
            $razon = Str::slug($empresa->razon_social ?? 'empresa');
            $timestamp = now()->format('Ymd_His');
            $tipoStr = strtoupper($request->tipo);
            $nombreFinal = "CARGO_{$tipoStr}_LIBROS_{$razon}_{$timestamp}." . $file->getClientOriginalExtension();
            $subcarpeta = 'archivos_libros';
            $carpeta = public_path($subcarpeta);
            if (!is_dir($carpeta)) {
                mkdir($carpeta, 0755, true);
            }
            $file->move($carpeta, $nombreFinal);
            $cargo->archivo = "{$subcarpeta}/{$nombreFinal}";
        }

        $cargo->save();

        // 5. Guardar detalles actualizados en estatus
        $estatus->detalles_libros = $detalles;
        $estatus->save();

        return response()->json(['success' => true, 'cargo' => $cargo, 'message' => 'Cargo actualizado exitosamente.']);
    }

    public function destroy($id)
    {
        $cargo = CargoLibroContable::findOrFail($id);

        if ($cargo->archivo && file_exists(public_path($cargo->archivo))) {
            unlink(public_path($cargo->archivo));
        }

        // Revert book statuses
        $estatus = EstatusLibroContable::find($cargo->id_estatus_libros_contables);
        if ($estatus) {
            $detalles = $estatus->detalles_libros ?? [];
            $librosCargo = $cargo->libros ?? [];
            foreach ($librosCargo as $idLibro) {
                if ($cargo->tipo === 'recepcion') {
                    if (isset($detalles[$idLibro])) {
                        $detalles[$idLibro]['entregado_por_cliente'] = 'no';
                    }
                } else if ($cargo->tipo === 'entrega') {
                    if (isset($detalles[$idLibro])) {
                        $detalles[$idLibro]['entregado_al_cliente'] = 'no';
                    }
                }
            }
            $estatus->detalles_libros = $detalles;
            $estatus->save();
        }

        $cargo->delete();

        return response()->json(['success' => true, 'message' => 'Cargo eliminado.']);
    }

    public function generarPdf($id)
    {
        $cargo = CargoLibroContable::with(['estatusLibros.empresaCliente'])->findOrFail($id);
        $estatus = $cargo->estatusLibros;

        $librosBase = Libro::whereIn('id_libro', $cargo->libros ?? [])->get();
        $detalles = $estatus->detalles_libros ?? [];

        $librosFiltrados = [];

        foreach ($librosBase as $libro) {
            $idLibro = $libro->id_libro;
            $detalle = $detalles[$idLibro] ?? [];
            $isElectronico = (isset($detalle['is_electronico']) && ($detalle['is_electronico'] == 1 || $detalle['is_electronico'] === true)) ? true : false;

            $librosFiltrados[] = [
                'nombre' => $libro->nombre,
                'is_electronico' => $isElectronico,
                'nro_libro' => $cargo->nro_libro[$idLibro] ?? null,
                'folio_libro' => $cargo->folio_libro[$idLibro] ?? null,
            ];
        }

        $tipo = strtolower($cargo->tipo);
        $tipoStr = strtoupper($cargo->tipo);
        $razonSocial = strtoupper(Str::slug($estatus->empresaCliente->razon_social ?? 'empresa'));

        $data = [
            'razonSocial'           => $razonSocial,
            'empresa'               => $estatus->empresaCliente,
            'nro_cargo'             => $cargo->nro_cargo,
            'fecha'                 => $cargo->fecha,
            'tipo'                  => $tipo,
            'libros'                => $librosFiltrados,
            'libro_cargo_recepcion' => $cargo->libro_cargo_recepcion ?? [],
        ];

        $pdf = PDF::loadView('pdf.cargo_libros', $data);
        return $pdf->stream("CARGO_{$tipoStr}_LIBROS_CONTABLES_{$razonSocial}.pdf");
    }
}
