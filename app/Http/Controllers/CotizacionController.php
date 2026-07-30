<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\EmpresasClientes;
use App\Models\ClienteAuditoria;
use App\Models\PrecioDolar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class CotizacionController extends Controller
{
    public function generar(Request $r)
    {
        $r->validate([
            'id_inventario' => 'required|exists:inventario,id_inventario',
            'destinatario_tipo' => 'required|in:empresa,cliente',
            'destinatario_id' => 'required',
            'moneda' => 'required|in:PEN,USD',
            'presentacion' => 'nullable|string',
            'despedida' => 'nullable|string',
        ]);

        $inv = Inventario::with([
            'empresa.branding',
            'empresa.direcciones', // << añadimos direcciones
            'productos' => fn($q) => $q->where('estado', 1)->orderBy('nombre')
        ])->findOrFail($r->id_inventario);

        // Destinatario
        $id = (int) $r->destinatario_id;
        $dest = $r->destinatario_tipo === 'empresa'
            ? EmpresasClientes::select('razon_social', 'ruc')->findOrFail($id)
            : ClienteAuditoria::select('razon_social', 'ruc')->findOrFail($id);

        // Moneda y TC
        $simbolo = $r->moneda === 'USD' ? '$' : 'S/';
        $tc = $r->moneda === 'USD'
            ? (float) (PrecioDolar::latest('id_precio')->value('precio') ?? 0)
            : 1.0;
        if ($r->moneda === 'USD' && $tc <= 0) {
            return back()->with('error', 'No hay precio del dólar registrado.');
        }

        // Cuentas activas
        $cuentas = $inv->empresa->cuentas()->where('estado', 1)->orderBy('nombre')->get();

        // Dirección: primera Sucursal activa
        $sucursal = $inv->empresa->direcciones()
            ->where('estado', 1)
            ->where('tipo_domicilio', 'Sucursal')
            ->orderBy('id_direccion')
            ->first();

        $direccion_texto = $sucursal
            ? trim($sucursal->nombre . ' - ' . $sucursal->distrito . ' - ' . $sucursal->provincia . ' - ' . $sucursal->region)
            : null;

        //Direccion: primera Emision activa
        $emision = $inv->empresa->direcciones()
            ->where('estado', 1)
            ->where('tipo_domicilio', 'Emision')
            ->orderBy('id_direccion')
            ->first();

        $direccion_emision = $emision
            ? trim($emision->nombre . ' - ' . $emision->distrito . ' - ' . $emision->provincia . ' - ' . $emision->region)
            : null;

        // Plantillas
        [$presentaciones, $despedidas] = $this->plantillas($inv->empresa->razon_social);
        $presentacion = $presentaciones[$r->presentacion] ?? null;
        $despedida = $despedidas[$r->despedida] ?? null;

        // Fecha en español y formato formal
        $fecha = Carbon::now()->locale('es')->translatedFormat('d \\de F \\de Y');
        $lugar = $r->input('lugar', 'Trujillo');
        $rawWeb = $inv->empresa->pagina_web;
        $web = null;
        if ($rawWeb) {
            $web = preg_match('#^https?://#i', $rawWeb) ? $rawWeb : 'https://' . $rawWeb;
        }

        // ! para el texto y el color del estilo del pdf
        $branding = optional($inv->empresa->branding);

        $headerLayout = $branding->header_layout ?: 'logo_right';
        $headerShowLogo = (bool) ($branding->header_show_logo ?? 1);
        $footerLayout = $branding->footer_layout ?: 'info_left_page_right';
        $footerShowLogo = (bool) ($branding->footer_show_logo ?? 0);

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
        $font = $branding->fuente ?: 'DejaVu Sans';

        $style = [
            'font_family' => $font,
            'text' => '#111827',
            'header' => $scheme['header'],
            'note_bg' => $scheme['note_bg'],
            'note_border' => $scheme['note_border'],
            'pill_bg' => $scheme['pill_bg'],
        ];

        // pasar $style a la vista junto con el resto



        $pdf = Pdf::loadView('pdf.cotizacion', [   // << usaremos una nueva vista
            'inventario' => $inv,
            'destinatario' => $dest,
            'lugar' => $lugar,
            'fecha_texto' => $fecha,
            'simbolo' => $simbolo,
            'tc' => $tc,
            'presentacion' => $presentacion,
            'despedida' => $despedida,
            'cuentas' => $cuentas,
            'direccion' => $direccion_texto,
            'correo' => $inv->empresa->correo,
            'celular' => $inv->empresa->nro_celular,
            'web' => $web,
            'style' => $style,
            'headerLayout' => $headerLayout,
            'headerShowLogo' => $headerShowLogo,
            'footerLayout' => $footerLayout,
            'footerShowLogo' => $footerShowLogo,
        ])->setPaper('A4');

        // ver inline
        $nombre = 'Cotizacion_' . $inv->nombre_codigo . '_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->stream($nombre);
        // PARA DESCARGARLO DIRECTO PONER: return $pdf->download($nombre);
    }

    /** Plantillas de mensajes */
    private function plantillas(string $nombreEmpresa): array
    {
        $presentaciones = [
            'p1' => 'Estimado cliente, el presente documento es para presentarle la lista de los productos que puede ofrecerle mi empresa, gracias por confiar en nosotros.',
            'p2' => "Reciba un saludo cordial de la empresa {$nombreEmpresa}; a continuación, le presentamos la lista de nuestros productos y precios.",
            'p3' => "Es un gusto saludarle. Compartimos nuestra propuesta comercial con el detalle de productos disponibles y sus precios vigentes.",
            'p4' => "Con la calidad y respaldo de {$nombreEmpresa}, ponemos a su disposición nuestro catálogo actualizado.",
            'p5' => "Agradecemos su interés. Adjuntamos la relación de productos que podemos suministrar, sujetas a stock y confirmación.",
            'p6' => "Nos entusiasma colaborar con ustedes. Aquí encontrará los artículos solicitados con sus precios referenciales.",
        ];
        $despedidas = [
            'd1' => "Quedamos atentos a sus comentarios y a la confirmación de su pedido.",
            'd2' => "Cualquier consulta adicional, no dude en comunicarse con nosotros.",
            'd3' => "Agradecemos la oportunidad y esperamos atenderlos muy pronto.",
            'd4' => "Saludos cordiales,",
            'd5' => "Gracias por su preferencia.",
        ];
        return [$presentaciones, $despedidas];
    }
}