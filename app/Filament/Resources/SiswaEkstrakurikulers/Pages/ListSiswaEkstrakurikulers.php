<?php

namespace App\Filament\Resources\SiswaEkstrakurikulers\Pages;

use App\Filament\Resources\SiswaEkstrakurikulers\SiswaEkstrakurikulerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSiswaEkstrakurikulers extends ListRecords
{
    protected static string $resource = SiswaEkstrakurikulerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
