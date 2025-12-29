<?php

namespace App\Filament\Resources\Kelas\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Kelas\KelasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKelas extends ListRecords
{
    protected static string $resource = KelasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Kelas Baru')
                ->icon('heroicon-o-plus'),
        ];
    }
}
