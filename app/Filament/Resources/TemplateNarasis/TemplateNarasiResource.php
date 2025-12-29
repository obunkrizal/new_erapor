<?php

namespace App\Filament\Resources\TemplateNarasis;

use App\Filament\Resources\TemplateNarasis\Pages\CreateTemplateNarasi;
use App\Filament\Resources\TemplateNarasis\Pages\EditTemplateNarasi;
use App\Filament\Resources\TemplateNarasis\Pages\ListTemplateNarasis;
use App\Filament\Resources\TemplateNarasis\Schemas\TemplateNarasiForm;
use App\Filament\Resources\TemplateNarasis\Tables\TemplateNarasisTable;
use App\Models\TemplateNarasi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TemplateNarasiResource extends Resource
{
    protected static ?string $model = TemplateNarasi::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Pembelajaran';

    protected static ?int $navigationSort=2;

    protected static ?string $recordTitleAttribute = 'Template Narasi';

    public static function form(Schema $schema): Schema
    {
        return TemplateNarasiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TemplateNarasisTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTemplateNarasis::route('/'),
            'create' => CreateTemplateNarasi::route('/create'),
            'edit' => EditTemplateNarasi::route('/{record}/edit'),
        ];
    }
}
