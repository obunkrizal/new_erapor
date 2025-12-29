<?php

namespace App\Filament\Resources\KelasSiswas\Pages;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\KelasSiswas\KelasSiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewKelasSiswa extends ViewRecord
{
    protected static string $resource = KelasSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
