<?php

namespace App\Filament\Resources\PindahKelas\Pages;

use App\Filament\Resources\PindahKelas\PindahKelasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPindahKelas extends ListRecords
{
    protected static string $resource = PindahKelasResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }
}
