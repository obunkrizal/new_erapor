<?php

namespace App\Filament\Resources\HargaSppResource\Pages;

use App\Filament\Resources\HargaSppResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHargaSpp extends EditRecord
{
    protected static string $resource = HargaSppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
