<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    // !REDIRIGIR A LOS USUARIOS DE ACUERDO A SU TIPO_USUARIO
    public function store(Request $request)
    {
    
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        $key = Str::lower($request->input('email')) . '|' . $request->ip();
    
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "Demasiados intentos. Intenta nuevamente en " . ceil($seconds / 60) . " minutos."
            ]);
        }
    
        if (Auth::attempt($request->only('email', 'password'))) {
            RateLimiter::clear($key); // Reiniciar intentos
    
            $user = Auth::user();
    
            if ($user->estado == 0) {
                Auth::logout();
                return back()->withErrors(['email' => 'Tu cuenta está desactivada.']);
            }
    
            switch ($user->tipo_usuario) {
                case 1:
                case 2:
                case 3:
                    return redirect()->route('dashboard');
                default:
                    return redirect()->route('dashboard-sin-asignar');
            }
        }
    
        RateLimiter::hit($key, 300); // Penalización de 5 minutos
        $remaining = RateLimiter::retriesLeft($key, 5);
    
        return back()->withErrors([
            'email' => "Credenciales inválidas. Te quedan $remaining intentos.",
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
