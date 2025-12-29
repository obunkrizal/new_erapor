<?php

namespace App\Filament\Resources\DimensiPembelajarans\Pages;

use App\Filament\Resources\DimensiPembelajarans\DimensiPembelajaranResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDimensiPembelajarans extends ListRecords
{
    protected static string $resource = DimensiPembelajaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
