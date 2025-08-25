<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Periode;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PeriodeResource\Pages;
use Filament\Tables\Actions\ActionGroup;

class PeriodeResource extends Resource
{
    protected static ?string $model = Periode::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Periode Akademik';
    protected static ?string $modelLabel = 'Periode';
    protected static ?string $pluralModelLabel = 'Periode Akademik';
    protected static ?string $navigationGroup = 'Akademik';
    protected static ?int $navigationSort = 3;

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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Periode')
                ->schema([

                Forms\Components\Select::make('tahun_ajaran')
                            ->label('Tahun Ajaran')
                    ->options(self::getTahunAjaranOptions())
                    ->required()
                    ->searchable()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        self::generateNamaPeriode($set, $get);
                    }),

                        Forms\Components\Select::make('semester')
                            ->label('Semester')
                            ->options([
                                'ganjil' => 'Ganjil',
                                'genap' => 'Genap',
                            ])
                    ->required()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        self::generateNamaPeriode($set, $get);
                    }),

                Forms\Components\TextInput::make('nama_periode')
                    ->label('Nama Periode')
                    ->required()
                    ->readOnly()
                    ->dehydrated()
                    ->helperText('Nama periode akan dibuat otomatis berdasarkan tahun ajaran dan semester'),

                        Forms\Components\Toggle::make('is_active')
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

    private static function generateNamaPeriode(Forms\Set $set, Forms\Get $get): void
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
                Tables\Columns\TextColumn::make('nama_periode')
                    ->label('Nama Periode')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('semester')
                    ->label('Semester')
                    ->badge()
                ->color(fn(string $state): string => match ($state) {
                        'ganjil' => 'info',
                        'genap' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
            Tables\Filters\SelectFilter::make('tahun_ajaran')
                ->label('Tahun Ajaran')
                ->options(self::getTahunAjaranOptions()),

            Tables\Filters\SelectFilter::make('semester')
                    ->options([
                        'ganjil' => 'Ganjil',
                        'genap' => 'Genap',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                ActionGroup::make([
                Tables\Actions\ViewAction::make(),

                // Only show for admin
                Tables\Actions\EditAction::make()
                    ->visible(fn() => Auth::user()?->isAdmin()),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => Auth::user()?->isAdmin()),
                ])
                ->label('Aksi')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => Auth::user()?->isAdmin()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeriodes::route('/'),
            'create' => Pages\CreatePeriode::route('/create'),
            // 'view' => Pages\ViewPeriode::route('/{record}'),
            'edit' => Pages\EditPeriode::route('/{record}/edit'),
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
