<?php

namespace App\Filament\Resources\GuruKelas\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\GuruKelas\GuruKelasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGuruKelas extends EditRecord
{
    protected static string $resource = GuruKelasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
