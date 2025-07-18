<?php

namespace App\Filament\Resources\SignatureDateResource\Pages;

use App\Filament\Resources\SignatureDateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSignatureDate extends EditRecord
{
    protected static string $resource = SignatureDateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
