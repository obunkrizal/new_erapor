<?php

namespace App\Filament\Resources\PenilaianSemesters\Pages;

use App\Filament\Resources\PenilaianSemesters\PenilaianSemesterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPenilaianSemester extends EditRecord
{
    protected static string $resource = PenilaianSemesterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
