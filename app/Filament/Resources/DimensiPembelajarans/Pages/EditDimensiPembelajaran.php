<?php

namespace App\Filament\Resources\DimensiPembelajarans\Pages;

use App\Filament\Resources\DimensiPembelajarans\DimensiPembelajaranResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDimensiPembelajaran extends EditRecord
{
    protected static string $resource = DimensiPembelajaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
