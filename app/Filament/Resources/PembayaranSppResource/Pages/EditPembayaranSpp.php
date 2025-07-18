<?php

namespace App\Filament\Resources\PembayaranSppResource\Pages;

use App\Filament\Resources\PembayaranSppResource;
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
            Actions\DeleteAction::make(),
        ];
    }
}
