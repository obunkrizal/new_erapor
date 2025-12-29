<?php

namespace App\Filament\Resources\SuratKeputusanGurus\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\SuratKeputusanGurus\SuratKeputusanGuruResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSuratKeputusanGurus extends ListRecords
{
    protected static string $resource = SuratKeputusanGuruResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
