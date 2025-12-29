<?php

namespace App\Filament\Resources\TemplateNarasis\Pages;

use App\Filament\Resources\TemplateNarasis\TemplateNarasiResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTemplateNarasi extends EditRecord
{
    protected static string $resource = TemplateNarasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
