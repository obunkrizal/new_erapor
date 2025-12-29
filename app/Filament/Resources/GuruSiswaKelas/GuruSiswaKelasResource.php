<?php

namespace App\Filament\Resources\GuruSiswaKelas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use App\Filament\Resources\GuruSiswaKelas\Pages\ListGuruSiswaKelas;
use App\Filament\Resources\GuruSiswaKelas\Pages\CreateGuruSiswaKelas;
use App\Filament\Resources\GuruSiswaKelas\Pages\EditGuruSiswaKelas;
use Exception;
use App\Models\User;
use Filament\Forms;
use Filament\Tables;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Periode;
use App\Models\KelasSiswa;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\GuruSiswaKelasResource\Pages;
use Filament\Actions\ActionGroup;

class GuruSiswaKelasResource extends Resource
{
    protected static ?string $model = KelasSiswa::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Siswa Kelas';

    protected static ?string $modelLabel = 'Siswa Kelas';

    protected static ?string $pluralModelLabel = 'Siswa Kelas';

    protected static string | \UnitEnum | null $navigationGroup = 'Guru';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = false; // Hide from main navigation

    public static function shouldRegisterNavigation(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user?->isGuru() ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        /** @var User|null $user */
        $user = Auth::user();
        $query = parent::getEloquentQuery();

        if (!$user || !$user->isGuru()) {
            return $query->whereRaw('1 = 0');
        }

        $guru = $user->guru;
        if (!$guru) {
            return $query->whereRaw('1 = 0');
        }

        // Filter by kelas parameter if provided
        $kelasId = request()->get('kelas');
        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        // Only show students from classes taught by this guru
        return $query->whereHas('kelas', function (Builder $query) use ($guru) {
            $query->where('guru_id', $guru->id);
        })->with(['siswa', 'kelas']);
    }

    // public static function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             Forms\Components\Section::make('Informasi Siswa Kelas')
    //                 ->schema([
    //                     Select::make('siswa_id')
    //                         ->label('Siswa')
    //                         ->relationship('siswa', 'nama_lengkap')
    //                         ->searchable()
    //                         ->preload()
    //                         ->required()
    //                         ->createOptionForm([
    //                             Forms\Components\TextInput::make('nama_lengkap')
    //                                 ->required()
    //                                 ->maxLength(255),
    //                             Forms\Components\TextInput::make('nis')
    //                                 ->required()
    //                                 ->unique('siswas', 'nis')
    //                                 ->maxLength(20),
    //                             Forms\Components\TextInput::make('nisn')
    //                                 ->required()
    //                                 ->unique('siswas', 'nisn')
    //                                 ->maxLength(20),
    //                         ]),

    //                     Select::make('kelas_id')
    //                         ->label('Kelas')
    //                         ->relationship('kelas', 'nama_kelas', function (Builder $query) {
    //                             $user = Auth::user();
    //                             if ($user && $user->isGuru() && $user->guru) {
    //                                 $query->where('guru_id', $user->guru->id);
    //                             }
    //                         })
    //                         ->required()
    //                         ->default(fn () => request()->get('kelas')),

    //                     Select::make('status')
    //                         ->label('Status')
    //                         ->options([
    //                             'aktif' => 'Aktif',
    //                             'tidak_aktif' => 'Tidak Aktif',
    //                             'pindah' => 'Pindah',
    //                             'lulus' => 'Lulus',
    //                         ])
    //                         ->default('aktif')
    //                         ->required(),

    //                     Forms\Components\DatePicker::make('tanggal_masuk')
    //                         ->label('Tanggal Masuk')
    //                         ->default(now())
    //                         ->required(),

    //                     Forms\Components\DatePicker::make('tanggal_keluar')
    //                         ->label('Tanggal Keluar')
    //                         ->visible(fn (Forms\Get $get): bool =>
    //                             in_array($get('status'), ['tidak_aktif', 'pindah', 'lulus'])),

