<?php

namespace App\Filament\Resources\IndikatorCapaians\Pages;

use App\Filament\Resources\IndikatorCapaians\IndikatorCapaianResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIndikatorCapaians extends ListRecords
{
    protected static string $resource = IndikatorCapaianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
