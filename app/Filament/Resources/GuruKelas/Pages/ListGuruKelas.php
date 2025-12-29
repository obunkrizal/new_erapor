<?php

namespace App\Filament\Resources\GuruKelas\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\GuruKelas\GuruKelasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGuruKelas extends ListRecords
{
    protected static string $resource = GuruKelasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
