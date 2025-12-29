<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\Periode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class NilaiStatsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        // Get active period
        $activePeriode = Periode::where('is_active', true)->first();

        // Total assessments
        $totalNilai = Nilai::count();

        // Assessments in current period
        $nilaiPeriodeAktif = $activePeriode ?
            Nilai::where('periode_id', $activePeriode->id)->count() : 0;

        // Complete assessments (all 4 aspects filled)
        $nilaiLengkap = $activePeriode ?
            Nilai::where('periode_id', $activePeriode->id)
                ->whereNotNull('nilai_agama')
                ->whereNotNull('nilai_jatiDiri')
                ->whereNotNull('nilai_literasi')
                ->whereNotNull('nilai_narasi')
                ->count() : 0;

        // Assessments with documentation
        $nilaiDenganFoto = $activePeriode ?
            Nilai::where('periode_id', $activePeriode->id)
                ->where(function ($query) {
                    $query->whereNotNull('fotoAgama')
                        ->orWhereNotNull('fotoJatiDiri')
                        ->orWhereNotNull('fotoLiterasi')
                        ->orWhereNotNull('fotoNarasi');
                })
                ->count() : 0;

        // Recent assessments
        $recentNilai = $activePeriode ?
            Nilai::where('periode_id', $activePeriode->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->count() : 0;

        // Completion percentage
        $completionPercentage = $nilaiPeriodeAktif > 0 ?
            round(($nilaiLengkap / $nilaiPeriodeAktif) * 100, 1) : 0;
        /** @var User|null $user */
        $user = Auth::user();
        return [
            Stat::make('Total Penilaian', $totalNilai)
                ->description('Seluruh penilaian di sistem')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ])
                ->url(
                $user?->isAdmin()
                    ? route('filament.admin.resources.nilais.index')
                    : route('filament.admin.resources.guru-nilais.index')
                )
                ->chart($this->getNilaiChart()),

            Stat::make('Periode Aktif', $nilaiPeriodeAktif)
                ->description($activePeriode ? $activePeriode->nama_periode : 'Tidak ada periode aktif')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ])
                ->url($user?->isAdmin() ? route('filament.admin.resources.nilais.index', [
                    'tableFilters' => $activePeriode ? [
                        'periode_id' => ['values' => [$activePeriode->id]]
                    ] : []
                ]) : route('filament.admin.resources.guru-nilais.index', [
                    'tableFilters' => $activePeriode ? [
                        'periode_id' => ['values' => [$activePeriode->id]]
                    ] : []
                ])),



            Stat::make('Penilaian Lengkap', "{$nilaiLengkap} ({$completionPercentage}%)")
                ->description('Semua aspek dinilai')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color($completionPercentage >= 80 ? 'success' : ($completionPercentage >= 50 ? 'warning' : 'danger'))
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ])
                ->url($user?->isAdmin() ? route('filament.admin.resources.nilais.index', [
                    'tableFilters' => ['complete_nilai' => true]
                ])
                    : route('filament.admin.resources.guru-nilais.index', [
                        'tableFilters' => ['complete_nilai' => true]
                    ])),

            Stat::make('Dengan Dokumentasi', $nilaiDenganFoto)
                ->description('Memiliki foto dokumentasi')
                ->descriptionIcon('heroicon-m-camera')
                ->color('info')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ])
                ->url($user?->isAdmin() ? route('filament.admin.resources.nilais.index', [
                    'tableFilters' => ['has_photos' => true]
                ])
                    : route('filament.admin.resources.guru-nilais.index', [
                        'tableFilters' => ['has_photos' => true]
                    ])),
        ];
    }

    private function getNilaiChart(): array
    {
        // Get assessment trend for last 7 days
        $days = collect(range(6, 0))->map(function ($i) {
            return now()->subDays($i)->startOfDay();
        });

        return $days->map(function ($day) {
            return Nilai::whereDate('created_at', $day->toDateString())->count();
        })->toArray();
    }
}
