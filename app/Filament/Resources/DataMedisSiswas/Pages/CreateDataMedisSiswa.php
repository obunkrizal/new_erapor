<?php

namespace App\Filament\Resources\DataMedisSiswas\Pages;

use App\Filament\Resources\DataMedisSiswas\DataMedisSiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDataMedisSiswa extends CreateRecord
{
    protected static string $resource = DataMedisSiswaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
