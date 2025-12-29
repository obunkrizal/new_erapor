<?php

namespace App\Filament\Resources\Siswas\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Siswas\SiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSiswas extends ListRecords
{
    protected static string $resource = SiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
