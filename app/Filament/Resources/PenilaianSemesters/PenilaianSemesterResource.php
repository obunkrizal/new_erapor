<?php

namespace App\Filament\Resources\PenilaianSemesters;

use App\Filament\Resources\PenilaianSemesters\Pages\CreatePenilaianSemester;
use App\Filament\Resources\PenilaianSemesters\Pages\EditPenilaianSemester;
use App\Filament\Resources\PenilaianSemesters\Pages\ListPenilaianSemesters;
use App\Filament\Resources\PenilaianSemesters\Schemas\PenilaianSemesterForm;
use App\Filament\Resources\PenilaianSemesters\Tables\PenilaianSemestersTable;
use App\Models\PenilaianSemester;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PenilaianSemesterResource extends Resource
{
    protected static ?string $model = PenilaianSemester::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::AcademicCap;

    protected static ?string $recordTitleAttribute = 'Penilaian Semester';

    protected static string|UnitEnum|null $navigationGroup = 'Akademik';

    protected static ?int $navigationSort=4;
    public static function form(Schema $schema): Schema
    {
        return PenilaianSemesterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PenilaianSemestersTable::configure($table);
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
            'index' => ListPenilaianSemesters::route('/'),
            'create' => CreatePenilaianSemester::route('/create'),
            'edit' => EditPenilaianSemester::route('/{record}/edit'),
        ];
    }
}
