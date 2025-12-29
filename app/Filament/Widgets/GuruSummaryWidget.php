<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Nilai;
use App\Models\Absensi;
use App\Models\Periode;
use App\Models\DataMedisSiswa;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class GuruSummaryWidget extends BaseWidget
{
    protected static ?int $sort = 3; // Adjust sorting as needed
    protected static bool $isLazy = false;



    protected function getAbsensiChart(): array
    {
        return [
            'labels' => [ 'Izin', 'Sakit', 'Alfa'],
            'datasets' => [
                [
                    'label' => 'Absensi',
                    'data' => [
                        Absensi::where('izin')->count(),
                        Absensi::where('sakit')->count(),
                        Absensi::where( 'tanpa_keterangan')->count(),
                    ],
                    'backgroundColor' => [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                    ],
                    'borderColor' => [
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(255, 99, 132, 1)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function getDataMedisChart(): array
    {
        return [
            'labels' => ['Data Medis'],
            'datasets' => [
                [
                    'label' => 'Data Medis',
                    'data' => [DataMedisSiswa::count()],
                    'backgroundColor' => [
                        'rgba(54, 162, 235, 0.2)',
                    ],
                    'borderColor' => [
                        'rgba(54, 162, 235, 1)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function getPenilaianChart(): array
    {
        return [
            'labels' => ['Penilaian'],
            'datasets' => [
                [
                    'label' => 'Penilaian',
                    'data' => [Nilai::count()],
                    'backgroundColor' => [
                        'rgba(255, 206, 86, 0.2)',
                    ],
                    'borderColor' => [
                        'rgba(255, 206, 86, 1)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function getStats(): array
    {
        // Get active period
        $activePeriode = Periode::where('is_active', true)->first();

        if (!$activePeriode) {
            return [
                Stat::make('Periode Aktif', 'Tidak Ada')
                    ->description('Tidak ada periode yang aktif')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
            ];
        }

        // Count reports, attendance, medical records, and assessments
        $totalReports = Absensi::where('periode_id', $activePeriode->id)->count();
        $totalAttendance = Absensi::where('periode_id', $activePeriode->id)->count();
        $totalMedicalRecords = DataMedisSiswa::where('periode_id', $activePeriode->id)->count();
        $totalAssessments = Nilai::where('periode_id', $activePeriode->id)->count();

        /** @var User|null $user */
        $user = Auth::user();
        return [
            Stat::make('Total Laporan', $totalReports)
                ->description('Jumlah laporan yang diisi')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Total Absensi', $totalAttendance )
                ->description('Jumlah absensi yang diisi')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url($user?->isAdmin() ? route('filament.admin.resources.absensis.index')
                    : route('filament.admin.resources.absensis.index'))
                ->chart($this->getAbsensiChart()),

            Stat::make('Total Data Medis', $totalMedicalRecords)
                ->description('Jumlah data medis yang diisi')
                ->descriptionIcon('heroicon-m-heart')
                ->color('info')
                ->url($user?->isAdmin() ? route('filament.admin.resources.data-medis-siswas.index')
                    : route('filament.admin.resources.data-medis-siswas.index'))
                ->chart($this->getDataMedisChart()),

            Stat::make('Total Penilaian', $totalAssessments)
                ->description('Jumlah penilaian yang diisi')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('warning')
                ->url($user?->isAdmin() ? route('filament.admin.resources.guru-nilais.index')
                    : route('filament.admin.resources.guru-nilais.index'))
                ->chart($this->getPenilaianChart()),
        ];
    }
}

