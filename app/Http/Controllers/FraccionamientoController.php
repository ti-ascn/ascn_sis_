<?php

namespace App\Http\Controllers;

use App\Models\Fraccionamiento;
use App\Models\EmpresasClientes;
use Illuminate\Http\Request;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class FraccionamientoController extends Controller
{
    public function index()
    {
        $fraccionamientos = Fraccionamiento::with([
            'empresa',
            'detalles' => function ($q) {
                $q->orderBy('id_detalle_fraccionamiento', 'asc');
            }
        ])
            ->latest()
            ->get()
            ->map(function ($f) {
                // Buscar solo la primera cuota pendiente (sin afectar la relación original)
                $detallePendiente = $f->detalles->where('estado', 'pendiente')->first();

                $f->nombre_cuota_pendiente = $detallePendiente->nombre_cuota ?? '-';
                $f->fecha_vencimiento_pendiente = $detallePendiente->fecha_vencimiento ?? '-';
                $f->total_pendiente = $detallePendiente->total ?? 0;

                return $f;
            });

        return view('fraccionamiento.index', compact('fraccionamientos'));
    }

    public function create()
    {
        $empresas = EmpresasClientes::all();
        return view('fraccionamiento.create', compact('empresas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_empresa_cliente' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'nro_resolucion' => 'nullable|string|max:255',
            'periodo' => 'nullable|string|max:255',
            'codigo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'tributo_asociado' => 'nullable|string|max:255',
            'monto_tributo' => 'nullable|numeric',
            'interes' => 'nullable|numeric',
            'total' => 'nullable|numeric',
            'nro_cuotas' => 'nullable|integer',
            'cuota_acogimiento' => 'required|boolean',
            'refinanciamiento' => 'required|boolean',
        ]);

        $fraccionamiento = Fraccionamiento::create($validated);

        return redirect()
            ->route('detalle_fraccionamiento.create', $fraccionamiento->id_fraccionamiento)
            ->with('success', 'Fraccionamiento creado correctamente. Ahora ingresa el detalle.');
    }

    public function edit($id_fraccionamiento)
    {
        $fraccionamiento = Fraccionamiento::findOrFail($id_fraccionamiento);
        $empresas = EmpresasClientes::all();
        return view('fraccionamiento.edit', compact('fraccionamiento', 'empresas'));
    }

    public function update(Request $request, $id_fraccionamiento)
    {
        $validated = $request->validate([
            'id_empresa_cliente' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'nro_resolucion' => 'nullable|string|max:255',
            'periodo' => 'nullable|string|max:255',
            'codigo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'tributo_asociado' => 'nullable|string|max:255',
            'monto_tributo' => 'nullable|numeric',
            'interes' => 'nullable|numeric',
            'total' => 'nullable|numeric',
            'nro_cuotas' => 'nullable|integer',
            'cuota_acogimiento' => 'required|boolean',
            'refinanciamiento' => 'required|boolean',
        ]);

        $fraccionamiento = Fraccionamiento::findOrFail($id_fraccionamiento);
        $fraccionamiento->update($validated);

        return redirect()
            ->route('fraccionamiento.index')
            ->with('success', 'Fraccionamiento actualizado correctamente.');
    }

    public function destroy($id_fraccionamiento)
    {
        $fraccionamiento = Fraccionamiento::findOrFail($id_fraccionamiento);
        $fraccionamiento->delete();

        return redirect()
            ->route('fraccionamiento.index')
            ->with('success', 'Fraccionamiento eliminado correctamente.');
    }

    public function generarPDF($id_fraccionamiento)
    {
        $fraccionamiento = Fraccionamiento::with(['empresa', 'detalles'])->findOrFail($id_fraccionamiento);
        $empresa = $fraccionamiento->empresa;
        $hoy = Carbon::now()->locale('es')->translatedFormat('d \\de F \\de Y');

        $pdf = Pdf::loadView('pdf.fraccionamiento', [
            'empresa' => $empresa,
            'fraccionamiento' => $fraccionamiento,
            'fechaHoy' => $hoy,
        ])->setPaper('A4', 'portrait');

        $nombreArchivo = 'FRACCIONAMIENTO_' . strtoupper(str_replace(' ', '_', $empresa->razon_social)) . '_' . $fraccionamiento->nro_resolucion . '.pdf';
        return $pdf->stream($nombreArchivo);
    }

    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:VIGENTE,PERDIDO,PAGADO',
        ]);

        $fraccionamiento = Fraccionamiento::findOrFail($id);
        $fraccionamiento->estado = $request->estado;
        $fraccionamiento->save();

        return redirect()
            ->back()
            ->with('success', 'Estado del fraccionamiento actualizado correctamente.');
    }
}
