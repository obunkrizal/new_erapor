<?php

namespace App\Filament\Resources\Siswas\Pages;

use Illuminate\View\View;
use App\Filament\Resources\Siswas\SiswaResource;
use App\Models\Sekolah;
use Filament\Resources\Pages\Page;
use App\Models\Siswa;

class PrintReport extends Page
{
    protected static string $resource = SiswaResource::class;

    protected string $view = 'filament.resources.siswa-resource.pages.print-report';

    protected static ?string $navigationLabel = 'Print Report';

    protected static string | \UnitEnum | null $navigationGroup = 'Data Master';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-printer';

    protected static ?int $navigationSort = 100;

    public function render(): View
    {
        $siswas = Siswa::all();
$sekolah=Sekolah::first();
        return view(static::$view, [
            'siswas' => $siswas,
            'sekolah'=>$sekolah,
        ]);
    }
}
