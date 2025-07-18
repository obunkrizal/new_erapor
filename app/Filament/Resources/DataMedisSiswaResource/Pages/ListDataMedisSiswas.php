<?php

namespace App\Filament\Resources\DataMedisSiswaResource\Pages;

use App\Filament\Resources\DataMedisSiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDataMedisSiswas extends ListRecords
{
    protected static string $resource = DataMedisSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Data Medis Siswa')
                ->color('primary')
                ->icon('heroicon-o-document-plus'),
        ];
    }
}
