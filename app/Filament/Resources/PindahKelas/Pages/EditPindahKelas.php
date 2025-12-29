<?php

namespace App\Filament\Resources\PindahKelas\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\PindahKelas\PindahKelasResource;
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
            DeleteAction::make(),
        ];
    }
}
