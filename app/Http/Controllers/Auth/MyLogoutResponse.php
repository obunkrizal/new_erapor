<?php

namespace App\Http\Controllers\Auth;

use Filament\Http\Responses\Auth\Contracts\LogoutResponse as Responsable;
use Illuminate\Http\RedirectResponse;

class MyLogoutResponse implements Responsable
{
    public function toResponse($request): RedirectResponse
    {
        // change this to your desired route
        return redirect()->route('home');
    }
}
