<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class LoginNotification
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
    public function handle(Login $event): void
    {
        $user = $event->user;
        $now = Carbon::now('Asia/Jakarta');
        $greeting = $this->getTimeBasedGreeting($now);
        $roleMessage = $this->getRoleBasedMessage($user->role);

        Notification::make()
            ->title($greeting . ' ' . $user->name)
            ->body("Selamat datang, {$roleMessage}")
            ->success()
            ->duration(5000)
            ->icon('heroicon-o-hand-raised')
            ->iconColor('success')
            ->send();
    }

    private function getTimeBasedGreeting(Carbon $time): string
    {
        $hour = $time->hour;

        if ($hour >= 5 && $hour < 12) {
            return 'Selamat Pagi!';
        } elseif ($hour >= 12 && $hour < 15) {
            return 'Selamat Siang!';
        } elseif ($hour >= 15 && $hour < 18) {
            return 'Selamat Sore!';
        } else {
            return 'Selamat Malam!';
        }


        // if ($hour < 12) {
        //     return 'Selamat Pagi!';
        // } elseif ($hour < 15) {
        //     return 'Selamat Siang!';
        // } elseif ($hour < 18) {
        //     return 'Selamat Sore!';
        // } else {
        //     return 'Selamat Malam!';
        // }
    }

    private function getRoleBasedMessage(string $role): string
    {
        return match ($role) {
            'admin' => 'Anda login sebagai Administrator.',
            'guru' => 'Anda login sebagai Guru.',
            'siswa' => 'Anda login sebagai Siswa.',
            default => 'Selamat menggunakan sistem.',
        };
    }
}
