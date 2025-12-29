<?php

namespace App\Filament\Resources\IndikatorCapaians\Pages;

use App\Filament\Resources\IndikatorCapaians\IndikatorCapaianResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIndikatorCapaian extends EditRecord
{
    protected static string $resource = IndikatorCapaianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
