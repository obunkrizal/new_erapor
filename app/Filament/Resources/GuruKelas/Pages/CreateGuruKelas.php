<?php

namespace App\Filament\Resources\GuruKelas\Pages;

use App\Filament\Resources\GuruKelas\GuruKelasResource;
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
