<?php

namespace App\Filament\Resources\KelasSiswaResource\Pages;

use App\Filament\Resources\KelasSiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKelasSiswas extends ListRecords
{
    protected static string $resource = KelasSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
