<?php

namespace App\Providers;

use App\Models\Kelas;
use App\Models\KelasSiswa;
use App\Services\AutoNarasiGenerator;
use App\Http\Responses\LogoutResponse;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\Filament\Auth\Http\Responses\Contracts\LogoutResponse::class, LogoutResponse::class);
        // Register AutoNarasiGenerator sebagai singleton
        $this->app->singleton(AutoNarasiGenerator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Validator::extend('kapasitas_available', function ($attribute, $value, $parameters, $validator) {
            $kelas = Kelas::find($value);
            return $kelas->kapasitas > $kelas->jumlah_siswa_aktif;
        });

        Validator::extend('kapasitas_available', function ($attribute, $value, $parameters, $validator) {
            $kelasSiswa = KelasSiswa::find($value);
            return $kelasSiswa->kapasitas > $kelasSiswa->jumlah_siswa_aktif;
        });

    }
}
