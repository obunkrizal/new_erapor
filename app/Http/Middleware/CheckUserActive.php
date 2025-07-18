<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && !Auth::user()->is_active) {
            Auth::logout();

            return redirect()->route('filament.admin.auth.login')
                ->withErrors(['email' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.']);
        }

        return $next($request);
    }
}