    //                     Forms\Components\Textarea::make('keterangan')
    //                         ->label('Keterangan')
    //                         ->maxLength(500)
    //                         ->columnSpanFull(),
    //                 ])
    //                 ->columns(2),
    //         ]);
    // }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Periode & Kelas')
                    ->description('Pilih periode dan kelas untuk mengelola siswa')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        Select::make('periode_id')
                            ->label('Periode')
                            ->options(self::getPeriodeOptions())
                            ->default(self::getActivePeriodeId())
                            ->afterStateUpdated(function (Set $set, ?Model $record) {
                                // Don't reset in edit mode
                                if (!$record) {
                                    $set('kelas_id', null);
                                    $set('siswa_ids', null);
                                    $set('siswa_id', null);
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->disabled(fn(?Model $record) => $record !== null)
                            ->helperText('Pilih periode akademik yang aktif'),

                        Select::make('kelas_id')
                            ->label('Kelas')
                            ->options(function (Get $get, ?Model $record) {
                                $periodeId = $get('periode_id') ?? $record?->periode_id;
                                return self::getKelasOptions($periodeId);
                            })
                            ->afterStateUpdated(function (Set $set, ?Model $record) {
                                // Don't reset in edit mode
                                if (!$record) {
                                    $set('siswa_ids', null);
                                    $set('siswa_id', null);
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->disabled(function (Get $get, ?Model $record) {
                                // Disable in edit mode
                                // if ($record !== null) {
                                //     return true;
                                // }

                                // Disable if no periode selected
                                $periodeId = $get('periode_id');
                                return empty($periodeId);
                            })
                            ->helperText('Pilih kelas berdasarkan periode yang dipilih'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Pilih Siswa')
                    ->description(function (?Model $record) {
                        return $record
                            ? 'Informasi siswa yang terdaftar dalam kelas'
                            : 'Pilih siswa yang akan dimasukkan ke dalam kelas';
                    })
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        // Show current student info in edit/view mode
                        Placeholder::make('current_student_info')
                            ->label('Siswa Terdaftar')
                            ->content(function (?Model $record): string {
                                if (!$record || !$record->siswa) {
                                    return 'Data siswa tidak ditemukan';
                                }

                                $siswa = $record->siswa;
                                $name = $siswa->nama_lengkap ?? $siswa->nama_lengkap ?? 'N/A';
                                $nis = $siswa->nis ?? 'N/A';

                                return "**{$name}**  \nNIS: {$nis}";
                            })
                            ->visible(fn(?Model $record) => $record !== null)
                            ->columnSpanFull(),

                        // Toggle for selection mode (only in create mode)
                        Toggle::make('multiple_selection')
                            ->label('Pilih Multiple Siswa')
                            ->default(true)
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('siswa_ids', null);
                                $set('siswa_id', null);
                            })
                            ->visible(fn(?Model $record) => $record === null)
                            ->helperText('Aktifkan untuk memilih lebih dari satu siswa sekaligus'),

                        // Multiple selection (only in create mode)
                        Select::make('siswa_ids')
                            ->label('Pilih Siswa (Multiple)')
                            ->options(function (Get $get, ?Model $record) {
                                if ($record) return [];
                                return self::getAvailableSiswaOptions($get('kelas_id'), $get('periode_id'));
                            })
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required(
                                fn(Get $get, ?Model $record): bool =>
                                $record === null && $get('multiple_selection')
                            )
                            ->visible(
                                fn(Get $get, ?Model $record): bool =>
                                $record === null && $get('multiple_selection')
                            )
                            ->disabled(fn(Get $get): bool => !$get('kelas_id'))
                            ->helperText('Pilih satu atau lebih siswa yang belum memiliki kelas aktif')
                            ->live()
                            ->getSearchResultsUsing(function (string $search, Get $get) {
                                if (!$get('kelas_id')) {
                                    return [];
                                }
                                return self::searchAvailableSiswa($search, $get('kelas_id'), $get('periode_id'));
                            })
                            ->columnSpanFull(),

                        // Single selection
                        Select::make('siswa_id')
                            ->label('Pilih Siswa')
                            ->options(function (Get $get, ?Model $record) {
                                // In edit mode, include current student + available students
                                if ($record) {
                                    $options = [];
                                    // Add current student
                                    if ($record->siswa) {
                                        $name = $record->siswa->nama_lengkap ?? $record->siswa->name ?? 'N/A';
                                        $nis = $record->siswa->nis ?? 'N/A';
                                        $options[$record->siswa_id] = $name . ' (NIS: ' . $nis . ')';
                                    }
                                    // Add available students
                                    $available = self::getAvailableSiswaOptions($get('kelas_id') ?? $record->kelas_id, $get('periode_id') ?? $record->periode_id);
                                    return array_merge($options, $available);
                                }

                                return self::getAvailableSiswaOptions($get('kelas_id'), $get('periode_id'));
                            })
                            ->searchable()
                            ->preload()
                            ->required(
                                fn(Get $get, ?Model $record): bool =>
                                $record !== null || ($record === null && !$get('multiple_selection'))
                            )
                            ->visible(
                                fn(Get $get, ?Model $record): bool =>
                                $record !== null || ($record === null && !$get('multiple_selection'))
                            )
                            ->disabled(
                                fn(Get $get, ?Model $record): bool =>
                                $record === null && !$get('kelas_id')
                            )
                            ->rules([
                                'required',
                                'integer',
                                'min:1',
                                'exists:siswas,id'
                            ])
                            ->validationMessages([
                                'required' => 'Siswa harus dipilih.',
                                'integer' => 'Siswa ID harus berupa angka.',
                                'min' => 'Siswa ID tidak valid.',
                                'exists' => 'Siswa yang dipilih tidak ditemukan.',
                            ])
                            ->helperText(function (?Model $record) {
                                return $record
                                    ? 'Pilih siswa lain untuk memindahkan ke kelas ini'
                                    : 'Pilih satu siswa yang belum memiliki kelas aktif';
                            })
                            ->live()
                            ->getSearchResultsUsing(function (string $search, Get $get, ?Model $record) {
                                $kelasId = $get('kelas_id') ?? $record?->kelas_id;
                                $periodeId = $get('periode_id') ?? $record?->periode_id;

                                if (!$kelasId) {
                                    return [];
                                }
                                return self::searchAvailableSiswa($search, $kelasId, $periodeId, $record);
                            }),

                        // Display selected students count
                        Placeholder::make('selected_count')
                            ->label('Jumlah Siswa Dipilih')
                            ->content(function (Get $get, ?Model $record) {
                                if ($record) {
                                    return '1 siswa terdaftar';
                                }

                                if ($get('multiple_selection')) {
                                    $siswaIds = $get('siswa_ids');
                                    if (is_array($siswaIds)) {
                                        $count = count($siswaIds);
                                        return $count . ' siswa dipilih';
                                    }
                                } else {
                                    if ($get('siswa_id')) {
                                        return '1 siswa dipilih';
                                    }
                                }
                                return '0 siswa dipilih';
                            })
                            ->visible(
                                fn(Get $get, ?Model $record): bool =>
                                $record !== null ||
                                    ($get('multiple_selection') && !empty($get('siswa_ids'))) ||
                                    (!$get('multiple_selection') && !empty($get('siswa_id')))
                            )
                            ->columnSpanFull(),
                    ]),

                Section::make('Informasi Tambahan')
                    ->description('Informasi status dan tanggal masuk siswa')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'aktif' => 'Aktif',
                                'pindah' => 'Pindah',
                                'lulus' => 'Lulus',
                                'keluar' => 'Keluar',
                            ])
                            ->default('aktif')
                            ->required()
                            ->native(false)
                            ->helperText(function (?Model $record) {
                                return $record
                                    ? 'Status siswa dalam kelas ini'
                                    : 'Status ini akan diterapkan untuk semua siswa yang dipilih';
                            }),

                        DatePicker::make('tanggal_masuk')
                            ->label('Tanggal Masuk')
                            ->default(now())
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->maxDate(now())
                            ->helperText(function (?Model $record) {
                                return $record
                                    ? 'Tanggal masuk siswa ke kelas ini'
                                    : 'Tanggal masuk untuk semua siswa yang dipilih';
                            }),

                        Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->placeholder('Catatan tambahan (opsional)')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText(function (?Model $record) {
                                return $record
                                    ? 'Keterangan untuk siswa ini'
                                    : 'Keterangan ini akan diterapkan untuk semua siswa yang dipilih';
                            }),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('siswa.foto')
                    ->label('Foto')
                    ->circular()
                    ->size(50)
                    ->defaultImageUrl(url('/images/default-avatar.png')),

                TextColumn::make('siswa.nama_lengkap')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn(KelasSiswa $record): string =>
                    "NIS: {$record->siswa->nis} | NISN: {$record->siswa->nisn}"),

