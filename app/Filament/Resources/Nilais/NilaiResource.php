<?php

namespace App\Filament\Resources\Nilais;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Exception;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Nilais\Pages\ListNilais;
use App\Filament\Resources\Nilais\Pages\CreateNilai;
use App\Filament\Resources\Nilais\Pages\ViewNilai;
use App\Filament\Resources\Nilais\Pages\EditNilai;
use Filament\Forms;
use App\Models\Guru;
use Filament\Tables;
use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\Siswa;
use App\Models\Periode;
use App\Models\KelasSiswa;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\NilaiResource\Pages;
use App\Filament\Resources\Nilais\Pages\NilaiStats;

class NilaiResource extends Resource
{
    protected static ?string $model = Nilai::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Penilaian Siswa';
    protected static ?string $modelLabel = 'Penilaian';
    protected static ?string $pluralModelLabel = 'Penilaian Siswa';
    protected static string | \UnitEnum | null $navigationGroup = 'Akademik';
    protected static ?int $navigationSort = 5;
    protected function getHeaderWidgets(): array
    {
        return [
            NilaiStats::class
        ];
    }

    // Show navigation only for admin
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    // Control access - allow both admin and guru to access
    public static function canAccess(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();
        return $user->role === 'admin' || $user->role === 'guru';
    }

