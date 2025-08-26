<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HargaSppResource\Pages;
use App\Models\HargaSpp;
use App\Models\Periode;
use App\Models\Kelas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class HargaSppResource extends Resource
{
    protected static ?string $model = HargaSpp::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Harga SPP';

    protected static ?string $modelLabel = 'Harga SPP';

    protected static ?string $pluralModelLabel = 'Harga SPP';

    protected static ?string $navigationGroup = 'Transaksi SPP';

    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Periode & Kelas')
                    ->schema([
                        Forms\Components\Select::make('periode_id')
                            ->label('Periode')
                            ->relationship('periode', 'nama_periode')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('kelas_id', null)),

                        Forms\Components\Select::make('kelas_id')
                            ->label('Kelas Spesifik (Opsional)')
                            ->options(function (Forms\Get $get) {
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

                Forms\Components\Section::make('Informasi Harga')
                    ->schema([
                        Forms\Components\TextInput::make('harga')
                            ->label('Harga SPP')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->step(1000)
                            ->minValue(0)
                            ->maxValue(99999999999999.99)
                            ->placeholder('500000'),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(3)
                            ->placeholder('Keterangan tambahan tentang harga SPP ini'),

                        Forms\Components\Toggle::make('is_active')
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
                Tables\Columns\TextColumn::make('periode.nama_periode')
                    ->label('Periode')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->searchable()
                    ->placeholder('Semua kelas')
                    ->sortable(),



                Tables\Columns\TextColumn::make('harga')
                    ->label('Harga SPP')
                    ->money('idr', true)
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('periode_id')
                    ->label('Periode')
                    ->relationship('periode', 'nama_periode')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListHargaSpps::route('/'),
            'create' => Pages\CreateHargaSpp::route('/create'),
            'edit' => Pages\EditHargaSpp::route('/{record}/edit'),
        ];
    }
}
