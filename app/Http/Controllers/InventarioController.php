<?php

namespace App\Http\Controllers;

use App\Exports\InventarioExport;
use App\Exports\ProductosVendidosExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Inventario;
use App\Models\EmpresasClientes;
use App\Models\EmpresasClientesBranding;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'id_empresas_cliente' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'prefijo' => 'required|string|max:10',
        ]);

        // Revisar si ya tiene inventario
        if (Inventario::where('id_empresas_cliente', $request->id_empresas_cliente)->exists()) {
            return back()->with('error', 'La empresa ya tiene un inventario.');
        }

        Inventario::create([
            'id_empresas_cliente' => $request->id_empresas_cliente,
            'nombre_codigo' => $request->prefijo,
        ]);

        return back()->with('success', "Inventario creado con el código: {$request->prefijo}");
    }

    public function index()
    {
        $inventarios = Inventario::with([
            'empresa.branding.usuario',
            'empresa.presentacion',
            'productos' => fn($q) => $q->where('estado', 1),
        ])->get();


        // plantillas para el modal
        $presentaciones = [
            'p1' => 'Estimado cliente, el presente documento es para presentarle la lista de los productos que puede ofrecerle mi empresa, gracias por confiar en nosotros.',
            'p2' => "Reciba un saludo cordial de la empresa NOMBRE_EMPRESA; a continuación, le presentamos la lista de nuestros productos y precios.",
            'p3' => 'Es un gusto saludarle. Compartimos nuestra propuesta comercial con el detalle de productos disponibles y sus precios vigentes.',
            'p4' => "Con la calidad y respaldo de NOMBRE_EMPRESA, ponemos a su disposición nuestro catálogo actualizado.",
            'p5' => 'Agradecemos su interés. Adjuntamos la relación de productos que podemos suministrar, sujetas a stock y confirmación.',
            'p6' => 'Nos entusiasma colaborar con ustedes. Aquí encontrará los artículos solicitados con sus precios referenciales.',
        ];
        $despedidas = [
            'd1' => 'Quedamos atentos a sus comentarios y a la confirmación de su pedido.',
            'd2' => 'Cualquier consulta adicional, no dude en comunicarse con nosotros.',
            'd3' => 'Agradecemos la oportunidad y esperamos atenderlos muy pronto.',
            'd4' => 'Saludos cordiales,',
            'd5' => 'Gracias por su preferencia.',
        ];

        return view('inventario.index', compact('inventarios', 'presentaciones', 'despedidas'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'id_inventario' => 'required|exists:inventario,id_inventario',
            'nombre_codigo' => 'required|string|max:10',
        ]);

        $nombre_codigo = strtoupper(preg_replace('/[^A-Z0-9&]/', '', $request->nombre_codigo));

        // Verificar duplicados (excluyendo el mismo inventario)
        $existe = Inventario::where('nombre_codigo', $nombre_codigo)
            ->where('id_inventario', '!=', $request->id_inventario)
            ->exists();

        if ($existe) {
            return back()->with('error', "Ya existe un inventario con el código '{$nombre_codigo}'");
        }

        $inventario = Inventario::findOrFail($request->id_inventario);
        $inventario->update([
            'nombre_codigo' => $nombre_codigo,
        ]);

        return back()->with('success', "Inventario actualizado a: {$nombre_codigo}");
    }
    public function branding()
    {
        $brandings = EmpresasClientesBranding::with(['empresa', 'usuario'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('inventario.branding', compact('brandings'));
    }

    public function exportarExcel(Request $request)
    {
        $request->validate([
            'id_inventario' => 'required|exists:inventario,id_inventario',
            'moneda' => 'required|in:PEN,USD'
        ]);

        $inventario = Inventario::with('empresa')->findOrFail($request->id_inventario);

        $empresaNombre = strtoupper(str_replace(' ', '_', $inventario->empresa->razon_social));
        $mes = ucfirst(now()->locale('es')->translatedFormat('F'));
        $anio = now()->format('Y');

        $nombreArchivo = "INVENTARIO_DE_{$empresaNombre}_{$mes}_{$anio}.xlsx";

        return Excel::download(
            new InventarioExport($request->id_inventario, $request->moneda),
            $nombreArchivo
        );
    }

    public function exportarMasVendidos(Request $request)
    {
        $request->validate([
            'id_inventario' => 'required|exists:inventario,id_inventario',
        ]);

        $inventario = Inventario::with('empresa')->findOrFail($request->id_inventario);

        $empresaNombre = strtoupper(str_replace(' ', '_', $inventario->empresa->razon_social));
        $fecha = now()->format('Y_m_d_His');

        $nombreArchivo = "PRODUCTOS_MAS_VENDIDOS_{$empresaNombre}_{$fecha}.xlsx";

        return Excel::download(
            new ProductosVendidosExport($request->id_inventario),
            $nombreArchivo
        );
    }
}

