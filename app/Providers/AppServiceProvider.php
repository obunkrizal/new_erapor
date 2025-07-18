<?php

namespace App\Providers;

use App\Models\Kelas;
use App\Models\KelasSiswa;
use App\Http\Responses\LogoutResponse;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LogoutResponseContract::class, LogoutResponse::class);
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
