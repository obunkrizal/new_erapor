<?php

namespace App\Filament\Resources\KelasSiswas\Pages;

use Illuminate\Database\Eloquent\Model;
use App\Models\KelasSiswa;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\KelasSiswas\KelasSiswaResource;

class CreateKelasSiswa extends CreateRecord
{
    protected static string $resource = KelasSiswaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Log the incoming data for debugging
        Log::info('CreateKelasSiswa - Form data:', $data);

        // Handle multiple selection
        if (!empty($data['siswa_ids']) && is_array($data['siswa_ids'])) {
            // For multiple selection, we'll handle this in handleRecordCreation
            return $data;
        }

        // For single selection, ensure siswa_id is valid
        if (empty($data['siswa_id']) || $data['siswa_id'] == 0) {
            Notification::make()
                ->title('Error')
                ->body('Silakan pilih siswa yang valid.')
                ->danger()
                ->send();

            $this->halt();
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Handle multiple students
        if (!empty($data['siswa_ids']) && is_array($data['siswa_ids'])) {
            $createdRecords = [];

            foreach ($data['siswa_ids'] as $siswaId) {
                if (empty($siswaId) || $siswaId == 0) {
                    continue; // Skip invalid siswa_id
                }

                $recordData = array_merge($data, [
                    'siswa_id' => $siswaId,
                ]);

                // Remove siswa_ids from individual record data
                unset($recordData['siswa_ids']);
                unset($recordData['multiple_selection']);

                $createdRecords[] = KelasSiswa::create($recordData);
            }

            if (empty($createdRecords)) {
                Notification::make()
                    ->title('Error')
                    ->body('Tidak ada siswa yang valid untuk ditambahkan.')
                    ->danger()
                    ->send();

                $this->halt();
            }

            Notification::make()
                ->title('Berhasil')
                ->body(count($createdRecords) . ' siswa berhasil ditambahkan ke kelas.')
                ->success()
                ->send();

            // Return the first created record
            return $createdRecords[0];
        }

        // Handle single student
        if (empty($data['siswa_id']) || $data['siswa_id'] == 0) {
            Notification::make()
                ->title('Error')
                ->body('Siswa ID tidak valid.')
                ->danger()
                ->send();

            $this->halt();
        }

        // Remove unnecessary fields
        unset($data['siswa_ids']);
        unset($data['multiple_selection']);

        return KelasSiswa::create($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
