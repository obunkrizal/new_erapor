<?php

namespace App\Filament\Resources\DataMedisSiswas\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\DataMedisSiswas\DataMedisSiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDataMedisSiswa extends EditRecord
{
    protected static string $resource = DataMedisSiswaResource::class;

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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ensure all required fields are populated for edit form
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Remove any fields that shouldn't be updated in edit mode
        // The disabled fields will automatically be excluded, but this is for safety
        return $data;
    }
}
