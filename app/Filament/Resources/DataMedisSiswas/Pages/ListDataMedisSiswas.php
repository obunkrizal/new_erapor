<?php

namespace App\Filament\Resources\DataMedisSiswas\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\DataMedisSiswas\DataMedisSiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDataMedisSiswas extends ListRecords
{
    protected static string $resource = DataMedisSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Data Medis Siswa')
                ->color('primary')
                ->icon('heroicon-o-document-plus'),
        ];
    }
}
