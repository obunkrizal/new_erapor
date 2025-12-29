<?php

namespace App\Filament\Resources\GuruSiswaKelas\Pages;

use App\Filament\Resources\GuruSiswaKelas\GuruSiswaKelasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGuruSiswaKelas extends EditRecord
{
    protected static string $resource = GuruSiswaKelasResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
