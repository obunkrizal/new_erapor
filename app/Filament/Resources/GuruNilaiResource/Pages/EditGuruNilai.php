<?php

namespace App\Filament\Resources\GuruNilaiResource\Pages;

use App\Filament\Resources\GuruNilaiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditGuruNilai extends EditRecord
{
    protected static string $resource = GuruNilaiResource::class;

    protected function getRedirectUrl(): string
    {
        // Redirect to print page after update
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Penilaian berhasil diperbarui')
            ->body('Perubahan telah berhasil disimpan.')
            ->duration(5000);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_report')
                ->label('Lihat Rapor')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->url(fn (): string => route('nilai.print', ['nilai' => $this->record]))
                ->openUrlInNewTab(),

            Actions\DeleteAction::make(),

            Actions\Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}
