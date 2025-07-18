<?php

namespace App\Filament\Resources\NilaiResource\Pages;

use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use App\Filament\Resources\NilaiResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Actions;

class CreateNilai extends CreateRecord
{
    protected static string $resource = NilaiResource::class;

    public function mount(): void
    {
        // Redirect admin away from create page
        if (Auth::user()->isAdmin()) {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Admin tidak dapat membuat data nilai baru.')
                ->warning()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }

        parent::mount();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        try {
            // Log the incoming data for debugging
            Log::info('Form data before create:', [
                'fotoAgama_count' => is_array($data['fotoAgama'] ?? null) ? count($data['fotoAgama']) : 0,
                'fotoJatiDiri_count' => is_array($data['fotoJatiDiri'] ?? null) ? count($data['fotoJatiDiri']) : 0,
                'fotoLiterasi_count' => is_array($data['fotoLiterasi'] ?? null) ? count($data['fotoLiterasi']) : 0,
                'fotoNarasi_count' => is_array($data['fotoNarasi'] ?? null) ? count($data['fotoNarasi']) : 0,
            ]);

            // Handle image uploads using the model method
            $model = new (static::getModel());
            $processedData = $model->handleImageUploads($data);

            Log::info('Processed data after upload handling:', [
                'fotoAgama_final_count' => is_array($processedData['fotoAgama'] ?? null) ? count($processedData['fotoAgama']) : 0,
            ]);

            return $processedData;
        } catch (\Exception $e) {
            Log::error('Create Nilai error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title('Error Upload Gambar')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();

            throw $e;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Penilaian berhasil dibuat')
            ->body('Data penilaian siswa telah berhasil disimpan.');
    }
}