    public static function form(Schema $schema): Schema
    {
        $guru = Auth::user()?->guru;
        return $schema
            ->components([
                Section::make('Informasi Penilaian')
                    ->description('Pilih kelas, siswa, dan guru untuk penilaian')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                Grid::make(2)
                            ->schema([
                    Select::make('kelas_id')
                                    ->label('Kelas')
                                    ->relationship('kelas', 'nama_kelas')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                        ->afterStateUpdated(function (Set $set) {
                                        $set('siswa_id', null);
                                    }),

                    Select::make('siswa_id')
                                    ->label('Siswa')
                        ->options(function (Get $get) {
                                        $kelasId = $get('kelas_id');
                                        if (!$kelasId) return [];

                                        return KelasSiswa::where('kelas_id', $kelasId)
                                            ->where('status', 'aktif')
                                            ->with('siswa')
                                            ->get()
                            ->filter(fn($kelasSiswa) => $kelasSiswa->siswa) // pastikan siswa ada
                            ->mapWithKeys(function ($kelasSiswa) {
                                                $siswa = $kelasSiswa->siswa;
                                return [
                                    $siswa->id => $siswa->nama_lengkap . ' (' . ($siswa->nis ?? 'N/A') . ')'
                                ];
                            })
                            ->toArray();
                                    })
                                    ->required()
                                    ->searchable()
                        ->searchable()
                        ->disabled(fn(Get $get) => empty($get('kelas_id'))),

                    Select::make('guru_id')
                                    ->label('Guru')
                        ->default($guru?->id),

                    Select::make('periode_id')
                                    ->label('Periode')
                                    ->relationship('periode', 'nama_periode')
                                    ->required()
                                    ->default(fn() => Periode::where('is_active', true)->value('id')),
                            ]),
                    ]),

            Section::make('Penilaian')
                    ->description('Masukkan penilaian untuk setiap aspek')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                Textarea::make('nilai_agama')
                            ->label('Nilai Agama dan Budi Pekerti')
                            ->rows(4)
                            ->placeholder('Masukkan penilaian untuk aspek agama dan budi pekerti')
                            ->columnSpanFull(),

                Textarea::make('nilai_jatiDiri')
                            ->label('Nilai Jati Diri')
                            ->rows(4)
                            ->placeholder('Masukkan penilaian untuk aspek jati diri')
                            ->columnSpanFull(),

                Textarea::make('nilai_literasi')
                            ->label('Nilai Dasar-Dasar Literasi, Matematika, Sains, Rekayasa, Teknologi, dan Seni')
                            ->rows(4)
                            ->placeholder('Masukkan penilaian untuk aspek literasi dan STEAM')
                            ->columnSpanFull(),

                // Forms\Components\Textarea::make('nilai_narasi')
                //     ->label('Narasi Pembelajaran')
                //     ->rows(4)
                //     ->placeholder('Masukkan narasi pembelajaran siswa')
                //     ->columnSpanFull(),

                Textarea::make('refleksi_guru')
                            ->label('Refleksi Guru')
                            ->rows(4)
                            ->placeholder('Masukkan refleksi guru tentang perkembangan siswa')
                            ->columnSpanFull(),
                    ]),

            Section::make('Dokumentasi')
                    ->description('Upload foto dokumentasi untuk setiap aspek penilaian')
                    ->icon('heroicon-o-camera')
                    ->schema([
                FileUpload::make('fotoAgama')
                            ->label('Foto Agama dan Budi Pekerti')
                            ->image()
                            ->multiple()
                            ->directory('nilai/agama')
                            ->maxFiles(5)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048)
                            ->helperText('Maksimal 5 foto, ukuran maksimal 2MB per foto'),

                FileUpload::make('fotoJatiDiri')
                            ->label('Foto Jati Diri')
                            ->image()
                            ->multiple()
                            ->directory('nilai/jati-diri')
                            ->maxFiles(5)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048)
                            ->helperText('Maksimal 5 foto, ukuran maksimal 2MB per foto'),

                FileUpload::make('fotoLiterasi')
                            ->label('Foto Literasi dan STEAM')
                            ->image()
                            ->multiple()
                            ->directory('nilai/literasi')
                            ->maxFiles(5)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048)
                            ->helperText('Maksimal 5 foto, ukuran maksimal 2MB per foto'),

                // Forms\Components\FileUpload::make('fotoNarasi')
                //     ->label('Foto Narasi Pembelajaran')
                //     ->image()
                //     ->multiple()
                //     ->directory('nilai/narasi')
                //     ->maxFiles(5)
                //     ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                //     ->maxSize(2048)
                //     ->helperText('Maksimal 5 foto, ukuran maksimal 2MB per foto'),
            ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            TextColumn::make('siswa.nama_lengkap')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => 'NIS: ' . ($record->siswa?->nis ?? 'N/A'))
                    ->weight('bold'),

            TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->badge()
                    ->color('info')
                    ->sortable(),

            TextColumn::make('guru.nama_guru')
                    ->label('Guru')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->guru?->nip ? 'NIP: ' . $record->guru->nip : 'Belum ada NIP'),

            TextColumn::make('periode.nama_periode')
                    ->label('Periode')
                    ->badge()
                    ->color('success')
                    ->sortable(),

            TextColumn::make('nilai_icons')
                ->label('Status Nilai')
                ->getStateUsing(function ($record) {
                    $checks = [
                        'Agama' => !empty($record->nilai_agama),
                        'Jati Diri' => !empty($record->nilai_jatiDiri),
                        'Literasi' => !empty($record->nilai_literasi),
                        'Narasi' => !empty($record->nilai_narasi),
                        'Refleksi' => !empty($record->refleksi_guru),
                    ];

                $result = [];
                foreach ($checks as $label => $status) {
                    $icon = $status ? '✅' : '❌';
                    $result[] = "{$label}: {$icon}";
                }

                    return implode(' | ', $result);
                })
                ->html()
                ->wrap()
                ->tooltip('Jika Ada Tanda Silang Mohon dilengkapi dulu')
                ->sortable(),


            TextColumn::make('status_check')
                    ->label('Status Data')
                ->tooltip('Jika Ada Tanda Silang Mohon dilengkapi dulu')
                    ->getStateUsing(function ($record) {
                        try {
                            $checks = [
                                'Absensi' => DB::table('absensis')
                                    ->where('siswa_id', $record->siswa_id)
                                    ->where('periode_id', $record->periode_id)
                                    ->exists(),

                                'Medis' => DB::table('data_medis_siswas')
                                    ->where('siswa_id', $record->siswa_id)
                                    ->where('periode_id', $record->periode_id)
                                    ->exists(),

                            ];

                            $statusParts = [];
                            foreach ($checks as $label => $exists) {
                                $icon = $exists ? '✅' : '❌';
                                $statusParts[] = "{$label}: {$icon}";
                            }

                    return implode(' | ', $statusParts);
                } catch (Exception $e) {
                            return 'Error: ' . $e->getMessage();
                        }
                    })
                    ->html()
                    ->wrap()
                    ->size('sm')
                    ->sortable(),
            TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->description(fn($record) => $record->created_at->diffForHumans())
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
            SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->relationship('kelas', 'nama_kelas')
                    ->searchable()
                    ->preload(),

            SelectFilter::make('guru_id')
                    ->label('Guru')
                    ->relationship('guru', 'nama_guru')
                    ->searchable()
                    ->preload(),

            SelectFilter::make('periode_id')
                    ->label('Periode')
                    ->relationship('periode', 'nama_periode')
                    ->default(fn() => Periode::where('is_active', true)->value('id')),
            ])
            ->recordActions([

                ActionGroup::make([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),

                Action::make('print')
                    ->label('Print Rapor')
                    ->tooltip('Print Rapor')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn($record): string => route('nilai.print', $record))
                    ->openUrlInNewTab(),
                // Tables\Actions\DeleteAction::make()
                //     ->requiresConfirmation()
                //     ->modalHeading('Hapus Penilaian')
                //     ->modalDescription('Apakah Anda yakin ingin menghapus penilaian ini?')
                //     ->modalSubmitActionLabel('Ya, Hapus'),
            ])->label('Aksi')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->tooltip('Aksi')
                ->button(),
            ])
            ->headerActions([
            Action::make('export_all')
                ->label('Export Semua Data')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->action(function () {
                // Implement export functionality
                Notification::make()
                        ->title('Export Data')
                        ->body('Fitur export akan segera tersedia')
                        ->info()
                        ->send();
                }),
            Action::make('refresh')
                    ->label('Refresh')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNilais::route('/'),
            'create' => CreateNilai::route('/create'),
            'view' => ViewNilai::route('/{record}'),
            'edit' => EditNilai::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        if (!Auth::check() || !method_exists(Auth::user(), 'isAdmin') || !Auth::user()->isAdmin()) {
            return null;
        }

        return static::getModel()::count();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Auth::check() && Auth::user()->role === 'guru') {
            $guru = Auth::user()->guru;
            if ($guru) {
                // Guru can only see their own records
                $query->where('guru_id', $guru->id);
            } else {
                // If guru record not found, show nothing
                $query->whereRaw('1 = 0');
            }
        }

        return $query->with(['absensi', 'datamedis', 'signature_dates']);
    }
}
