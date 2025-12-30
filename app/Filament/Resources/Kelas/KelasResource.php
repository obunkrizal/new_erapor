<?php

namespace App\Filament\Resources\Kelas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Exception;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Kelas\Pages\ListKelas;
use App\Filament\Resources\Kelas\Pages\CreateKelas;
use App\Filament\Resources\Kelas\Pages\EditKelas;
use Filament\Forms;
use Filament\Tables;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\Periode;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\KelasResource\Pages;

class KelasResource extends Resource
{
    protected static ?string $model = Kelas::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Manajemen Kelas';
    protected static ?string $modelLabel = 'Kelas';
    protected static ?string $pluralModelLabel = 'Manajemen Kelas';
    protected static string | \UnitEnum | null $navigationGroup = 'Data Master';
    protected static ?int $navigationSort = 3;

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

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kelas Siswa')
                    ->description('Data Kelas')
                    ->schema([
                Grid::make(2)
                    ->schema([
                    TextInput::make('nama_kelas')
                                    ->label('Nama Kelas')
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder('Contoh: Kelas A, 1A, dll')
                                    ->unique(ignoreRecord: true),
                    Select::make('rentang_usia')
                        ->label('Rentang Usia')
                        ->options([
                            '2-3' => 'Playgroup',
                            '4-5' => 'PAUD A (4-5 Tahun)',
                            '5-6' => 'PAUD B (5-6 Tahun)',
                        ])
                        ->searchable()
                        ->required()
                        ->placeholder('Pilih rentang usia'),

                    TextInput::make('kapasitas')
                                    ->label('Kapasitas Kelas')
                                    ->required()
                                    ->numeric()
                                    ->default(30)
                                    ->minValue(1)
                                    ->maxValue(50)
                                    ->placeholder('Maksimal siswa dalam kelas')
                                    ->helperText('Jumlah maksimal siswa yang dapat diterima')
                                    ->suffixIcon('heroicon-m-users'),

                    Select::make('guru_id')
                                    ->label('Wali Kelas')
                                    ->relationship('guru', 'nama_guru')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->placeholder('Pilih wali kelas'),

                    Select::make('periode_id')
                                    ->label('Periode Akademik')
                                    ->relationship('periode', 'nama_periode')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->placeholder('Pilih periode akademik')
                                    ->default(fn() => Periode::where('is_active', true)->value('id')),

                    Select::make('status')
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
                ])
                ->columnSpan(2),

            Section::make('Statistik Kelas')
                    ->description('Informasi kapasitas dan siswa')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                Grid::make(3)
                            ->schema([
                    Placeholder::make('jumlah_siswa')
                                    ->label('Jumlah Siswa Aktif')
                                    ->content(function (?Kelas $record) {
                                        if (!$record) return '0 siswa';
                                        $count = $record->kelasSiswa()->where('status', 'aktif')->count();
                                        return "{$count} siswa";
                                    }),

                    Placeholder::make('sisa_kapasitas')
                                    ->label('Sisa Kapasitas')
                                    ->content(function (?Kelas $record) {
                                        if (!$record) return '-';
                                        $count = $record->kelasSiswa()->where('status', 'aktif')->count();
                                        $sisa = $record->kapasitas - $count;
                                        return "{$sisa} siswa";
                                    }),

                    Placeholder::make('persentase_kapasitas')
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
            TextColumn::make('nama_kelas')
                    ->label('Nama Kelas')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-o-academic-cap'),
            TextColumn::make('rentang_usia')
                ->label('Rentang Usia')
                ->formatStateUsing(fn(string $state): string => match ($state) {
                    '2-3' => 'Playgroup (2-3 Tahun)',
                    '4-5' => 'PAUD A (4-5 Tahun)',
                    '5-6' => 'PAUD B (5-6 Tahun)',
                    default => 'Tidak Diketahui',
                })
                ->searchable()
                ->sortable()
                ->weight('bold')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    '2-3' => 'warning',
                    '4-5' => 'success',
                    '5-6' => 'info',
                    default => 'gray',
                })
                ->icon('heroicon-o-adjustments-horizontal'),

            TextColumn::make('guru.nama_guru')
                    ->label('Wali Kelas')
                    ->searchable()
                    ->sortable()
                ->description(
                    fn(Kelas $record): string =>
                        $record->guru?->nip ? "NIP: {$record->guru->nip}" : "Belum ada NIP"
                    )
                    ->wrap(),

            TextColumn::make('periode.nama_periode')
                    ->label('Periode')
                    ->badge()
                    ->color('success')
                ->description(
                    fn(Kelas $record): string =>
                        "Tahun: {$record->periode?->tahun_ajaran}"
                    )
                    ->sortable(),

            TextColumn::make('kapasitas')
                    ->label('Kapasitas')
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn($state) => "{$state} siswa")
                    ->sortable(),

            TextColumn::make('siswa_aktif_count')
                ->label('Siswa Aktif')
                ->alignCenter()
                ->badge()
                ->color('warning')
                ->formatStateUsing(fn($state, $record) => "{$state} siswa")
                ->getStateUsing(function ($record) {
                    try {
                        return $record->kelasSiswa()->where('status', 'aktif')->count();
                } catch (Exception $e) {
                        return 0;
                    }
                })
                ->sortable(false),


            TextColumn::make('sisa_kapasitas')
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

            TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'aktif' => 'success',
                        'tidak_aktif' => 'warning',
                        'selesai' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

            TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            SelectFilter::make('guru_id')
                    ->label('Wali Kelas')
                    ->relationship('guru', 'nama_guru')
                    ->searchable()
                    ->preload(),

            SelectFilter::make('periode_id')
                    ->label('Periode')
                    ->relationship('periode', 'nama_periode')
                    ->default(fn() => Periode::where('is_active', true)->value('id')),

            SelectFilter::make('status')
                    ->label('Status Kelas')
                    ->options([
                        'aktif' => 'Aktif',
                        'tidak_aktif' => 'Tidak Aktif',
                        'selesai' => 'Selesai',
                    ])
                    ->default('aktif'),

            Filter::make('kapasitas_penuh')
                    ->label('Kapasitas Penuh')
                ->query(
                    fn(Builder $query): Builder =>
                        $query->whereRaw('(SELECT COUNT(*) FROM kelas_siswas WHERE kelas_id = kelas.id AND status = "aktif") >= kapasitas')
                    )
                    ->toggle(),

            Filter::make('hampir_penuh')
                    ->label('Hampir Penuh (≥90%)')
                ->query(
                    fn(Builder $query): Builder =>
                        $query->whereRaw('(SELECT COUNT(*) FROM kelas_siswas WHERE kelas_id = kelas.id AND status = "aktif") >= (kapasitas * 0.9)')
                    )
                    ->toggle(),

            Filter::make('masih_kosong')
                    ->label('Masih Kosong')
                ->query(
                    fn(Builder $query): Builder =>
                        $query->whereRaw('(SELECT COUNT(*) FROM kelas_siswas WHERE kelas_id = kelas.id AND status = "aktif") = 0')
                    )
                    ->toggle(),
            ])
            ->recordActions([
                ActionGroup::make([
                ViewAction::make(),
                EditAction::make(),

                Action::make('manage_students')
                    ->label('Kelola Siswa')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->url(
                        fn(Kelas $record): string =>
                        route('filament.admin.resources.kelas-siswas.index', [
                            'kelas' => $record->id
                        ])
                        ),

                Action::make('view_assessments')
                    ->label('Lihat Penilaian')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->url(
                        fn($record): string =>
                        route('filament.admin.resources.guru-nilais.index', [
                            'tableFilters[kelas_id][value]' => $record->id
                        ])
                        ),

                DeleteAction::make()
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
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('update_kapasitas')
                        ->label('Update Kapasitas')
                        ->icon('heroicon-o-users')
                        ->color('info')
                        ->form([
                    TextInput::make('kapasitas')
                                ->label('Kapasitas Baru')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->maxValue(50)
                                ->default(30),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(fn($record) => $record->update(['kapasitas' => $data['kapasitas']]));

                    Notification::make()
                                ->title('Berhasil')
                                ->body(count($records) . ' kelas berhasil diupdate kapasitasnya')
                                ->success()
                                ->send();
                        }),

                DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKelas::route('/'),
            'create' => CreateKelas::route('/create'),
            'edit' => EditKelas::route('/{record}/edit'),
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
