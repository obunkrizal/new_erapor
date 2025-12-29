<?php

namespace App\Filament\Resources\HargaSpps;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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
use App\Filament\Resources\HargaSpps\Pages\ListHargaSpps;
use App\Filament\Resources\HargaSpps\Pages\CreateHargaSpp;
use App\Filament\Resources\HargaSpps\Pages\EditHargaSpp;
use App\Filament\Resources\HargaSppResource\Pages;
use App\Models\HargaSpp;
use App\Models\Periode;
use App\Models\Kelas;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class HargaSppResource extends Resource
{
    protected static ?string $model = HargaSpp::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Harga SPP';

    protected static ?string $modelLabel = 'Harga SPP';

    protected static ?string $pluralModelLabel = 'Harga SPP';

    protected static string | \UnitEnum | null $navigationGroup = 'Transaksi SPP';

    protected static ?int $navigationSort = 6;

    public static function canAccess(): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Periode & Kelas')
                    ->schema([
                Select::make('periode_id')
                            ->label('Periode')
                            ->relationship('periode', 'nama_periode')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('kelas_id', null)),

                Select::make('kelas_id')
                            ->label('Kelas Spesifik (Opsional)')
                    ->options(function (Get $get) {
                                $periodeId = $get('periode_id');
                                if (!$periodeId) return [];

                                return Kelas::where('periode_id', $periodeId)
                                    ->pluck('nama_kelas', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->helperText('Pilih kelas spesifik atau gunakan tingkat kelas'),


                    ])
                    ->columns(2),

            Section::make('Informasi Harga')
                    ->schema([
                TextInput::make('harga')
                            ->label('Harga SPP')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->step(1000)
                            ->minValue(0)
                            ->maxValue(99999999999999.99)
                            ->placeholder('500000'),

                Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(3)
                            ->placeholder('Keterangan tambahan tentang harga SPP ini'),

                Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Hanya harga aktif yang akan digunakan dalam sistem'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            TextColumn::make('periode.nama_periode')
                    ->label('Periode')
                    ->searchable()
                    ->sortable(),

            TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->searchable()
                    ->placeholder('Semua kelas')
                    ->sortable(),



            TextColumn::make('harga')
                    ->label('Harga SPP')
                    ->money('idr', true)
                    ->sortable(),

            IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

            TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            SelectFilter::make('periode_id')
                    ->label('Periode')
                    ->relationship('periode', 'nama_periode')
                    ->searchable()
                    ->preload(),

            TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => ListHargaSpps::route('/'),
            'create' => CreateHargaSpp::route('/create'),
            'edit' => EditHargaSpp::route('/{record}/edit'),
        ];
    }
}
