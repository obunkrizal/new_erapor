<?php

namespace App\Filament\Resources\ObservasiHarians\Pages;

use App\Filament\Resources\ObservasiHarians\ObservasiHarianResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditObservasiHarian extends EditRecord
{
    protected static string $resource = ObservasiHarianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
