<?php

namespace App\Filament\Resources\SiswaResource\Pages;

use App\Filament\Resources\SiswaResource;
use App\Models\Sekolah;
use Filament\Resources\Pages\Page;
use App\Models\Siswa;

class PrintReport extends Page
{
    protected static string $resource = SiswaResource::class;

    protected static string $view = 'filament.resources.siswa-resource.pages.print-report';

    protected static ?string $navigationLabel = 'Print Report';

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $navigationIcon = 'heroicon-o-printer';

    protected static ?int $navigationSort = 100;

    public function render(): \Illuminate\View\View
    {
        $siswas = Siswa::all();
$sekolah=Sekolah::first();
        return view(static::$view, [
            'siswas' => $siswas,
            'sekolah'=>$sekolah,
        ]);
    }
}
