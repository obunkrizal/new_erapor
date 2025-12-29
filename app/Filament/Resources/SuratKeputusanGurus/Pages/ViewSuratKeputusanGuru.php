<?php

namespace App\Filament\Resources\SuratKeputusanGurus\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\SuratKeputusanGurus\SuratKeputusanGuruResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSuratKeputusanGuru extends ViewRecord
{
    protected static string $resource = SuratKeputusanGuruResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('Cetak Surat')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn (): string => route('surat-keputusan.print', $this->record))
                ->openUrlInNewTab(),

            Action::make('download')
                ->label('Unduh PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->url(fn (): string => route('surat-keputusan.download', $this->record)),

            EditAction::make()
                ->icon('heroicon-o-pencil-square'),

            DeleteAction::make()
                ->icon('heroicon-o-trash'),
        ];
    }
}
