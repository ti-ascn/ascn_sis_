<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UsuariosOnline extends Component
{
    public $usuarios;
    public $sesionesCrudas;

    public function __construct()
    {
        $minutos = 5;

        $this->sesionesCrudas = DB::table('sessions')
            ->where('last_activity', '>=', now()->subMinutes($minutos)->timestamp)
            ->get();

        $ids = $this->sesionesCrudas->pluck('user_id')->filter()->unique();

        $this->usuarios = User::whereIn('id', $ids)->get();
    }

    public static function detectarDispositivo($userAgent)
    {
        $userAgent = strtolower($userAgent);

        $esMovil = preg_match('/(iphone|android|mobile|ipad|tablet)/', $userAgent);

        return $esMovil ? 'Móvil/Tableta' : 'PC/Laptop';
    }

    public function render()
    {
        return view('components.usuarios-online');
    }
}


