<?php

namespace App\Http\Controllers;

use App\Models\PrecioDolar;
use Illuminate\Http\Request;

class PrecioDolarController extends Controller
{
    public function update(Request $request)
    {
        $usuario = auth()->user();
        if (
            $usuario->tipo_usuario == 3 &&
            ($usuario->id_area != 7 || !$usuario->puede_editar)
        ) {
            return back()->with('error', 'No tienes permiso para actualizar el precio del dólar.');
        }
        $request->validate([
            'precio' => 'required|numeric|min:0',
        ]);

        $precioDolar = PrecioDolar::first(); // único registro
        if (!$precioDolar) {
            $precioDolar = new PrecioDolar();
        }

        $precioDolar->precio = $request->precio;
        $precioDolar->id_usuario = auth()->id();
        $precioDolar->save();

        return back()->with('success', 'Precio del dólar actualizado.');
    }

}
