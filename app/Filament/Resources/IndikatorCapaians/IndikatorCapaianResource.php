<?php

namespace App\Filament\Resources\IndikatorCapaians;

use App\Filament\Resources\IndikatorCapaians\Pages\CreateIndikatorCapaian;
use App\Filament\Resources\IndikatorCapaians\Pages\EditIndikatorCapaian;
use App\Filament\Resources\IndikatorCapaians\Pages\ListIndikatorCapaians;
use App\Filament\Resources\IndikatorCapaians\Schemas\IndikatorCapaianForm;
use App\Filament\Resources\IndikatorCapaians\Tables\IndikatorCapaiansTable;
use App\Models\IndikatorCapaian;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class IndikatorCapaianResource extends Resource
{
    protected static ?string $model = IndikatorCapaian::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChartBarSquare;

    protected static ?string $recordTitleAttribute = 'Indikator Capaian';

    protected static string | UnitEnum |null$navigationGroup = 'Manajemen Pembelajaran';

    protected static ?string $navigationLabel = 'Indikator Capaian';

    protected static ?string $pluralLabel = 'Indikator Capaian';

    protected static ?string $slug = 'indikator-capaians';

    protected static ?string $label = 'Indikator Capaian';

    protected static ?int $navigationSort = 2;




    public static function form(Schema $schema): Schema
    {
        return IndikatorCapaianForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IndikatorCapaiansTable::configure($table);
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
            'index' => ListIndikatorCapaians::route('/'),
            'create' => CreateIndikatorCapaian::route('/create'),
            'edit' => EditIndikatorCapaian::route('/{record}/edit'),
        ];
    }
}
