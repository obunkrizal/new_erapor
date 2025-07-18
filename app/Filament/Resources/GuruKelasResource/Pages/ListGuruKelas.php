<?php

namespace App\Filament\Resources\GuruKelasResource\Pages;

use App\Filament\Resources\GuruKelasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGuruKelas extends ListRecords
{
    protected static string $resource = GuruKelasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
