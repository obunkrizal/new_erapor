<?php

namespace App\Filament\Resources\PembayaranSpps\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\PembayaranSpps\PembayaranSppResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPembayaranSpps extends ListRecords
{
    protected static string $resource = PembayaranSppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            // Actions\Action::make('printLaporan')
            //     ->label('Cetak Laporan')
            //     ->icon('heroicon-o-printer')
            //     ->url(route('pembayaran-spp.print-laporan'))
            //     ->button()
            //     ->openUrlInNewTab()
            //     ->color('secondary'),
        ];
    }
}
