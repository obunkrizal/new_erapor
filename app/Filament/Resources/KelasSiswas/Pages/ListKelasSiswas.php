<?php

namespace App\Filament\Resources\KelasSiswas\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\KelasSiswas\KelasSiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKelasSiswas extends ListRecords
{
    protected static string $resource = KelasSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
