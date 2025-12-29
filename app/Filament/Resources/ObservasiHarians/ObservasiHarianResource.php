<?php

namespace App\Filament\Resources\ObservasiHarians;

use UnitEnum;
use BackedEnum;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\ObservasiHarian;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\ObservasiHarians\Pages\EditObservasiHarian;
use App\Filament\Resources\ObservasiHarians\Pages\ListObservasiHarians;
use App\Filament\Resources\ObservasiHarians\Pages\CreateObservasiHarian;
use App\Filament\Resources\ObservasiHarians\Schemas\ObservasiHarianForm;
use App\Filament\Resources\ObservasiHarians\Tables\ObservasiHariansTable;

class ObservasiHarianResource extends Resource
{
    protected static ?string $model = ObservasiHarian::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Observasi Harian';

    protected static string|UnitEnum|null $navigationGroup = 'Akademik';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return ObservasiHarianForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ObservasiHariansTable::configure($table);
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
            'index' => ListObservasiHarians::route('/'),
            'create' => CreateObservasiHarian::route('/create'),
            'edit' => EditObservasiHarian::route('/{record}/edit'),
        ];
    }

    // ObservasiHarianResource.php



}
