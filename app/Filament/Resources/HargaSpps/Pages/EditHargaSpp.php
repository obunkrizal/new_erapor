<?php

namespace App\Filament\Resources\HargaSpps\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\HargaSpps\HargaSppResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHargaSpp extends EditRecord
{
    protected static string $resource = HargaSppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
