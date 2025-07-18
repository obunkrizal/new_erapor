<?php

namespace App\Filament\Resources\KelasSiswaResource\Pages;

use App\Filament\Resources\KelasSiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewKelasSiswa extends ViewRecord
{
    protected static string $resource = KelasSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
