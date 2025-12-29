<?php

namespace App\Filament\Resources\Absensis\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Absensis\AbsensiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAbsensis extends ListRecords
{
    protected static string $resource = AbsensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Absensi')
                ->color('primary')
                ->icon('heroicon-o-document-plus'),
        ];
    }
}
