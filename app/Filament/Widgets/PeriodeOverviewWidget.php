<?php

namespace App\Filament\Widgets;

use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\Periode;
use App\Models\KelasSiswa;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class PeriodeOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        // Get active period
        $activePeriode = Periode::where('is_active', true)->first();

        if (!$activePeriode) {
            return [
                Stat::make('Periode Aktif', 'Tidak Ada')
                    ->description('Tidak ada periode yang aktif')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger')
                    ->extraAttributes([
                        'class' => 'cursor-pointer',
                    ])
                    ->url(route('filament.admin.resources.periodes.index')),
            ];
        }

        // Classes in active period
        $totalKelas = Kelas::where('periode_id', $activePeriode->id)
            ->where('status', 'aktif')
            ->count();

        // Students in active period
        $totalSiswa = KelasSiswa::where('periode_id', $activePeriode->id)
            ->where('status', 'aktif')
            ->distinct('siswa_id')
            ->count();

        // Assessments in active period
        $totalNilai = Nilai::where('periode_id', $activePeriode->id)->count();

        // Average students per class
        $avgSiswaPerKelas = $totalKelas > 0 ? round($totalSiswa / $totalKelas, 1) : 0;

        return [
            Stat::make('Periode Aktif', $activePeriode->nama_periode)
                ->description("Tahun Ajaran: {$activePeriode->tahun_ajaran}")
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ])
                ->url(route('filament.admin.resources.periodes.edit', $activePeriode->id)),

            Stat::make('Kelas Aktif', $totalKelas)
                ->description('Kelas dalam periode ini')
                ->descriptionIcon('heroicon-m-building-library')
                ->color('primary')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ])
                ->url(route('filament.admin.resources.kelas.index', [
                    'tableFilters' => [
                        'periode_id' => ['values' => [$activePeriode->id]],
                        'status' => ['values' => ['aktif']]
                    ]

                ]) ),

            Stat::make('Total Siswa', $totalSiswa)
                ->description("Rata-rata: {$avgSiswaPerKelas} siswa/kelas")
                ->descriptionIcon('heroicon-m-users')
                ->color('info')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ])
                ->url(route('filament.admin.resources.kelas-siswas.index', [
                    'tableFilters' => [
                        'periode_id' => ['values' => [$activePeriode->id]],
                        'status' => ['values' => ['aktif']]
                    ]
                ])),

            Stat::make('Penilaian', $totalNilai)
                ->description('Penilaian dalam periode ini')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('warning')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ])
                ->url(Auth::user()?->isAdmin() ? route('filament.admin.resources.nilais.index', [
                    'tableFilters' => [
                        'periode_id' => ['values' => [$activePeriode->id]]
                    ]
                ])
                : route('filament.admin.resources.guru-nilais.index', [
                    'tableFilters' => [
                        'periode_id' => ['values' => [$activePeriode->id]],
                        'status' => ['values' => ['proses']]
                    ]
                ])),
        ];
    }
}
