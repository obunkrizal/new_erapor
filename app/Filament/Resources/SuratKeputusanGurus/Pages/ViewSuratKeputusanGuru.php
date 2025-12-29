<?php

namespace App\Filament\Resources\SuratKeputusanGuruResource\Pages;

use App\Filament\Resources\SuratKeputusanGuruResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSuratKeputusanGuru extends ViewRecord
{
    protected static string $resource = SuratKeputusanGuruResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Cetak Surat')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn (): string => route('surat-keputusan.print', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('download')
                ->label('Unduh PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->url(fn (): string => route('surat-keputusan.download', $this->record)),

            Actions\EditAction::make()
                ->icon('heroicon-o-pencil-square'),

            Actions\DeleteAction::make()
                ->icon('heroicon-o-trash'),
        ];
    }
}
