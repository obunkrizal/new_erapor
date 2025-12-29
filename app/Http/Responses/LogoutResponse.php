<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;

class LogoutResponse implements \Filament\Auth\Http\Responses\Contracts\LogoutResponse
{
    public function toResponse($request): RedirectResponse
    {
        // Redirect to home page with logout success message
        return redirect()->route('home')->with('logout_success', 'You have been successfully logged out.');
    }
}