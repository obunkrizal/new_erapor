<?php

namespace App\Filament\Resources\NilaiResource\Widgets;

use App\Models\Nilai;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class NilaiStatsWidget extends BaseWidget
{
    protected function getCards(): array
    {
        $totalNilai = Nilai::count();

        $averageNilaiAgama = Nilai::avg('nilai_agama');
        $averageNilaiJatiDiri = Nilai::avg('nilai_jatiDiri');
        $averageNilaiLiterasi = Nilai::avg('nilai_literasi');

        return [
            Card::make('Total Penilaian', $totalNilai),
            Card::make('Rata-rata Nilai Agama', number_format($averageNilaiAgama, 2)),
            Card::make('Rata-rata Nilai Jati Diri', number_format($averageNilaiJatiDiri, 2)),
            Card::make('Rata-rata Nilai Literasi', number_format($averageNilaiLiterasi, 2)),
        ];
    }
}
