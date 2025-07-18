<?php

namespace App\Filament\Resources\NilaiResource\Pages;

use App\Filament\Resources\NilaiResource\Widgets\NilaiStatsWidget;
use Filament\Resources\Pages\Page;

class NilaiStats extends Page
{
    protected static string $resource = \App\Filament\Resources\NilaiResource::class;

    protected static ?string $navigationLabel = 'Statistik Penilaian';
    protected static ?string $title = 'Statistik Penilaian Siswa';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    public function render(): \Illuminate\View\View
    {
        return view('filament.resources.nilai-resource.pages.nilai-stats', [
            'widgets' => [
                \App\Filament\Resources\NilaiResource\Widgets\NilaiStatsWidget::class,
            ],
        ]);
    }
}
