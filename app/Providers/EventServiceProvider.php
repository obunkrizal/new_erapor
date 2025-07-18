<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Listeners\LoginNotification;
use App\Listeners\LogoutNotification;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            LoginNotification::class,
        ],
        Logout::class => [
            LogoutNotification::class,
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}