                TextColumn::make('siswa.jenis_kelamin')
                    ->label('Gender')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'laki-laki', 'L' => 'blue',
                        'perempuan', 'P' => 'pink',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'laki-laki', 'L' => 'Laki-laki',
                        'perempuan', 'P' => 'Perempuan',
                        default => $state,
                    }),

                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'aktif' => 'success',
                        'tidak_aktif' => 'warning',
                        'pindah' => 'danger',
                        'lulus' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('tanggal_masuk')
                    ->label('Tanggal Masuk')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tanggal_keluar')
                    ->label('Tanggal Keluar')
                    ->date()
                    ->placeholder('Masih aktif')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->relationship('kelas', 'nama_kelas', function (Builder $query) {
                        $user = Auth::user();

                        if ($user && $user->guru) {
                            $query->where('guru_id', $user->guru->id);
                        }
                    }),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'aktif' => 'Aktif',
                        'tidak_aktif' => 'Tidak Aktif',
                        'pindah' => 'Pindah',
                        'lulus' => 'Lulus',
                    ])
                    ->default('aktif'),

                SelectFilter::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->relationship('siswa', 'jenis_kelamin')
                    ->options([
                        'laki-laki' => 'Laki-laki',
                        'perempuan' => 'Perempuan',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    // Tables\Actions\EditAction::make(),

                    Action::make('create_assessment')
                        ->label('Buat Penilaian')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->color('success')
                        ->url(
                            fn(KelasSiswa $record): string =>
                            route('filament.admin.resources.guru-nilais.create', [
                                'siswa_id' => $record->siswa_id,
                                'kelas_id' => $record->kelas_id,
                            ])
                        )
                        ->openUrlInNewTab()
                        ->visible(fn(KelasSiswa $record): bool => $record->status === 'aktif'),

            // Tables\Actions\DeleteAction::make()
            //     ->requiresConfirmation()
            //     ->modalHeading('Hapus Siswa dari Kelas')
            //     ->modalDescription('Apakah Anda yakin ingin menghapus siswa ini dari kelas?'),

            Action::make('print_cover')
                ->label('Print Cover')
                ->icon('heroicon-o-document-text')
                ->color('primary')
                ->url(fn(KelasSiswa $record): string => route('gurusiswakelas.print-cover', ['kelasSiswa' => $record->id]))
                ->openUrlInNewTab(),

            Action::make('print')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn(KelasSiswa $record): string => route('gurusiswakelas.print', ['kelasSiswa' => $record->id]))
                ->openUrlInNewTab(),
        ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),

                    BulkAction::make('change_status')
                        ->label('Ubah Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->form([
                            Select::make('status')
                                ->label('Status Baru')
                                ->options([
                                    'aktif' => 'Aktif',
                                    'tidak_aktif' => 'Tidak Aktif',
                                    'pindah' => 'Pindah',
                                    'lulus' => 'Lulus',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });

                            Notification::make()
                                ->title('Status berhasil diubah')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('siswa.nama_lengkap', 'asc')
            ->poll('30s');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGuruSiswaKelas::route('/'),
            'create' => CreateGuruSiswaKelas::route('/create'),
            'edit' => EditGuruSiswaKelas::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();

        if (!$user || !$user->isGuru() || !$user->guru) {
            return null;
        }

        return static::getModel()::whereHas('kelas', function (Builder $query) use ($user) {
            $query->where('guru_id', $user->guru->id);
        })->where('status', 'aktif')->count();
    }


    private static function getPeriodeOptions(): array
    {
        return Periode::query()
            ->orderBy('is_active', 'desc')
            ->orderBy('tahun_ajaran', 'desc')
            ->get()
            ->mapWithKeys(function ($periode) {
                $status = $periode->is_active ? ' (Aktif)' : '';
                return [
                    $periode->id => $periode->nama_periode . ' - ' . $periode->tahun_ajaran . $status
                ];
            })
            ->toArray();
    }

    private static function getActivePeriodeId(): ?int
    {
        return Periode::where('is_active', true)->value('id');
    }

    private static function getKelasOptions(?int $periodeId): array
    {
        if (!$periodeId) {
            return [];
        }

        try {
            return Kelas::where('periode_id', $periodeId)
                ->orderBy('nama_kelas')
                ->pluck('nama_kelas', 'id')
                ->toArray();
        } catch (Exception $e) {
            Log::error('Failed to load kelas options: ' . $e->getMessage());
            return [];
        }
    }


    private static function getAvailableSiswaOptions(?int $kelasId, ?int $periodeId, ?Model $currentRecord = null): array
    {
        if (!$kelasId || !$periodeId) {
            return [];
        }

        $query = Siswa::query()
            ->whereNotIn('id', function ($subQuery) use ($kelasId, $periodeId, $currentRecord) {
                $subQuery->select('siswa_id')
                    ->from('kelas_siswas')
                    ->where('kelas_id', $kelasId)
                    ->where('periode_id', $periodeId)
                    ->where('status', 'aktif');

                // Exclude current record's siswa_id in edit mode
                if ($currentRecord) {
                    $subQuery->where('siswa_id', '!=', $currentRecord->siswa_id);
                }
            })
            ->orderBy('nama_lengkap');

        return $query->get()
            ->mapWithKeys(function ($siswa) {
                $name = $siswa->nama_lengkap ?? $siswa->nama_lengkap ?? 'N/A';
                return [
                    $siswa->id => $name . ' (NIS: ' . ($siswa->nis ?? 'N/A') . ')'
                ];
            })
            ->toArray();
    }

    private static function searchAvailableSiswa(string $search, ?int $kelasId, ?int $periodeId, ?Model $currentRecord = null): array
    {
        if (!$kelasId || !$periodeId) {
            return [];
        }

        $query = Siswa::query()
            ->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            })
            ->whereNotIn('id', function ($subQuery) use ($kelasId, $periodeId, $currentRecord) {
                $subQuery->select('siswa_id')
                    ->from('kelas_siswas')
                    ->where('kelas_id', $kelasId)
                    ->where('periode_id', $periodeId)
                    ->where('status', 'aktif');

                if ($currentRecord) {
                    $subQuery->where('siswa_id', '!=', $currentRecord->siswa_id);
                }
            })
            ->orderBy('nama_lengkap')
            ->limit(50);

        return $query->get()
            ->mapWithKeys(function ($siswa) {
                $name = $siswa->nama_lengkap ?? $siswa->nama_lengkap ?? 'N/A';
                return [
                    $siswa->id => $name . ' (NIS: ' . ($siswa->nis ?? 'N/A') . ')'
                ];
            })
            ->toArray();
    }


    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['siswa', 'kelas', 'periode']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'siswa.nama_lengkap',
            'siswa.nis',
            'kelas.nama_kelas',
            'periode.nama_periode',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Kelas' => $record->kelas?->nama_kelas,
            'NIS' => $record->siswa?->nis,
            'Status' => $record->status,
        ];
    }
}
