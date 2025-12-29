<?php

namespace App\Filament\Resources\KelasSiswas\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\KelasSiswas\KelasSiswaResource;

class EditKelasSiswa extends EditRecord
{
    protected static string $resource = KelasSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ensure siswa_id is properly set for edit form
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure siswa_id is never 0 or null
        if (empty($data['siswa_id']) || $data['siswa_id'] == 0) {
            // Keep the original siswa_id if no valid new one is provided
            $data['siswa_id'] = $this->record->siswa_id;
        }

        // Log the data for debugging
        Log::info('EditKelasSiswa - Data before save:', $data);

        return $data;
    }

    protected function beforeSave(): void
    {
        // Additional validation before saving
        $data = $this->form->getState();

        if (empty($data['siswa_id']) || $data['siswa_id'] == 0) {
            Notification::make()
                ->title('Error')
                ->body('Siswa ID tidak valid. Silakan pilih siswa yang valid.')
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
