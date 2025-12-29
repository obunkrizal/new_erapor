<?php

namespace App\Filament\Resources\SignatureDateResource\Pages;

use App\Filament\Resources\SignatureDateResource;
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
