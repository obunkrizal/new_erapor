<?php

namespace App\Filament\Resources\Absensis\Pages;

use App\Filament\Resources\Absensis\AbsensiResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

use Illuminate\Validation\ValidationException;

class CreateAbsensi extends CreateRecord
{
    protected static string $resource = AbsensiResource::class;

    protected function beforeSave(): void
    {
        $data = $this->form->getState();

        $validationResult = AbsensiResource::validateUniqueAbsence(
            $data['siswa_id'] ?? null,
            $data['tanggal'] ?? null,
            $data['periode_id'] ?? null,
            $data['kelas_id'] ?? null,
            null
        );

        if ($validationResult !== true) {
            throw ValidationException::withMessages([
                'siswa_id' => $validationResult,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
