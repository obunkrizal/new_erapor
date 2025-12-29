<?php

namespace App\Filament\Resources\GuruKelasResource\Pages;

use App\Filament\Resources\GuruKelasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGuruKelas extends EditRecord
{
    protected static string $resource = GuruKelasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
