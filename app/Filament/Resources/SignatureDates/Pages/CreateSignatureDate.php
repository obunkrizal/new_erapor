<?php

namespace App\Filament\Resources\SignatureDates\Pages;

use App\Filament\Resources\SignatureDates\SignatureDateResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSignatureDate extends CreateRecord
{
    protected static string $resource = SignatureDateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
