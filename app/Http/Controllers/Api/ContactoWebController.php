<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactoWeb;

class ContactoWebController extends Controller
{
    public function store(Request $request)
    {
        // Honeypot: si este campo viene con algo, es un bot
        if ($request->filled('website')) {
            return response()->json(['message' => 'Spam detected'], 200);
        }

        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/'
            ],
            'telefono' => 'required|string|max:20',
            'tipo' => 'required|string',
            'correo' => 'required|string|max:100',
        ]);

        ContactoWeb::create($request->all());

        return response()->json(['message' => 'Datos enviados correctamente'], 201);
    }

    public function index()
    {
        return response()->json(
            ContactoWeb::orderBy('created_at', 'desc')->get()
        );
    }
}
