<?php

namespace App\Filament\Resources\Nilais\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Nilai;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class NilaiStatsWidget extends BaseWidget
{
    protected function getCards(): array
    {
        $totalNilai = Nilai::count();

        $averageNilaiAgama = Nilai::avg('nilai_agama');
        $averageNilaiJatiDiri = Nilai::avg('nilai_jatiDiri');
        $averageNilaiLiterasi = Nilai::avg('nilai_literasi');

        return [
            Stat::make('Total Penilaian', $totalNilai),
            Stat::make('Rata-rata Nilai Agama', number_format($averageNilaiAgama, 2)),
            Stat::make('Rata-rata Nilai Jati Diri', number_format($averageNilaiJatiDiri, 2)),
            Stat::make('Rata-rata Nilai Literasi', number_format($averageNilaiLiterasi, 2)),
        ];
    }
}
