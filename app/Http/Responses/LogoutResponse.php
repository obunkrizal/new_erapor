<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LogoutResponse as Responsable;
use Illuminate\Http\RedirectResponse;

class LogoutResponse implements Responsable
{
    public function toResponse($request): RedirectResponse
    {
        // Redirect to home page with logout success message
        return redirect()->route('home')->with('logout_success', 'You have been successfully logged out.');
    }
}