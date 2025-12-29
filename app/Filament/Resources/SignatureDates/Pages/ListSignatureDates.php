<?php

namespace App\Filament\Resources\SignatureDateResource\Pages;

use App\Filament\Resources\SignatureDateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSignatureDates extends ListRecords
{
    protected static string $resource = SignatureDateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
