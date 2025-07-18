<?php

namespace App\Filament\Resources\SuratKeputusanGuruResource\Pages;

use App\Filament\Resources\SuratKeputusanGuruResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSuratKeputusanGuru extends EditRecord
{
    protected static string $resource = SuratKeputusanGuruResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
