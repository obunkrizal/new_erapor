<?php

namespace App\Filament\Resources\TemplateNarasis\Pages;

use App\Filament\Resources\TemplateNarasis\TemplateNarasiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTemplateNarasis extends ListRecords
{
    protected static string $resource = TemplateNarasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
