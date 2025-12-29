<?php

namespace App\Filament\Resources\PindahKelas\Pages;

use App\Filament\Resources\PindahKelas\PindahKelasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePindahKelas extends CreateRecord
{
    protected static string $resource = PindahKelasResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
