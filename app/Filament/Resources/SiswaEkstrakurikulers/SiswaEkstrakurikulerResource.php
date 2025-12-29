<?php

namespace App\Filament\Resources\SiswaEkstrakurikulers;

use App\Filament\Resources\SiswaEkstrakurikulers\Pages\CreateSiswaEkstrakurikuler;
use App\Filament\Resources\SiswaEkstrakurikulers\Pages\EditSiswaEkstrakurikuler;
use App\Filament\Resources\SiswaEkstrakurikulers\Pages\ListSiswaEkstrakurikulers;
use App\Filament\Resources\SiswaEkstrakurikulers\Schemas\SiswaEkstrakurikulerForm;
use App\Filament\Resources\SiswaEkstrakurikulers\Tables\SiswaEkstrakurikulersTable;
use App\Models\SiswaEkstrakurikuler;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class SiswaEkstrakurikulerResource extends Resource
{
    protected static ?string $model = SiswaEkstrakurikuler::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowDownRight;

    protected static ?string $recordTitleAttribute = 'Ekstrakurikuler Siswa';

    protected static string|UnitEnum|null $navigationGroup = 'Akademik';

    protected static ?int $navigationSort=3;

    public static function form(Schema $schema): Schema
    {
        return SiswaEkstrakurikulerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SiswaEkstrakurikulersTable::configure($table);
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
            'index' => ListSiswaEkstrakurikulers::route('/'),
            'create' => CreateSiswaEkstrakurikuler::route('/create'),
            'edit' => EditSiswaEkstrakurikuler::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
