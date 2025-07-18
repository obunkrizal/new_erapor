<?php

namespace App\Filament\Widgets;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\Periode;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class GuruStatsWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected static bool $isLazy = false;
    public static function canView(): bool
    {
        return auth()->user()->isAdmin();
    }
    protected function getStats(): array
    {
        // Get active period
        $activePeriode = Periode::where('is_active', true)->first();

        // Total teachers
        $totalGuru = Guru::count();

        // Active homeroom teachers
        $waliKelas = $activePeriode ?
            Kelas::where('periode_id', $activePeriode->id)
                ->where('status', 'aktif')
                ->whereNotNull('guru_id')
                ->distinct('guru_id')
                ->count() : 0;

        // Teachers with assessments in current period
        $guruDenganNilai = $activePeriode ?
            Nilai::where('periode_id', $activePeriode->id)
                ->distinct('guru_id')
                ->count() : 0;

        // Gender distribution
        $genderStats = Guru::select('jenis_kelamin', DB::raw('count(*) as total'))
            ->groupBy('jenis_kelamin')
            ->pluck('total', 'jenis_kelamin')
            ->toArray();

        $lakiLaki = $genderStats['laki-laki'] ?? $genderStats['L'] ?? $genderStats['Laki-laki'] ?? 0;
        $perempuan = $genderStats['perempuan'] ?? $genderStats['P'] ?? $genderStats['Perempuan'] ?? 0;

        // Recent assessments count
        $recentNilai = $activePeriode ?
            Nilai::where('periode_id', $activePeriode->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->count() : 0;

        return [
            Stat::make('Total Guru', $totalGuru)
                ->description('Guru terdaftar di sistem')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ])
                ->url(route('filament.admin.resources.gurus.index'))
                ->chart($this->getGuruChart()),

            Stat::make('Wali Kelas', $waliKelas)
                ->description($activePeriode ? "Periode: {$activePeriode->nama_periode}" : 'Tidak ada periode aktif')
                ->descriptionIcon('heroicon-m-building-library')
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ])
                ->url(route('filament.admin.resources.kelas.index', [
                    'tableFilters' => ['status' => ['values' => ['aktif']]]
                ])),

            Stat::make('Guru Aktif Menilai', $guruDenganNilai)
                ->description('Telah membuat penilaian')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('info')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ])
                ->url(route('filament.admin.resources.nilais.index')),

            Stat::make('Penilaian Terbaru', $recentNilai)
                ->description('7 hari terakhir')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color($recentNilai > 0 ? 'warning' : 'gray')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ])
                ->url(route('filament.admin.resources.nilais.index', [
                    'tableFilters' => [
                        'created_date' => [
                            'created_from' => now()->subDays(7)->format('Y-m-d'),
                            'created_until' => now()->format('Y-m-d')
                        ]
                    ]
                ])),
        ];
    }

    private function getGuruChart(): array
    {
        // Get teacher registration trend for last 6 months
        $months = collect(range(5, 0))->map(function ($i) {
            return now()->subMonths($i)->startOfMonth();
        });

        return $months->map(function ($month) {
            return Guru::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        })->toArray();
    }
}
