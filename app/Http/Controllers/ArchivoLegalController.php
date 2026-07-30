<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ArchivoLegal;
use App\Models\EmpresasClientes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArchivoLegalController extends Controller
{
    public function index()
    {
        // Solo empresas con estado A1 y estado_actual = 1 o 2
        $empresas = EmpresasClientes::where('estado', 'A1')
            ->whereIn('estado_actual', [1, 2])
            ->get();
        return view('archivos_legales.index', compact('empresas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_empresa_cliente' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'tipo_archivo' => 'required|string',
            'archivo' => 'required|file|extensions:pdf,jpg,jpeg,png,doc,docx|max:40960',
        ]);

        try {
            $empresa = EmpresasClientes::findOrFail($request->id_empresa_cliente);
            $tipo = $request->tipo_archivo;

            // Obtener el máximo contador existente para este tipo y empresa
            // (evita colisiones si se borró un archivo intermedio)
            $maxContador = ArchivoLegal::where('id_empresa_cliente', $empresa->id_empresas_clientes)
                ->where('tipo_archivo', $tipo)
                ->get()
                ->map(function ($a) {
                    // Extraer el último número del nombre del archivo (ej: ..._3.pdf → 3)
                    $filename = pathinfo($a->archivo, PATHINFO_FILENAME);
                    if (preg_match('/_(\d+)$/', $filename, $matches)) {
                        return (int) $matches[1];
                    }
                    return 0;
                })
                ->max() ?? 0;

            $contador = $maxContador + 1;

            $file = $request->file('archivo');
            $extension = $file->getClientOriginalExtension();

            // Formato: RAZONSOCIAL_TIPO_RUC_CONTADOR
            $razonSocialClean = Str::slug($empresa->razon_social, '_');
            $tipoClean = Str::slug($tipo, '_');

            $nombreArchivo = strtoupper($razonSocialClean . '_' . $tipoClean . '_' . $empresa->ruc . '_' . $contador);
            $fileNameWithExt = $nombreArchivo . '.' . $extension;

            // Guardar en public/legal_archivos
            $file->move(public_path('legal_archivos'), $fileNameWithExt);
            $path = 'legal_archivos/' . $fileNameWithExt;

            ArchivoLegal::create([
                'archivo' => $path,
                'tipo_archivo' => $tipo,
                'id_usuario' => Auth::id(),
                'id_empresa_cliente' => $empresa->id_empresas_clientes,
            ]);

            return response()->json(['success' => true, 'message' => 'Archivo subido correctamente.']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getArchivosByEmpresa($id)
    {
        $archivos = ArchivoLegal::where('id_empresa_cliente', $id)
            ->with('usuario')
            ->get()
            ->map(function ($archivo) {
                // Formatear el nombre para mostrar (el nombre del archivo sin la extensión y con el contador)
                $pathInfo = pathinfo($archivo->archivo);
                return [
                    'id' => $archivo->id_archivo_legal,
                    'nombre' => $pathInfo['filename'],
                    'tipo' => $archivo->tipo_archivo,
                    'url' => asset($archivo->archivo),
                    'usuario' => optional($archivo->usuario)->name ?? 'N/A',
                    'fecha' => $archivo->created_at->format('d/m/Y H:i'),
                ];
            });

        return response()->json($archivos);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'archivo' => 'required|file|extensions:pdf,jpg,jpeg,png,doc,docx|max:40960',
        ]);

        try {
            $archivoLegal = ArchivoLegal::findOrFail($id);

            // Eliminar archivo anterior
            if (file_exists(public_path($archivoLegal->archivo))) {
                unlink(public_path($archivoLegal->archivo));
            }

            // Guardar nuevo archivo (usando el mismo nombre que tenía)
            $file = $request->file('archivo');
            $extension = $file->getClientOriginalExtension();

            // Obtenemos el nombre sin extensión del path guardado
            $pathInfo = pathinfo($archivoLegal->archivo);
            $fileNameWithExt = $pathInfo['filename'] . '.' . $extension;

            $file->move(public_path('legal_archivos'), $fileNameWithExt);
            $path = 'legal_archivos/' . $fileNameWithExt;

            $archivoLegal->update([
                'archivo' => $path,
                'id_usuario' => Auth::id(), // Actualizar quién lo reemplazó
            ]);

            return response()->json(['success' => true, 'message' => 'Archivo reemplazado correctamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al reemplazar: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        // Solo tipo_usuario == 1 puede eliminar
        if (Auth::user()->tipo_usuario != 1) {
            return response()->json(['success' => false, 'message' => 'No tiene permisos para eliminar.'], 403);
        }

        try {
            $archivoLegal = ArchivoLegal::findOrFail($id);

            // Eliminar archivo físico
            if (file_exists(public_path($archivoLegal->archivo))) {
                unlink(public_path($archivoLegal->archivo));
            }

            $archivoLegal->delete();

            return response()->json(['success' => true, 'message' => 'Archivo eliminado correctamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }
}
