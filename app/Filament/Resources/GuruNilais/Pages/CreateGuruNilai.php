<?php

namespace App\Filament\Resources\GuruNilaiResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use App\Filament\Resources\NilaiResource;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\GuruNilaiResource;

class CreateGuruNilai extends CreateRecord
{
    protected static string $resource = GuruNilaiResource::class;

    
    protected function getRedirectUrl(): string
    {
        // Option 1: Redirect to print page
        // return route('nilai.print', ['nilai' => $this->record]);

        // Option 2: Redirect to NilaiResource index with filter
        // return route('filament.admin.resources.nilais.index', [
        //     'tableFilters' => [
        //         'siswa_id' => ['value' => $this->record->siswa_id]
        //     ]
        // ]);

        // Option 3: Stay in GuruNilaiResource
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Penilaian berhasil dibuat')
            ->body('Penilaian telah berhasil disimpan.')
            ->duration(5000);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure guru_id is set
        $data['guru_id'] = auth()->user()->guru->id;

        // Set periode_id if not already set
        if (empty($data['periode_id']) && !empty($data['kelas_id'])) {
            $kelas = \App\Models\Kelas::find($data['kelas_id']);
            $data['periode_id'] = $kelas?->periode_id;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}
