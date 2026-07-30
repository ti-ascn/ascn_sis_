<?php
// app/Http/Controllers/PresentacionController.php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Presentacion;
use App\Models\EmpresasClientes;
use App\Models\ClienteAuditoria;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class PresentacionController extends Controller
{
    /**
     * Guarda o actualiza el formato de presentación de una empresa.
     */
    public function saveFormato(Request $request)
    {
        $request->validate([
            'id_empresa_cliente' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'texto_presentacion' => 'nullable|string',
            'texto_despedida' => 'nullable|string',
            'mostrar_datos' => 'nullable|boolean',
            'info_empresa' => 'nullable|in:cabecera_1,cabecera_2,pie_pagina_1,pie_pagina_2',
            'lugar_info' => 'nullable|in:cabecera_1,cabecera_2,pie_pagina_1,pie_pagina_2',
            'mostrar_info_firma' => 'nullable|boolean',
            'archivo_formato_presentacion' => 'nullable|file|mimes:jpg|max:10240',
            'marca_agua' => 'nullable|boolean',
            'margen_top' => 'nullable|integer|min:0|max:80',
            'margen_bottom' => 'nullable|integer|min:0|max:80',
            'margen_left' => 'nullable|integer|min:0|max:80',
            'margen_right' => 'nullable|integer|min:0|max:80',
            'id_direccion' => 'nullable|integer',
            // NUEVO
            'tipo_presentacion_fecha' => 'nullable|in:ciudad_minuscula_derecha,ciudad_mayuscula_derecha,ciudad_bold_derecha,ciudad_minuscula_izquierda,ciudad_mayuscula_izquierda,ciudad_bold_izquierda,solo_fecha_derecha,solo_fecha_izquierda',
            'tipo_presentacion_destinatario' => 'nullable|in:cargo_subtitulo,cargo_arriba_nombre_abajo,solo_cargo,estimado_cargo_nombre,nombre_bold_cargo_normal,nombre_normal_cargo_bold',
        ]);

        $presentacion = Presentacion::firstOrNew([
            'id_empresa_cliente' => $request->id_empresa_cliente,
        ]);

        $presentacion->texto_presentacion = $request->texto_presentacion;
        $presentacion->texto_despedida = $request->texto_despedida;
        $presentacion->mostrar_datos = $request->boolean('mostrar_datos');
        $presentacion->info_empresa = $request->info_empresa ?: null;
        $presentacion->lugar_info = $request->lugar_info ?: null;
        $presentacion->mostrar_info_firma = $request->boolean('mostrar_info_firma');
        $presentacion->marca_agua = $request->boolean('marca_agua');
        $presentacion->margen_top = $request->input('margen_top', 25);
        $presentacion->margen_bottom = $request->input('margen_bottom', 25);
        $presentacion->margen_left = $request->input('margen_left', 25);
        $presentacion->margen_right = $request->input('margen_right', 25);
        // NUEVO
        $presentacion->tipo_presentacion_fecha = $request->input('tipo_presentacion_fecha', 'ciudad_minuscula_derecha');
        $presentacion->tipo_presentacion_destinatario = $request->input('tipo_presentacion_destinatario', 'cargo_subtitulo');

        // Subir archivo JPG de fondo si viene
        if ($request->hasFile('archivo_formato_presentacion')) {
            $empresaId = $request->id_empresa_cliente;
            $dirPublic = public_path("branding/{$empresaId}");

            if (!file_exists($dirPublic)) {
                mkdir($dirPublic, 0775, true);
            }

            if ($presentacion->archivo_formato_presentacion) {
                @unlink(public_path($presentacion->archivo_formato_presentacion));
            }

            $cachedBg = public_path("branding/{$empresaId}/bg_presentacion.jpg");
            if (file_exists($cachedBg)) {
                @unlink($cachedBg);
            }

            $filename = 'formato_presentacion_' . time() . '.jpg';
            $request->file('archivo_formato_presentacion')->move($dirPublic, $filename);
            $presentacion->archivo_formato_presentacion = "branding/{$empresaId}/{$filename}";
        }

        $presentacion->save();

        return back()->with('success', 'Formato de presentación guardado correctamente.');
    }

    /**
     * Genera el PDF de presentación.
     */
    public function generar(Request $request)
    {
        $request->validate([
            'id_inventario' => 'required|exists:inventario,id_inventario',
            'destinatario_tipo' => 'required|in:empresa,cliente',
            'destinatario_id' => 'required',
            'fecha' => 'required|date',
            'id_direccion' => 'nullable|integer',
        ]);

        $inv = Inventario::with([
            'empresa.branding',
            'empresa.presentacion',
            'empresa.direcciones',
        ])->findOrFail($request->id_inventario);

        // ── Destinatario ─────────────────────────────────────────────────────
        $id = (int) $request->destinatario_id;
        $dest = $request->destinatario_tipo === 'empresa'
            ? EmpresasClientes::select('razon_social', 'ruc')->findOrFail($id)
            : ClienteAuditoria::select('razon_social', 'ruc')->findOrFail($id);

        $branding = optional($inv->empresa->branding);
        $presentacion = $inv->empresa->presentacion;

        // ── Fecha en español ─────────────────────────────────────────────────
        $fechaCarbon = Carbon::parse($request->fecha)->locale('es');

        // NUEVO: construir fecha_html según tipo_presentacion_fecha
        $tipoFecha = $presentacion->tipo_presentacion_fecha ?? 'ciudad_minuscula_derecha';

        $esMayuscula = str_contains($tipoFecha, 'mayuscula');
        $esBold = str_contains($tipoFecha, 'bold');
        $esDerecha = str_contains($tipoFecha, 'derecha');
        $sinCiudad = str_starts_with($tipoFecha, 'solo_fecha');

        $fechaStr = $esMayuscula
            ? strtoupper($fechaCarbon->translatedFormat('d \\de F \\de Y'))
            : $fechaCarbon->translatedFormat('d \\de F \\de Y');
        $ciudadStr = $esMayuscula ? 'TRUJILLO' : 'Trujillo';

        if ($sinCiudad) {
            $fecha_html = $esBold ? "<strong>{$fechaStr}</strong>" : $fechaStr;
        } else {
            $texto = "{$ciudadStr}, {$fechaStr}";
            $fecha_html = $esBold ? "<strong>{$texto}</strong>" : $texto;
        }

        $fecha_alineacion = $esDerecha ? 'right' : 'left';

        // ── Reemplazar placeholder $empresa ──────────────────────────────────
        $razon = "<strong>" . strtoupper($inv->empresa->razon_social) . "</strong>";

        $texto_presentacion = $presentacion
            ? str_replace('$empresa', $razon, $presentacion->texto_presentacion ?? '')
            : '';

        $texto_despedida = $presentacion
            ? str_replace('$empresa', $razon, $presentacion->texto_despedida ?? '')
            : '';

        // ── Dirección seleccionada (viene del modal) o primera activa ────────
        $direccionFiscal = null;
        if ($request->id_direccion) {
            $direccionFiscal = $inv->empresa->direcciones()
                ->where('id_direccion', $request->id_direccion)
                ->first();
        }
        if (!$direccionFiscal) {
            $direccionFiscal = $inv->empresa->direcciones()
                ->where('estado', 1)
                ->orderBy('id_direccion')
                ->first();
        }

        $direccion_texto = $direccionFiscal
            ? trim(
                $direccionFiscal->nombre . ' - ' .
                $direccionFiscal->distrito . ' - ' .
                $direccionFiscal->provincia . ' - ' .
                $direccionFiscal->region
            )
            : null;

        // ── Branding / estilos ───────────────────────────────────────────────
        $schemes = [
            'blue' => ['header' => '#EFF6FF', 'note_bg' => '#F0F9FF', 'note_border' => '#DBEAFE', 'pill_bg' => '#E5EDFF'],
            'sky' => ['header' => '#E0F2FE', 'note_bg' => '#F0F9FF', 'note_border' => '#BAE6FD', 'pill_bg' => '#E0F2FE'],
            'emerald' => ['header' => '#D1FAE5', 'note_bg' => '#ECFDF5', 'note_border' => '#A7F3D0', 'pill_bg' => '#D1FAE5'],
            'teal' => ['header' => '#CCFBF1', 'note_bg' => '#F0FDFA', 'note_border' => '#99F6E4', 'pill_bg' => '#CCFBF1'],
            'amber' => ['header' => '#FEF3C7', 'note_bg' => '#FFFBEB', 'note_border' => '#FDE68A', 'pill_bg' => '#FEF3C7'],
            'orange' => ['header' => '#FFEDD5', 'note_bg' => '#FFF7ED', 'note_border' => '#FED7AA', 'pill_bg' => '#FFEDD5'],
            'red' => ['header' => '#FEE2E2', 'note_bg' => '#FEF2F2', 'note_border' => '#FCA5A5', 'pill_bg' => '#FEE2E2'],
            'violet' => ['header' => '#EDE9FE', 'note_bg' => '#F5F3FF', 'note_border' => '#DDD6FE', 'pill_bg' => '#EDE9FE'],
            'fuchsia' => ['header' => '#F5D0FE', 'note_bg' => '#FAE8FF', 'note_border' => '#F0ABFC', 'pill_bg' => '#F5D0FE'],
            'gray' => ['header' => '#F3F4F6', 'note_bg' => '#F9FAFB', 'note_border' => '#E5E7EB', 'pill_bg' => '#F3F4F6'],
        ];

        $schemeKey = $branding->color ?: 'gray';
        $scheme = $schemes[$schemeKey] ?? $schemes['gray'];

        // Fuente desde branding (igual que cotizaciones)
        $font = $branding->fuente ?: 'DejaVu Sans';

        $style = [
            'font_family' => $font,
            'text' => '#111827',
            'header' => $scheme['header'],
            'note_bg' => $scheme['note_bg'],
            'note_border' => $scheme['note_border'],
            'pill_bg' => $scheme['pill_bg'],
        ];

        // ── Config de layout ─────────────────────────────────────────────────
        $mostrarDatos = $presentacion ? $presentacion->mostrar_datos : false;
        $infoEmpresa = $presentacion ? $presentacion->info_empresa : null;
        $lugarInfo = $presentacion ? $presentacion->lugar_info : null;
        $mostrarInfoFirma = $presentacion ? $presentacion->mostrar_info_firma : true;
        $marcaAgua = $presentacion ? $presentacion->marca_agua : false;

        // Márgenes (en mm, convertidos a puntos para DomPDF: 1mm ≈ 2.8346pt)
        $margenTop = $presentacion ? ($presentacion->margen_top ?? 25) : 25;
        $margenBottom = $presentacion ? ($presentacion->margen_bottom ?? 25) : 25;
        $margenLeft = $presentacion ? ($presentacion->margen_left ?? 25) : 25;
        $margenRight = $presentacion ? ($presentacion->margen_right ?? 25) : 25;

        // NUEVO: tipo destinatario
        $tipoDestinatario = $presentacion->tipo_presentacion_destinatario ?? 'cargo_subtitulo';

        // ── Imagen de fondo ──────────────────────────────────────────────────
        $imagenFondoBase64 = null;

        if ($presentacion && !empty($presentacion->archivo_formato_presentacion)) {
            $jpgPath = public_path($presentacion->archivo_formato_presentacion);

            Log::info('Intentando cargar JPG de fondo:', [
                'path' => $jpgPath,
                'exists' => file_exists($jpgPath),
            ]);

            if (file_exists($jpgPath)) {
                $type = pathinfo($jpgPath, PATHINFO_EXTENSION);
                $data = file_get_contents($jpgPath);
                $imagenFondoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        // Logo para marca de agua
        $logoBase64 = null;
        if ($marcaAgua && $branding->logo_empresa) {
            $logoPath = public_path($branding->logo_empresa);
            if (file_exists($logoPath)) {
                $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
                $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $rawWeb = $inv->empresa->pagina_web;
        $web = $rawWeb ? (preg_match('#^https?://#i', $rawWeb) ? $rawWeb : 'https://' . $rawWeb) : null;

        // ── Generar PDF ──────────────────────────────────────────────────────
        $pdf = Pdf::loadView('pdf.presentacion', [
            'inventario' => $inv,
            'destinatario' => $dest,
            'fecha_html' => $fecha_html,               // NUEVO (reemplaza fecha_texto)
            'fecha_alineacion' => $fecha_alineacion,   // NUEVO
            'tipoDestinatario' => $tipoDestinatario,   // NUEVO
            'texto_presentacion' => $texto_presentacion,
            'texto_despedida' => $texto_despedida,
            'mostrarDatos' => $mostrarDatos,
            'infoEmpresa' => $infoEmpresa,
            'lugarInfo' => $lugarInfo,
            'mostrarInfoFirma' => $mostrarInfoFirma,
            'marcaAgua' => $marcaAgua,
            'logoBase64' => $logoBase64,
            'direccion' => $direccion_texto,
            'correo' => $inv->empresa->correo,
            'celular' => $inv->empresa->nro_celular,
            'style' => $style,
            'imagenFondoBase64' => $imagenFondoBase64,
            'web' => $web,
            // Márgenes en mm para el blade
            'margenTop' => $margenTop,
            'margenBottom' => $margenBottom,
            'margenLeft' => $margenLeft,
            'margenRight' => $margenRight,
        ])->setPaper('A4');

        $nombre = 'Presentacion_' . $inv->nombre_codigo . '_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->stream($nombre);
    }
}