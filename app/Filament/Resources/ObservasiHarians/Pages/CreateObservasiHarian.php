<?php

namespace App\Filament\Resources\ObservasiHarians\Pages;

use App\Filament\Resources\ObservasiHarians\ObservasiHarianResource;
use App\Models\ObservasiHarian;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateObservasiHarian extends CreateRecord
{
    protected static string $resource = ObservasiHarianResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remove fields that are not part of the model
        unset($data['multiple_selection']);
        unset($data['siswa_ids']);
        unset($data['periode_id']); // Assuming periode_id is not needed in the model

        return $data;
    }

    public function create(bool $another = false): void
    {
        $form = $this->form;
        $data = $form->getState();

        $multipleSelection = $data['multiple_selection'] ?? false;
        $siswaIds = $data['siswa_ids'] ?? [];
        $siswaId = $data['siswa_id'] ?? null;

        // Prepare base data
        $baseData = $this->mutateFormDataBeforeCreate($data);

        if ($multipleSelection && !empty($siswaIds)) {
            // Create multiple records
            foreach ($siswaIds as $id) {
                $recordData = array_merge($baseData, ['siswa_id' => $id]);
                ObservasiHarian::create($recordData);
            }

            Notification::make()
                ->title('Observasi Harian berhasil dibuat')
                ->body(count($siswaIds) . ' record observasi harian telah dibuat.')
                ->success()
                ->send();

        } elseif (!$multipleSelection && $siswaId) {
            // Create single record
            ObservasiHarian::create($baseData);

            Notification::make()
                ->title('Observasi Harian berhasil dibuat')
                ->success()
                ->send();

        } else {
            Notification::make()
                ->title('Error')
                ->body('Pilih siswa terlebih dahulu.')
                ->danger()
                ->send();
            return;
        }

        if ($another) {
            // If creating another, reset the form
            $this->form->fill();
        } else {
            // Redirect to index
            $this->redirect($this->getResource()::getUrl('index'));
        }
    }
}
