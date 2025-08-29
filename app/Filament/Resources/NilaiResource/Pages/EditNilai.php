<?php

namespace App\Filament\Resources\NilaiResource\Pages;

use App\Filament\Resources\NilaiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class EditNilai extends EditRecord
{
    protected static string $resource = NilaiResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    public function mount(int | string $record): void
    {
        // Redirect admin away from edit page
        if (Auth::user()->isAdmin()) {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Admin tidak dapat mengedit data nilai.')
                ->warning()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }

        parent::mount($record);
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Penilaian berhasil diperbaharui')
            ->body('Perubahan telah berhasil disimpan.');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn (): bool => !Auth::user()->isAdmin()),
        ];
    }
}
