<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $timeout = config('session.lifetime') * 60; // timeout in seconds
            $lastActivity = session('lastActivityTime');

            if ($lastActivity && (time() - $lastActivity) > $timeout) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('filament.admin.auth.login')
                    ->withErrors(['email' => 'Session expired due to inactivity. Please login again.']);
            }

            session(['lastActivityTime' => time()]);
        }

        return $next($request);
    }
}
