<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Session;

class LogoutNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        $user = $event->user;
        
        if ($user) {
            // Store in session for JavaScript to pick up
            session(['logout_user_name' => $user->name]);
        }
    }
}
