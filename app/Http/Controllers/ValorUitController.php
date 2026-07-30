<?php

namespace App\Http\Controllers;

use App\Models\ValorUit;
use Illuminate\Http\Request;

class ValorUitController extends Controller
{
    public function index()
    {
        $valores = ValorUit::orderBy('anio', 'desc')->get();
        return view('valor_uit.index', compact('valores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'anio' => 'required|integer|unique:valor_uits,anio',
            'valor' => 'required|numeric',
        ]);

        ValorUit::create($request->all());

        return redirect()->route('valor_uit.index')->with('success', 'Valor UIT creado exitosamente.');
    }

    public function update(Request $request, $id_uit)
    {
        $request->validate([
            'anio' => 'required|integer|unique:valor_uits,anio,' . $id_uit . ',id_uit',
            'valor' => 'required|numeric',
        ]);

        $valor = ValorUit::findOrFail($id_uit);
        $valor->update($request->all());

        return redirect()->route('valor_uit.index')->with('success', 'Valor UIT actualizado exitosamente.');
    }

    public function destroy($id_uit)
    {
        $valor = ValorUit::findOrFail($id_uit);
        $valor->delete();

        return redirect()->route('valor_uit.index')->with('success', 'Valor UIT eliminado exitosamente.');
    }
}
