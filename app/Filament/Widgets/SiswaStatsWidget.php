<?php

namespace App\Filament\Widgets;

use App\Models\Siswa;
use App\Models\Periode;
use App\Models\KelasSiswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class SiswaStatsWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        return $user && $user->isAdmin();
    }
    protected function getStats(): array
    {
        // Get active period
        $activePeriode = Periode::where('is_active', true)->first();

        // Total students
        $totalSiswa = Siswa::count();

        // Active students in current period
        $siswaAktif = $activePeriode ?
            KelasSiswa::where('periode_id', $activePeriode->id)
                ->where('status', 'aktif')
                ->distinct('siswa_id')
                ->count() : 0;

        // Gender distribution
        $genderStats = Siswa::select('jenis_kelamin', DB::raw('count(*) as total'))
            ->groupBy('jenis_kelamin')
            ->pluck('total', 'jenis_kelamin')
            ->toArray();

        $lakiLaki = $genderStats['laki-laki'] ?? $genderStats['L'] ?? $genderStats['Laki-laki'] ?? 0;
        $perempuan = $genderStats['perempuan'] ?? $genderStats['P'] ?? $genderStats['Perempuan'] ?? 0;

        // Status distribution for current period
        $statusStats = $activePeriode ?
            KelasSiswa::where('periode_id', $activePeriode->id)
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray() : [];

        // Recent registrations (last 30 days)
        $recentSiswa = Siswa::where('created_at', '>=', now()->subDays(30))->count();

        return [
            Stat::make('Total Siswa', $totalSiswa)
                ->description('Siswa terdaftar di sistem')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ])
                ->url(route('filament.admin.resources.siswas.index'))
                ->chart($this->getSiswaChart()),

            Stat::make('Siswa Aktif', $siswaAktif)
                ->description($activePeriode ? "Periode: {$activePeriode->nama_periode}" : 'Tidak ada periode aktif')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ])
                ->url(route('filament.admin.resources.kelas-siswas.index', [
                    'tableFilters' => ['status' => ['values' => ['aktif']]]
                ])),

            Stat::make('Distribusi Gender', "{$lakiLaki}L / {$perempuan}P")
                ->description('Laki-laki / Perempuan')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info')
                ->chart([$lakiLaki, $perempuan]),

            Stat::make('Pendaftaran Baru', $recentSiswa)
                ->description('30 hari terakhir')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color($recentSiswa > 0 ? 'warning' : 'gray')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ])
                ->url(route('filament.admin.resources.siswas.index', [
                    'tableFilters' => [
                        'created_date' => [
                            'created_from' => now()->subDays(30)->format('Y-m-d'),
                            'created_until' => now()->format('Y-m-d')
                        ]
                    ]
                ])),
        ];
    }

    private function getSiswaChart(): array
    {
        // Get student registration trend for last 6 months
        $months = collect(range(5, 0))->map(function ($i) {
            return now()->subMonths($i)->startOfMonth();
        });

        return $months->map(function ($month) {
            return Siswa::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        })->toArray();
    }
}
