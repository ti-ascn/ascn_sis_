<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\DetalleCargo;
use App\Models\EmpresasClientes;
use Illuminate\Http\Request;

class CargoController extends Controller
{
    public function index()
    {
        $cargos = Cargo::with(['empresaCliente', 'usuario'])->latest()->get();
        $empresas = EmpresasClientes::select('id_empresas_clientes', 'razon_social', 'ruc')
            ->whereIn('estado_actual', [0, 1, 2])
            ->where('estado', 'C1')
            ->orderBy('razon_social')
            ->get();

        return view('cargo.index', compact('cargos', 'empresas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_empresa_cliente' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'nro_cargo' => 'required|string|max:255',
            'estado_cargo' => 'required|in:0,1'
        ]);

        $cargoExistente = Cargo::where('nro_cargo', $request->nro_cargo)->exists();
        
        if ($cargoExistente) {
            return back()->with('error', 'Cargo existente.');
        }

        Cargo::create([
            'id_empresa_cliente' => $request->id_empresa_cliente,
            'nro_cargo' => $request->nro_cargo,
            'estado_cargo' => $request->estado_cargo,
            'id_usuario' => auth()->id()
        ]);

        return back()->with('success', 'Cargo creado correctamente.');
    }

    public function destroy($id)
    {
        $cargo = Cargo::findOrFail($id);
        $cargo->detalles()->delete();
        $cargo->delete();

        return back()->with('success', 'Cargo eliminado correctamente.');
    }

    public function storeItems(Request $request)
    {
        $cargoId = $request->cargo_id;
        $nroItems = $request->nro_items;
        
        for ($i = 1; $i <= $nroItems; $i++) {
            $itemName = $request->input("item_{$i}");
            $tipoDoc = $request->input("tipo_{$i}");
            $cantidad = $request->input("cantidad_{$i}");
            
            if ($itemName && $tipoDoc && $cantidad) {
                DetalleCargo::create([
                    'id_cargo' => $cargoId,
                    'nombre_documento' => strtoupper($itemName),
                    'tipo_documento' => $tipoDoc,
                    'cantidades' => $cantidad,
                    'id_usuario' => auth()->id()
                ]);
            }
        }
        
        return response()->json(['success' => true, 'message' => 'Items guardados correctamente']);
    }

    public function getDetails($id)
    {
        $cargo = Cargo::with(['empresaCliente', 'detalles'])->findOrFail($id);
        return response()->json($cargo);
    }

    public function updateCargo(Request $request, $id)
    {
        $nroCargo = strtoupper($request->nro_cargo);
        
        if ($request->validate) {
            $existe = Cargo::where('nro_cargo', $nroCargo)
                ->where('id_cargo', '!=', $id)
                ->exists();
            
            if ($existe) {
                return response()->json(['error' => 'El número de cargo ya existe']);
            }
        }
        
        $cargo = Cargo::findOrFail($id);
        $cargo->update(['nro_cargo' => $nroCargo]);
        return response()->json(['success' => true]);
    }

    public function updateItem(Request $request, $id)
    {
        $item = DetalleCargo::findOrFail($id);
        $item->update([
            'nombre_documento' => strtoupper($request->nombre_documento),
            'tipo_documento' => $request->tipo_documento,
            'cantidades' => $request->cantidades
        ]);
        return response()->json(['success' => true]);
    }

    public function deleteItem($id)
    {
        $item = DetalleCargo::findOrFail($id);
        $item->delete();
        return response()->json(['success' => true]);
    }

    public function generarPDF(Request $request, $id)
    {
        $cargo = Cargo::with(['empresaCliente', 'detalles'])->findOrFail($id);
        
        if ($request->has('fecha')) {
            if ($cargo->estado_cargo == 0) {
                // Recepción: solo fecha (extraer solo la fecha del datetime-local)
                $fechaSolo = date('Y-m-d', strtotime($request->fecha));
                $cargo->update(['fecha' => $fechaSolo]);
            } else {
                // Entrega: fecha con hora (usar datetime-local completo)
                $fechaHora = date('Y-m-d H:i:s', strtotime($request->fecha));
                $cargo->update(['fecha' => $fechaHora]);
            }
            $cargo->refresh();
        }
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.cargo', [
            'cargo' => $cargo,
        ])->setPaper('A4');

        $empresaNombre = str_replace(' ', '_', $cargo->empresaCliente->razon_social);
        $nombre = 'C' . $cargo->nro_cargo . '-' . $empresaNombre . '-' . now()->format('d-m-Y_H-i-s') . '.pdf';
        return $pdf->stream($nombre);
    }
}