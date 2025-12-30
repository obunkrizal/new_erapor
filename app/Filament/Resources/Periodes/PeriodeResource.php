<?php

namespace App\Filament\Resources\Periodes;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Periodes\Pages\ListPeriodes;
use App\Filament\Resources\Periodes\Pages\CreatePeriode;
use App\Filament\Resources\Periodes\Pages\EditPeriode;
use Filament\Forms;
use Filament\Tables;
use App\Models\Periode;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PeriodeResource\Pages;

class PeriodeResource extends Resource
{
    protected static ?string $model = Periode::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Periode Akademik';
    protected static ?string $modelLabel = 'Periode';
    protected static ?string $pluralModelLabel = 'Periode Akademik';
    protected static string | \UnitEnum | null $navigationGroup = 'Data Master';
    protected static ?int $navigationSort = 2;

    // Hide navigation for guru
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    // Control access - only admin can access
    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canEdit($record): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canDelete($record): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Periode')
                ->schema([

                Select::make('tahun_ajaran')
                            ->label('Tahun Ajaran')
                    ->options(self::getTahunAjaranOptions())
                    ->required()
                    ->searchable()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        self::generateNamaPeriode($set, $get);
                    }),

                        Select::make('semester')
                            ->label('Semester')
                            ->options([
                                'ganjil' => 'Ganjil',
                                'genap' => 'Genap',
                            ])
                    ->required()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        self::generateNamaPeriode($set, $get);
                    }),

                TextInput::make('nama_periode')
                    ->label('Nama Periode')
                    ->required()
                    ->readOnly()
                    ->dehydrated()
                    ->helperText('Nama periode akan dibuat otomatis berdasarkan tahun ajaran dan semester'),

                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(false),
                    ])
                    ->columns(2),
            ]);
    }

    private static function getTahunAjaranOptions(): array
    {
        $currentYear = date('Y');
        $options = [];

        // Generate options for 5 years back and 5 years forward
        for ($i = -5; $i <= 5; $i++) {
            $startYear = $currentYear + $i;
            $endYear = $startYear + 1;
            $tahunAjaran = $startYear . '/' . $endYear;
            $options[$tahunAjaran] = $tahunAjaran;
        }

        return $options;
    }

    private static function generateNamaPeriode(Set $set, Get $get): void
    {
        $tahunAjaran = $get('tahun_ajaran');
        $semester = $get('semester');

        if ($tahunAjaran && $semester) {
            $semesterLabel = $semester === 'ganjil' ? 'Ganjil' : 'Genap';
            $namaPeriode = "Semester {$semesterLabel} {$tahunAjaran}";
            $set('nama_periode', $namaPeriode);
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_periode')
                    ->label('Nama Periode')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('semester')
                    ->label('Semester')
                    ->badge()
                ->color(fn(string $state): string => match ($state) {
                        'ganjil' => 'info',
                        'genap' => 'success',
                        default => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
            SelectFilter::make('tahun_ajaran')
                ->label('Tahun Ajaran')
                ->options(self::getTahunAjaranOptions()),

            SelectFilter::make('semester')
                    ->options([
                        'ganjil' => 'Ganjil',
                        'genap' => 'Genap',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->recordActions([
                ActionGroup::make([
                ViewAction::make(),

                // Only show for admin
                EditAction::make()
                    ->visible(fn() => Auth::user()?->isAdmin()),

                DeleteAction::make()
                    ->visible(fn() => Auth::user()?->isAdmin()),
                ])
                ->label('Aksi')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn() => Auth::user()?->isAdmin()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPeriodes::route('/'),
            'create' => CreatePeriode::route('/create'),
            // 'view' => Pages\ViewPeriode::route('/{record}'),
            'edit' => EditPeriode::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        if (!Auth::user()?->isAdmin()) {
            return null;
        }

        return static::getModel()::count();
    }
}
