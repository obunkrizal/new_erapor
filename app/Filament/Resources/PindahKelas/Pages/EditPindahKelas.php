<?php

namespace App\Filament\Resources\PindahKelasResource\Pages;

use App\Filament\Resources\PindahKelasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPindahKelas extends EditRecord
{
    protected static string $resource = PindahKelasResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
