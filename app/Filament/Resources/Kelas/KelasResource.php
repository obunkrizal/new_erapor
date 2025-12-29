<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\Periode;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\KelasResource\Pages;
use Filament\Tables\Actions\ActionGroup;

class KelasResource extends Resource
{
    protected static ?string $model = Kelas::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Manajemen Kelas';
    protected static ?string $modelLabel = 'Kelas';
    protected static ?string $pluralModelLabel = 'Manajemen Kelas';
    protected static ?string $navigationGroup = 'Data Master';
    protected static ?int $navigationSort = 2;

    // Show navigation only for admin
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    // Control access - only admin can access
    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kelas')
                    ->description('Data dasar kelas dan pengaturan')
                    ->icon('heroicon-o-building-library')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nama_kelas')
                                    ->label('Nama Kelas')
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder('Contoh: Kelas A, 1A, dll')
                                    ->unique(ignoreRecord: true),

                                Forms\Components\TextInput::make('kapasitas')
                                    ->label('Kapasitas Kelas')
                                    ->required()
                                    ->numeric()
                                    ->default(30)
                                    ->minValue(1)
                                    ->maxValue(50)
                                    ->placeholder('Maksimal siswa dalam kelas')
                                    ->helperText('Jumlah maksimal siswa yang dapat diterima')
                                    ->suffixIcon('heroicon-m-users'),

                                Forms\Components\Select::make('guru_id')
                                    ->label('Wali Kelas')
                                    ->relationship('guru', 'nama_guru')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->placeholder('Pilih wali kelas'),

                                Forms\Components\Select::make('periode_id')
                                    ->label('Periode Akademik')
                                    ->relationship('periode', 'nama_periode')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->placeholder('Pilih periode akademik')
                                    ->default(fn() => Periode::where('is_active', true)->value('id')),

