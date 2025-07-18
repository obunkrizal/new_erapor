<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait AdminOnlyResource
{
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }
}
