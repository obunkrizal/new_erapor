<?php

namespace App\Filament\Resources\HargaSpps\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\HargaSpps\HargaSppResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHargaSpps extends ListRecords
{
    protected static string $resource = HargaSppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
