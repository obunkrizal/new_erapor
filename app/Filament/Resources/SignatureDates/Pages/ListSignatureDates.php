<?php

namespace App\Filament\Resources\SignatureDates\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\SignatureDates\SignatureDateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSignatureDates extends ListRecords
{
    protected static string $resource = SignatureDateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
