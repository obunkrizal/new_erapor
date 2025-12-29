<?php

namespace App\Filament\Resources\DimensiPembelajarans;

use App\Filament\Resources\DimensiPembelajarans\Pages\CreateDimensiPembelajaran;
use App\Filament\Resources\DimensiPembelajarans\Pages\EditDimensiPembelajaran;
use App\Filament\Resources\DimensiPembelajarans\Pages\ListDimensiPembelajarans;
use App\Filament\Resources\DimensiPembelajarans\Schemas\DimensiPembelajaranForm;
use App\Filament\Resources\DimensiPembelajarans\Tables\DimensiPembelajaransTable;
use App\Models\DimensiPembelajaran;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DimensiPembelajaranResource extends Resource
{
    protected static ?string $model = DimensiPembelajaran::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::AdjustmentsHorizontal;

    protected static ?string $recordTitleAttribute = 'Dimensi Pembelajaran';

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Pembelajaran';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return DimensiPembelajaranForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DimensiPembelajaransTable::configure($table);
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
            'index' => ListDimensiPembelajarans::route('/'),
            'create' => CreateDimensiPembelajaran::route('/create'),
            'edit' => EditDimensiPembelajaran::route('/{record}/edit'),
        ];
    }
}
