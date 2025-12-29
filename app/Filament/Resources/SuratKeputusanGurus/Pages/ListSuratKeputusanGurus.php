<?php

namespace App\Filament\Resources\SuratKeputusanGuruResource\Pages;

use App\Filament\Resources\SuratKeputusanGuruResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSuratKeputusanGurus extends ListRecords
{
    protected static string $resource = SuratKeputusanGuruResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
