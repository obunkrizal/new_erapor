<?php

namespace App\Filament\Resources\PembayaranSpps\Pages;

use App\Filament\Resources\PembayaranSpps\PembayaranSppResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePembayaranSpp extends CreateRecord
{
    protected static string $resource = PembayaranSppResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
