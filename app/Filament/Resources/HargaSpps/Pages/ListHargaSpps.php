<?php

namespace App\Filament\Resources\HargaSppResource\Pages;

use App\Filament\Resources\HargaSppResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHargaSpps extends ListRecords
{
    protected static string $resource = HargaSppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
