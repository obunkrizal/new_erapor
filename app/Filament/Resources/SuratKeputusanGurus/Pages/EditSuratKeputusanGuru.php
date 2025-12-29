<?php

namespace App\Filament\Resources\SuratKeputusanGurus\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\SuratKeputusanGurus\SuratKeputusanGuruResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSuratKeputusanGuru extends EditRecord
{
    protected static string $resource = SuratKeputusanGuruResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
