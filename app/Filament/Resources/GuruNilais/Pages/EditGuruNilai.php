<?php

namespace App\Filament\Resources\GuruNilais\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\GuruNilais\GuruNilaiResource;
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
            Action::make('view_report')
                ->label('Lihat Rapor')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->url(fn (): string => route('nilai.print', ['nilai' => $this->record]))
                ->openUrlInNewTab(),

            DeleteAction::make(),

            Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}
