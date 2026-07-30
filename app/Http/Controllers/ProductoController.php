<?php

namespace App\Http\Controllers;

use App\Models\PrecioDolar;
use App\Models\UnidadMedida;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Inventario;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::with('inventario.empresa')->get();
        return view('producto.index', compact('productos'));
    }

    public function porInventario($id)
    {
        $inventario = Inventario::with('empresa')->findOrFail($id);

        $productos = Producto::where('id_inventario', $id)
            ->where('estado', 1) // solo productos activos
            ->get();

        $precioDolar = PrecioDolar::first();

        $usuariosAuditoria = \App\Models\User::where('tipo_usuario', 3)
            ->where('id_area', 7)
            ->get();

        // Obtener todos los IDs de productos activos
        $todosProductosIDs = $productos->pluck('id_producto');

        return view('productos.index_por_inventario', compact(
            'usuariosAuditoria',
            'inventario',
            'productos',
            'precioDolar',
            'todosProductosIDs' // Pasamos los IDs a la vista
        ));
    }

    public function create($id_inventario)
    {
        $inventario = Inventario::with('empresa')->findOrFail($id_inventario);
        return view('productos.create', compact('inventario'));
    }


    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'id_inventario' => 'required|exists:inventario,id_inventario',
                'nombre' => 'required|string|max:255',
                'unidad_medida' => 'required|string|max:50',
                'precio_unitario' => 'required|numeric|min:0',
                'peso' => 'nullable|numeric|min:0',
                'estado' => 'required|boolean',
            ]);

            $inventario = Inventario::findOrFail($validated['id_inventario']);

            // Verificar si ya existe un producto con el mismo nombre (case insensitive)
            $existe = Producto::where('id_inventario', $inventario->id_inventario)
                ->whereRaw('LOWER(nombre) = LOWER(?)', [$validated['nombre']])
                ->exists();

            if ($existe) {
                return response()->json([
                    'success' => false,
                    'error' => 'Ya existe un producto con ese nombre en este inventario.'
                ], 422);
            }

            // Generar el código del producto
            $prefijo = $inventario->nombre_codigo;
            $ultimoCodigo = Producto::where('id_inventario', $inventario->id_inventario)
                ->where('id_producto', 'LIKE', "{$prefijo}%")
                ->orderByRaw("CAST(SUBSTRING(id_producto, LENGTH(?) + 1) AS UNSIGNED) DESC", [$prefijo])
                ->value('id_producto');

            $ultimoNumero = $ultimoCodigo ? intval(substr($ultimoCodigo, strlen($prefijo))) : 0;
            $validated['id_producto'] = $prefijo . ($ultimoNumero + 1);
            $validated['nombre'] = mb_strtoupper($validated['nombre']);
            $validated['unidad_medida'] = mb_strtoupper($validated['unidad_medida']);

            // Crear el producto
            $producto = Producto::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'producto' => $producto,
                'message' => "Producto creado: {$validated['id_producto']}"
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear producto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }


    public function edit($id)
    {
        $producto = Producto::with('inventario.empresa')->findOrFail($id);
        $unidades = UnidadMedida::all();
        return view('productos.edit', compact('producto', 'unidades'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'id_inventario' => 'required|exists:inventario,id_inventario',
            'nombre' => 'required|string|max:255',
            'unidad_medida' => 'required|string|max:50',
            'precio_unitario' => 'required|numeric|min:0',
            'peso' => 'nullable|numeric|min:0',
            'estado' => 'required|boolean',
        ]);

        $producto = Producto::findOrFail($id);
        $validated['nombre'] = mb_strtoupper($validated['nombre']);
        $validated['unidad_medida'] = mb_strtoupper($validated['unidad_medida']);
        $producto->update($validated);

        return redirect()
            ->route('productos.porInventario', $producto->id_inventario)
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        // Desactivar el producto
        $producto->estado = 0;
        $producto->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Producto desactivado correctamente'
            ]);
        }

        return back()->with('success', 'Producto desactivado correctamente.');
    }

    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt',
            'id_inventario' => 'required|exists:inventario,id_inventario',
        ]);

        $file = fopen($request->file('archivo')->getRealPath(), 'r');
        $header = fgetcsv($file); // Salta el encabezado

        $inventario = Inventario::findOrFail($request->id_inventario);

        while (($row = fgetcsv($file, 1000, ';')) !== false) {
            if (count($row) < 4)
                continue;

            $codigo = strtoupper(trim($row[0])); // usa la columna A como id_producto
            $unidad = mb_strtoupper(trim($row[1]));
            $nombre = mb_strtoupper(trim($row[2]));
            $precio = floatval(preg_replace('/[^\d.]/', '', $row[3]));
            $peso = isset($row[4]) && is_numeric($row[4]) ? floatval($row[4]) : null;

            Producto::updateOrCreate(
                ['id_producto' => $codigo], // Usa código del Excel
                [
                    'id_inventario' => $inventario->id_inventario,
                    'nombre' => $nombre,
                    'unidad_medida' => $unidad,
                    'precio_unitario' => $precio,
                    'peso' => $peso,
                    'estado' => 1,
                ]
            );
        }

        fclose($file);
        return back()->with('success', 'Productos importados correctamente.');
    }

    public function vaciar(Request $request)
    {
        $request->validate([
            'id_inventario' => 'required|exists:inventario,id_inventario',
        ]);

        $productos = Producto::where('id_inventario', $request->id_inventario);

        if (!$productos->exists()) {
            return back()->with('error', 'No hay productos para eliminar en este inventario.');
        }

        $productos->delete();

        return back()->with('success', 'Todos los productos del inventario fueron eliminados.');
    }

    public function transferir(Request $request)
    {
        // Validar si el usuario tipo 3 tiene permiso
        $usuario = auth()->user();

        if (
            $usuario->tipo_usuario == 3 &&
            ($usuario->id_area != 7 || !$usuario->puede_editar)
        ) {
            return back()->with('error', 'No tienes permiso para transferir productos.');
        }

        $validated = $request->validate([
            'productos' => 'required|array',
            'productos.*' => 'string|exists:producto,id_producto',
            'id_inventario_destino' => 'required|exists:inventario,id_inventario',
        ]);

        $inventarioDestino = Inventario::findOrFail($validated['id_inventario_destino']);
        $prefijo = $inventarioDestino->nombre_codigo;

        // Obtener el último número usado con el prefijo
        $ultimoCodigo = Producto::where('id_inventario', $inventarioDestino->id_inventario)
            ->where('id_producto', 'LIKE', "{$prefijo}%")
            ->orderByRaw("CAST(SUBSTRING(id_producto, LENGTH(?) + 1) AS UNSIGNED) DESC", [$prefijo])
            ->value('id_producto');

        $ultimoNumero = $ultimoCodigo ? intval(substr($ultimoCodigo, strlen($prefijo))) : 0;

        foreach ($validated['productos'] as $codigoProducto) {
            $original = Producto::findOrFail($codigoProducto);
            $nuevoCodigo = $prefijo . (++$ultimoNumero);

            // Crear el nuevo producto en el inventario destino
            Producto::create([
                'id_producto' => $nuevoCodigo,
                'id_inventario' => $inventarioDestino->id_inventario,
                'nombre' => $original->nombre,
                'unidad_medida' => $original->unidad_medida,
                'precio_unitario' => $original->precio_unitario,
                'peso' => $original->peso,
                'estado' => 1,
            ]);

            // Desactivar el original
            $original->estado = 0;
            $original->save();
        }

        return back()->with('success', 'Productos transferidos correctamente.');
    }

    public function duplicar(Request $request)
    {
        // Validación de permisos (igual que transferir)
        $usuario = auth()->user();
        if ($usuario->tipo_usuario == 3 && ($usuario->id_area != 7 || !$usuario->puede_editar)) {
            return back()->with('error', 'No tienes permiso para duplicar productos.');
        }

        $validated = $request->validate([
            'id_inventario_origen' => 'required|exists:inventario,id_inventario',
            'id_inventario_destino' => 'required|exists:inventario,id_inventario|different:id_inventario_origen',
        ]);

        $inventarioOrigen = Inventario::with('empresa')->findOrFail($validated['id_inventario_origen']);
        $inventarioDestino = Inventario::with('empresa')->findOrFail($validated['id_inventario_destino']);

        $prefijoDestino = $inventarioDestino->nombre_codigo;
        $prefijoOrigen = $inventarioOrigen->nombre_codigo;

        // Productos activos del ORIGEN en orden por su número
        $productosOrigen = Producto::where('id_inventario', $inventarioOrigen->id_inventario)
            ->where('estado', 1)
            ->orderByRaw("CAST(SUBSTRING(id_producto, LENGTH(?) + 1) AS UNSIGNED) ASC", [$prefijoOrigen])
            ->get();

        if ($productosOrigen->isEmpty()) {
            return back()->with('error', 'El inventario origen no tiene productos activos para duplicar.');
        }

        // Set de nombres existentes en DESTINO (case-insensitive, sin filtrar por estado,
        // para que coincida con tu validación de store())
        $nombresDestino = Producto::where('id_inventario', $inventarioDestino->id_inventario)
            ->pluck('nombre')
            ->map(fn($n) => mb_strtolower(trim($n)))
            ->all();
        $nombresDestinoSet = array_fill_keys($nombresDestino, true);

        DB::beginTransaction();
        try {
            // Último correlativo existente en el DESTINO
            $ultimoCodigoDestino = Producto::where('id_inventario', $inventarioDestino->id_inventario)
                ->where('id_producto', 'LIKE', "{$prefijoDestino}%")
                ->orderByRaw("CAST(SUBSTRING(id_producto, LENGTH(?) + 1) AS UNSIGNED) DESC", [$prefijoDestino])
                ->value('id_producto');

            $ultimoNumero = $ultimoCodigoDestino
                ? intval(substr($ultimoCodigoDestino, strlen($prefijoDestino)))
                : 0;

            $creados = 0;
            $omitidos = 0;
            $omitidosNombres = [];

            foreach ($productosOrigen as $original) {
                $nombreKey = mb_strtolower(trim($original->nombre));

                // Si el nombre ya existe en el destino, omitir
                if (isset($nombresDestinoSet[$nombreKey])) {
                    $omitidos++;
                    if (count($omitidosNombres) < 10) { // muestra algunos ejemplos en el flash
                        $omitidosNombres[] = $original->nombre;
                    }
                    continue;
                }

                // Crear con el siguiente correlativo disponible
                $nuevoCodigo = $prefijoDestino . ($ultimoNumero + 1);

                Producto::create([
                    'id_producto' => $nuevoCodigo,
                    'id_inventario' => $inventarioDestino->id_inventario,
                    'nombre' => $original->nombre,
                    'unidad_medida' => $original->unidad_medida,
                    'precio_unitario' => $original->precio_unitario,
                    'peso' => $original->peso,
                    'estado' => 1,
                ]);

                // Actualizar correlativo y set de nombres
                $ultimoNumero++;
                $creados++;
                $nombresDestinoSet[$nombreKey] = true;
            }

            DB::commit();

            $msg = "Se duplicaron {$creados} productos de {$inventarioOrigen->nombre_codigo} ({$inventarioOrigen->empresa->razon_social}) a {$inventarioDestino->nombre_codigo} ({$inventarioDestino->empresa->razon_social}).";
            if ($omitidos > 0) {
                $ejemplos = $omitidosNombres ? ' (p.ej.: ' . implode(', ', $omitidosNombres) . ')' : '';
                $msg .= " Se omitieron {$omitidos} por nombre duplicado{$ejemplos}.";
            }

            return back()->with('success', $msg);

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al duplicar: ' . $e->getMessage());
        }
    }

    public function updateUnidadMedida(Request $request, $id)
    {
        $request->validate([
            'unidad_medida' => 'required|string|max:50',
        ]);

        $producto = Producto::findOrFail($id);
        $producto->unidad_medida = $request->unidad_medida;
        $producto->save();

        return response()->json([
            'success' => true,
            'producto' => $producto,
        ]);
    }

    public function updateAjax(Request $request, $id)
    {
        try {
            $producto = Producto::findOrFail($id);

            $validated = $request->validate([
                'nombre' => 'sometimes|string|max:255',
                'precio_unitario' => 'sometimes|numeric|min:0',
                'peso' => 'nullable|numeric|min:0',
                'unidad_medida' => 'sometimes|string|max:50',
            ]);

            if (isset($validated['nombre'])) {
                $validated['nombre'] = mb_strtoupper($validated['nombre']);
            }

            if (isset($validated['unidad_medida'])) {
                $validated['unidad_medida'] = mb_strtoupper($validated['unidad_medida']);
            }

            $producto->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Guardado correctamente',
                'producto' => $producto
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function activar(Request $request)
    {
        $validated = $request->validate([
            'productos' => 'required|array',
            'productos.*' => 'string|exists:producto,id_producto',
            'id_inventario' => 'required|exists:inventario,id_inventario',
        ]);

        $activados = 0;
        foreach ($validated['productos'] as $id_producto) {
            $producto = Producto::where('id_producto', $id_producto)
                ->where('id_inventario', $validated['id_inventario'])
                ->first();
            
            if ($producto && $producto->estado == 0) {
                $producto->estado = 1;
                $producto->save();
                $activados++;
            }
        }

        return back()->with('success', "Se activaron {$activados} productos correctamente.");
    }


}