                                Forms\Components\Select::make('status')
                                    ->label('Status Kelas')
                                    ->options([
                                        'aktif' => 'Aktif',
                                        'tidak_aktif' => 'Tidak Aktif',
                                        'selesai' => 'Selesai',
                                    ])
                                    ->default('aktif')
                                    ->required()
                                    ->native(false),


                            ]),
                    ]),

                Forms\Components\Section::make('Statistik Kelas')
                    ->description('Informasi kapasitas dan siswa')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Placeholder::make('jumlah_siswa')
                                    ->label('Jumlah Siswa Aktif')
                                    ->content(function (?Kelas $record) {
                                        if (!$record) return '0 siswa';
                                        $count = $record->kelasSiswa()->where('status', 'aktif')->count();
                                        return "{$count} siswa";
                                    }),

                                Forms\Components\Placeholder::make('sisa_kapasitas')
                                    ->label('Sisa Kapasitas')
                                    ->content(function (?Kelas $record) {
                                        if (!$record) return '-';
                                        $count = $record->kelasSiswa()->where('status', 'aktif')->count();
                                        $sisa = $record->kapasitas - $count;
                                        return "{$sisa} siswa";
                                    }),

                                Forms\Components\Placeholder::make('persentase_kapasitas')
                                    ->label('Persentase Terisi')
                                    ->content(function (?Kelas $record) {
                                        if (!$record || $record->kapasitas == 0) return '0%';
                                        $count = $record->kelasSiswa()->where('status', 'aktif')->count();
                                        $persentase = ($count / $record->kapasitas) * 100;
                                        return number_format($persentase, 1) . '%';
                                    }),
                            ]),
                    ])
                    ->visible(fn(?Kelas $record) => $record !== null)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_kelas')
                    ->label('Nama Kelas')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-o-academic-cap'),

                Tables\Columns\TextColumn::make('guru.nama_guru')
                    ->label('Wali Kelas')
                    ->searchable()
                    ->sortable()
                    ->description(fn(Kelas $record): string =>
                        $record->guru?->nip ? "NIP: {$record->guru->nip}" : "Belum ada NIP"
                    )
                    ->wrap(),

                Tables\Columns\TextColumn::make('periode.nama_periode')
                    ->label('Periode')
                    ->badge()
                    ->color('success')
                    ->description(fn(Kelas $record): string =>
                        "Tahun: {$record->periode?->tahun_ajaran}"
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('kapasitas')
                    ->label('Kapasitas')
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn($state) => "{$state} siswa")
                    ->sortable(),

            Tables\Columns\TextColumn::make('siswa_aktif_count')
                ->label('Siswa Aktif')
                ->alignCenter()
                ->badge()
                ->color('warning')
                ->formatStateUsing(fn($state, $record) => "{$state} siswa")
                ->getStateUsing(function ($record) {
                    try {
                        return $record->kelasSiswa()->where('status', 'aktif')->count();
                    } catch (\Exception $e) {
                        return 0;
                    }
                })
                ->sortable(false),


            Tables\Columns\TextColumn::make('sisa_kapasitas')
                    ->label('Sisa Kapasitas')
                    ->alignCenter()
                    ->badge()
                    ->color(function (Kelas $record): string {
                        $count = $record->kelasSiswa()->where('status', 'aktif')->count();
                        $sisa = $record->kapasitas - $count;

                        return match (true) {
                            $sisa <= 0 => 'danger',
                            $sisa <= 3 => 'warning',
                            $sisa <= 5 => 'info',
                            default => 'success',
                        };
                    })
                    ->formatStateUsing(function (Kelas $record): string {
                        $count = $record->kelasSiswa()->where('status', 'aktif')->count();
                        $sisa = $record->kapasitas - $count;
                        return "{$sisa} siswa";
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'aktif' => 'success',
                        'tidak_aktif' => 'warning',
                        'selesai' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('guru_id')
                    ->label('Wali Kelas')
                    ->relationship('guru', 'nama_guru')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('periode_id')
                    ->label('Periode')
                    ->relationship('periode', 'nama_periode')
                    ->default(fn() => Periode::where('is_active', true)->value('id')),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Kelas')
                    ->options([
                        'aktif' => 'Aktif',
                        'tidak_aktif' => 'Tidak Aktif',
                        'selesai' => 'Selesai',
                    ])
                    ->default('aktif'),

                Tables\Filters\Filter::make('kapasitas_penuh')
                    ->label('Kapasitas Penuh')
                    ->query(fn(Builder $query): Builder =>
                        $query->whereRaw('(SELECT COUNT(*) FROM kelas_siswa WHERE kelas_id = kelas.id AND status = "aktif") >= kapasitas')
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('hampir_penuh')
                    ->label('Hampir Penuh (≥90%)')
                    ->query(fn(Builder $query): Builder =>
                        $query->whereRaw('(SELECT COUNT(*) FROM kelas_siswa WHERE kelas_id = kelas.id AND status = "aktif") >= (kapasitas * 0.9)')
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('masih_kosong')
                    ->label('Masih Kosong')
                    ->query(fn(Builder $query): Builder =>
                        $query->whereRaw('(SELECT COUNT(*) FROM kelas_siswa WHERE kelas_id = kelas.id AND status = "aktif") = 0')
                    )
                    ->toggle(),
            ])
            ->actions([
                ActionGroup::make([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('manage_students')
                    ->label('Kelola Siswa')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->url(
                        fn(Kelas $record): string =>
                        route('filament.admin.resources.kelas-siswas.index', [
                            'kelas' => $record->id
                        ])
                        ),

                Tables\Actions\Action::make('view_assessments')
                    ->label('Lihat Penilaian')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->url(
                        fn($record): string =>
                        route('filament.admin.resources.guru-nilais.index', [
                            'tableFilters[kelas_id][value]' => $record->id
                        ])
                        ),

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Kelas')
                    ->modalDescription('Apakah Anda yakin ingin menghapus kelas ini?')
                    ->modalSubmitActionLabel('Ya, Hapus'),
                ])
                ->label('Aksi')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('update_kapasitas')
                        ->label('Update Kapasitas')
                        ->icon('heroicon-o-users')
                        ->color('info')
                        ->form([
                            Forms\Components\TextInput::make('kapasitas')
                                ->label('Kapasitas Baru')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->maxValue(50)
                                ->default(30),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(fn($record) => $record->update(['kapasitas' => $data['kapasitas']]));

                            \Filament\Notifications\Notification::make()
                                ->title('Berhasil')
                                ->body(count($records) . ' kelas berhasil diupdate kapasitasnya')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKelas::route('/'),
            'create' => Pages\CreateKelas::route('/create'),
            'edit' => Pages\EditKelas::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        if (!Auth::user()?->isAdmin()) {
            return null;
        }

        return static::getModel()::where('status', 'aktif')->count();
    }
}
