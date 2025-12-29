<?php

namespace App\Filament\Resources\Absensis\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Absensis\AbsensiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

use Illuminate\Validation\ValidationException;

class EditAbsensi extends EditRecord
{
    protected static string $resource = AbsensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function beforeSave(): void
    {
        $data = $this->form->getState();

        $validationResult = AbsensiResource::validateUniqueAbsence(
            $data['siswa_id'] ?? null,
            $data['tanggal'] ?? null,
            $data['periode_id'] ?? null,
            $data['kelas_id'] ?? null,
            $this->record->id ?? null
        );

        if ($validationResult !== true) {
            throw ValidationException::withMessages([
                'siswa_id' => $validationResult,
            ]);
        }
    }
}
