<?php

namespace App\Filament\Resources\SiswaEkstrakurikulers\Pages;

use App\Filament\Resources\SiswaEkstrakurikulers\SiswaEkstrakurikulerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSiswaEkstrakurikuler extends EditRecord
{
    protected static string $resource = SiswaEkstrakurikulerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
