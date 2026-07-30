<?php

namespace App\Http\Controllers;

use App\Models\ContactoWeb;

class ContactoController extends Controller
{
    public function index()
    {
        $contactos = ContactoWeb::orderBy('created_at', 'desc')->get();
        return view('contactos.index', compact('contactos'));
    }
}
