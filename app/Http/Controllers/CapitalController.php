<?php

namespace App\Http\Controllers;

use App\Models\Capital;
use App\Models\AumentoCapital;
use App\Models\EmpresasClientes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CapitalesExport;

class CapitalController extends Controller
{
    public function index()
    {
        $empresas = EmpresasClientes::where('estado', 'A1')
            ->with([
                'capital' => function ($q) {
                    $q->withCount('aumentos');
                }
            ])
            ->get();

        return view('capital.index', compact('empresas'));
    }

    public function export()
    {
        return Excel::download(new CapitalesExport, 'Capitales_' . date('Y_m_d_H_i_s') . '.xlsx');
    }

    // ── CAPITAL CRUD ──────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'empresas_cliente' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'partida' => 'nullable|string|max:255',
            'capital_Constituido' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:255',
        ]);

        if (Capital::where('empresas_cliente', $request->empresas_cliente)->exists()) {
            return response()->json(['error' => 'Esta empresa ya tiene un capital registrado.'], 422);
        }

        $capital = Capital::create([
            'empresas_cliente' => $request->empresas_cliente,
            'partida' => $request->partida,
            'capital_Constituido' => $request->capital_Constituido,
            'fecha' => $request->fecha ?: null,
            'total_capital' => $request->capital_Constituido,
            'observaciones' => $request->observaciones,
        ]);

        return response()->json([
            'message' => 'Capital registrado correctamente.',
            'capital' => $capital,
        ]);
    }

    public function update(Request $request, $id)
    {
        $capital = Capital::findOrFail($id);

        $request->validate([
            'partida' => 'nullable|string|max:255',
            'capital_Constituido' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:255',
        ]);

        $sumaAumentos = $capital->aumentos()->sum('monto_Capital');

        $capital->update([
            'partida' => $request->partida,
            'capital_Constituido' => $request->capital_Constituido,
            'total_capital' => $request->capital_Constituido + $sumaAumentos,
            'fecha' => $request->fecha ?: null,
            'observaciones' => $request->observaciones,
        ]);

        $capital->refresh();

        return response()->json([
            'message' => 'Capital actualizado correctamente.',
            'capital' => $capital,
            'total_capital' => $capital->total_capital,
        ]);
    }

    public function destroy($id)
    {
        $capital = Capital::findOrFail($id);

        foreach ($capital->aumentos as $aumento) {
            if ($aumento->archivo_testimonio && file_exists(public_path($aumento->archivo_testimonio))) {
                unlink(public_path($aumento->archivo_testimonio));
            }
        }

        $capital->aumentos()->delete();
        $capital->delete();

        return response()->json([
            'message' => 'Capital eliminado correctamente.',
        ]);
    }

    public function getCapital($id_empresa)
    {
        $capital = Capital::where('empresas_cliente', $id_empresa)->first();
        return response()->json(['capital' => $capital]);
    }

    // ── AUMENTO DE CAPITAL CRUD ───────────────────────────────────

    public function storeAumento(Request $request)
    {
        $request->validate([
            'id_capital' => 'required|exists:capital,id_capital',
            'monto_Capital' => 'required|numeric|min:0',
            'banco' => 'nullable|string|max:255',
            'fecha' => 'required|date',
            'observaciones' => 'nullable|string|max:255',
            'archivo_testimonio' => 'nullable|file|extensions:pdf|max:10240',
        ]);

        $rutaArchivo = null;

        if ($request->hasFile('archivo_testimonio')) {
            $rutaArchivo = $this->guardarTestimonio(
                $request->file('archivo_testimonio'),
                $request->id_capital,
                $request->fecha
            );
        }

        $aumento = AumentoCapital::create([
            'id_capital' => $request->id_capital,
            'monto_Capital' => $request->monto_Capital,
            'banco' => $request->banco ?? 'SIN BANCO',
            'fecha' => $request->fecha,
            'observaciones' => $request->observaciones,
            'archivo_testimonio' => $rutaArchivo,
        ]);

        $capital = Capital::findOrFail($request->id_capital);
        $capital->total_capital = $capital->capital_Constituido + $capital->aumentos()->sum('monto_Capital');
        $capital->save();

        return response()->json([
            'message' => 'Aumento registrado correctamente.',
            'aumento' => $aumento,
            'total_capital' => $capital->total_capital,
        ]);
    }

    public function updateAumento(Request $request, $id)
    {
        $aumento = AumentoCapital::findOrFail($id);

        $request->validate([
            'monto_Capital' => 'required|numeric|min:0',
            'banco' => 'nullable|string|max:255',
            'fecha' => 'required|date',
            'observaciones' => 'nullable|string|max:255',
            'archivo_testimonio' => 'nullable|file|extensions:pdf|max:10240',
        ]);

        if ($request->hasFile('archivo_testimonio')) {
            if ($aumento->archivo_testimonio && file_exists(public_path($aumento->archivo_testimonio))) {
                unlink(public_path($aumento->archivo_testimonio));
            }

            $aumento->archivo_testimonio = $this->guardarTestimonio(
                $request->file('archivo_testimonio'),
                $aumento->id_capital,
                $request->fecha
            );
        }

        $aumento->monto_Capital = $request->monto_Capital;
        $aumento->banco = $request->banco ?? 'SIN BANCO';
        $aumento->fecha = $request->fecha;
        $aumento->observaciones = $request->observaciones;
        $aumento->save();

        $capital = Capital::findOrFail($aumento->id_capital);
        $capital->total_capital = $capital->capital_Constituido + $capital->aumentos()->sum('monto_Capital');
        $capital->save();

        return response()->json([
            'message' => 'Aumento actualizado correctamente.',
            'aumento' => $aumento,
            'total_capital' => $capital->total_capital,
        ]);
    }

    public function destroyAumento($id)
    {
        $aumento = AumentoCapital::findOrFail($id);

        if ($aumento->archivo_testimonio && file_exists(public_path($aumento->archivo_testimonio))) {
            unlink(public_path($aumento->archivo_testimonio));
        }

        $id_capital = $aumento->id_capital;
        $aumento->delete();

        $capital = Capital::findOrFail($id_capital);
        $capital->total_capital = $capital->capital_Constituido + $capital->aumentos()->sum('monto_Capital');
        $capital->save();

        return response()->json([
            'message' => 'Aumento eliminado correctamente.',
            'total_capital' => $capital->total_capital,
        ]);
    }

    public function getAumentos($id_capital)
    {
        $aumentos = AumentoCapital::where('id_capital', $id_capital)
            ->orderBy('fecha', 'desc')
            ->get();

        return response()->json(['aumentos' => $aumentos]);
    }

    // ── HELPER PRIVADO: guardar testimonio PDF ────────────────────────────
    private function guardarTestimonio($file, $id_capital, $fecha): string
    {
        $capital = Capital::findOrFail($id_capital);
        $empresa = EmpresasClientes::findOrFail($capital->empresas_cliente);

        $idEmpresa = $empresa->id_empresas_clientes;
        $nombreSlug = strtoupper(Str::slug($empresa->razon_social, '_'));
        $fechaFormato = date('d_m_Y', strtotime($fecha));
        $baseNombre = "TESTIMONIO_AUMENTO_CAPITAL_{$nombreSlug}_{$fechaFormato}";

        $carpeta = public_path("aumentos_capital/{$idEmpresa}");
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0755, true);
        }

        $nombreFinal = $baseNombre . '.pdf';
        $contador = 1;
        while (file_exists("{$carpeta}/{$nombreFinal}")) {
            $nombreFinal = "{$baseNombre}_{$contador}.pdf";
            $contador++;
        }

        $file->move($carpeta, $nombreFinal);

        return "aumentos_capital/{$idEmpresa}/{$nombreFinal}";
    }
}