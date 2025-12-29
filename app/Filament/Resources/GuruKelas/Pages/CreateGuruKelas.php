<?php

namespace App\Filament\Resources\GuruKelasResource\Pages;

use App\Filament\Resources\GuruKelasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateGuruKelas extends CreateRecord
{
    protected static string $resource = GuruKelasResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
