<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            if (Auth::user()->estado == 0) {
                Auth::logout();

                // Invalidar la sesión actual
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'Tu cuenta ha sido desactivada. Por favor, contacta al administrador.');
            }

            // Registrar última actividad si no es petición AJAX
            if (!$request->ajax() && $request->isMethod('GET')) {
                $path = $request->path();
                if (!str_starts_with($path, 'usuarios-online-partial') && !str_starts_with($path, 'up')) {
                    \Illuminate\Support\Facades\Cache::put('user_last_path_' . Auth::id(), '/' . $path, now()->addMinutes(5));
                }
            }
        }

        return $next($request);
    }
}
