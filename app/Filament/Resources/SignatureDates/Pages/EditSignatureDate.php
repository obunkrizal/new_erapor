<?php

namespace App\Filament\Resources\SignatureDates\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\SignatureDates\SignatureDateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSignatureDate extends EditRecord
{
    protected static string $resource = SignatureDateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
