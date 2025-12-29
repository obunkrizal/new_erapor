<?php

namespace App\Filament\Resources\Nilais\Pages;

use App\Filament\Resources\Nilais\NilaiResource;
use Illuminate\View\View;
use App\Filament\Resources\Nilais\Widgets\NilaiStatsWidget;
use Filament\Resources\Pages\Page;

class NilaiStats extends Page
{
    protected static string $resource = NilaiResource::class;

    protected static ?string $navigationLabel = 'Statistik Penilaian';
    protected static ?string $title = 'Statistik Penilaian Siswa';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';

    public function render(): View
    {
        return view('filament.resources.nilai-resource.pages.nilai-stats', [
            'widgets' => [
                NilaiStatsWidget::class,
            ],
        ]);
    }
}
