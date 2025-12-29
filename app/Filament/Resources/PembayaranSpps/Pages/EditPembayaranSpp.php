<?php

namespace App\Filament\Resources\PembayaranSpps\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\PembayaranSpps\PembayaranSppResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPembayaranSpp extends EditRecord
{
    protected static string $resource = PembayaranSppResource::class;

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
