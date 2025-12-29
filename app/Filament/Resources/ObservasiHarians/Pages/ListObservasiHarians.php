<?php

namespace App\Filament\Resources\ObservasiHarians\Pages;

use App\Filament\Resources\ObservasiHarians\ObservasiHarianResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListObservasiHarians extends ListRecords
{
    protected static string $resource = ObservasiHarianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
