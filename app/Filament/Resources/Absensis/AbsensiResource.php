<?php

namespace App\Filament\Resources\Absensis;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Exception;
use Carbon\Carbon;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Absensis\Pages\ListAbsensis;
use App\Filament\Resources\Absensis\Pages\CreateAbsensi;
use App\Filament\Resources\Absensis\Pages\EditAbsensi;
use Closure;
use Filament\Forms;
use Filament\Tables;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Absensi;
use App\Models\Periode;
use App\Models\KelasSiswa;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use App\Filament\Resources\AbsensiResource\Pages;

class AbsensiResource extends Resource
{
    protected static ?string $model = Absensi::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Manajemen Absensi';

    protected static ?string $modelLabel = 'Absensi';

    protected static ?string $pluralModelLabel = 'Data Absensi';

    protected static string | \UnitEnum | null $navigationGroup = 'Data Master';

    protected static ?int $navigationSort = 3;

    // Hide navigation from guru - only show for admin
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()->isGuru();
    }

    // Control access - only admin can access
    public static function canAccess(): bool
    {
        return Auth::check() && Auth::user()->isGuru();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Hidden field to store record ID for edit mode
                Hidden::make('record_id')
                    ->default(fn(?Model $record) => $record?->id),

                self::createPeriodeSection(),
                self::createInformasiAbsensiSection(),
                self::createInformasiDetailSection(),
                self::createDataKetidakhadiranSection(),
                self::createCatatanSection(),
            ])
            ->columns(3);
    }

    private static function createPeriodeSection(): Section
    {
        return Section::make('Periode dan Kelas')
            ->description('Pilih periode akademik dan kelas untuk memfilter siswa')
            ->schema([
                Select::make('periode_id')
                    ->label('Periode Akademik')
                    ->options(self::getPeriodeOptions())
                    ->searchable()
                    ->required()
                    ->placeholder('Pilih Periode Akademik')
                    ->helperText('Pilih periode akademik yang aktif')
                    ->default(self::getActivePeriodeId())
                    ->disabled(function () {
                        // Disable in edit mode
                        return request()->routeIs('filament.admin.resources.absensis.edit');
                    })
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $set('kelas_id', null);
                        $set('siswa_id', null);
                    }),

                Select::make('kelas_id')
                    ->label('Kelas')
                    ->options(function (Get $get) {
                        $periodeId = $get('periode_id');
                        $guruId = Auth::user()?->guru?->id;

                        if (!$periodeId || !$guruId) {
                            return [];
                        }

                        // Get kelas assigned to guru for periode
                        $kelasList = Kelas::where('periode_id', $periodeId)
                            ->where('guru_id', $guruId)
                            ->orderBy('nama_kelas')
                            ->pluck('nama_kelas', 'id')
                            ->toArray();

                        return $kelasList;
                    })
                    ->default(function (Get $get) {
                        $periodeId = $get('periode_id');
                        $guruId = Auth::user()?->guru?->id;

                        if (!$periodeId || !$guruId) {
                            return null;
                        }

                        $kelas = Kelas::where('periode_id', $periodeId)
                            ->where('guru_id', $guruId)
                            ->orderBy('nama_kelas')
                            ->first();

                        return $kelas?->id;
                    })
                    ->searchable()
                    ->required()
                    ->placeholder('Pilih Kelas')
                    ->helperText('Pilih kelas untuk memfilter siswa yang tersedia')
                    ->disabled(function (Get $get) {
                        $periodeId = $get('periode_id');
                        $guruId = Auth::user()?->guru?->id;

                        // Disable if periode not selected OR in edit mode OR guru has no kelas for periode
                        if (empty($periodeId) || request()->routeIs('filament.admin.resources.absensis.edit')) {
                            return true;
                        }

                        $kelasCount = Kelas::where('periode_id', $periodeId)
                            ->where('guru_id', $guruId)
                            ->count();

                        return $kelasCount === 0;
                    })
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $set('siswa_id', null);
                        if ($state) {
                            $set('tanggal', now()->format('Y-m-d'));
                        }
                    }),

                Select::make('siswa_id')
                    ->label('Siswa')
                    ->options(function (Get $get) {
                        $kelasId = $get('kelas_id');
                        $periodeId = $get('periode_id');

                        return ($kelasId && $periodeId)
                            ? self::getSiswaByKelasAndPeriodeOptions($kelasId, $periodeId)
                            : [];
                    })
                    ->searchable()
                    ->required()
                    ->placeholder(function (Get $get) {
                        if (!$get('periode_id')) return 'Pilih periode terlebih dahulu';
                        if (!$get('kelas_id')) return 'Pilih kelas terlebih dahulu';
                        return 'Pilih Siswa';
                    })
                    ->helperText('Siswa akan muncul setelah memilih periode dan kelas')
                    ->disabled(function (Get $get) {
                        // Disable if requirements not met OR in edit mode
                        return empty($get('kelas_id')) ||
                            empty($get('periode_id')) ||
                            request()->routeIs('filament.admin.resources.absensis.edit');
                    })
                    ->suffixIcon('heroicon-m-user')
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set) {
                        if ($state) {
                            $set('tanggal', now()->format('Y-m-d'));
                        }
                    }),
            ])->columns(3);
    }

    private static function createInformasiAbsensiSection(): Section
    {
        return Section::make('Informasi Absensi')
            ->description('Detail tanggal dan validasi absensi')
            ->schema([
                DatePicker::make('tanggal')
                    ->label('Tanggal Absensi')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->default(now())
                    ->helperText('Tanggal periode absensi ini berlaku')
                    ->disabled(function () {
                        // Disable tanggal field in edit mode
                        return request()->routeIs('filament.admin.resources.absensis.edit');
                    })
                    ->live(),

                Placeholder::make('validation_info')
                    ->label('Status Validasi')
                ->content(function (Get $get, ?Model $record): string {
                    if ($record !== null) {
                            return "ℹ️ **Mode Edit:** Siswa dan tanggal tidak dapat diubah";
                        }
                        return self::getValidationStatus($get);
                    })
                    ->columnSpan(2),
            ])
            ->columns(3)
            ->visible(fn(Get $get) => !empty($get('siswa_id')));
    }

    /**
     * Create informasi detail section
     */
    private static function createInformasiDetailSection(): Section
    {
        return Section::make('Informasi Detail')
            ->description('Informasi detail siswa dan kelas yang dipilih')
            ->schema([
                Placeholder::make('periode_info')
                    ->label('Informasi Periode')
                    ->content(fn(Get $get): string => self::getPeriodeInfo($get('periode_id'))),

                Placeholder::make('kelas_info')
                    ->label('Informasi Kelas')
                    ->content(fn(Get $get): string => self::getKelasInfo($get('kelas_id'))),

                Placeholder::make('siswa_info')
                    ->label('Informasi Siswa')
                    ->content(fn(Get $get): string => self::getSiswaInfo($get('siswa_id'))),
            ])
            ->columns(3)
            ->visible(fn(Get $get) => !empty($get('kelas_id')));
    }

    /**
     * Create data ketidakhadiran section
     */
    private static function createDataKetidakhadiranSection(): Section
    {
        return Section::make('Data Ketidakhadiran')
            ->description('Masukkan jumlah hari ketidakhadiran siswa berdasarkan kategori')
            ->schema([
                TextInput::make('sakit')
                    ->label('Sakit (Hari)')
                    ->numeric()
                    ->suffix(' hari')
                    ->placeholder('0')
                    ->helperText('Jumlah hari tidak hadir karena sakit')
                    ->rules(['integer', 'min:0', 'max:365'])
                    ->live(onBlur: true)
                    ->dehydrated(),

                TextInput::make('izin')
                    ->label('Izin (Hari)')
                    ->numeric()
                    ->suffix(' hari')
                    ->placeholder('0')
                    ->helperText('Jumlah hari tidak hadir dengan izin')
                    ->rules(['integer', 'min:0', 'max:365'])
                    ->live(onBlur: true)
                    ->dehydrated(),

                TextInput::make('tanpa_keterangan')
                    ->label('Tanpa Keterangan (Hari)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(365)
                    ->suffix(' hari')
                    ->placeholder('0')
                    ->helperText('Jumlah hari tidak hadir tanpa keterangan (Alpha)')
                    ->rules(['integer', 'min:0', 'max:365'])
                    ->live(onBlur: true)
                    ->dehydrated(),

                Placeholder::make('total_ketidakhadiran')
                    ->label('Total Ketidakhadiran')
                    ->content(fn(Get $get): string => self::calculateTotalAbsence($get)),
            ])
            ->columns(4);
    }

    /**
     * Create catatan section
     */
    private static function createCatatanSection(): Section
    {
        return Section::make('Catatan Tambahan')
            ->description('Informasi tambahan terkait absensi siswa')
            ->schema([
                Textarea::make('catatan')
                    ->label('Catatan')
                    ->rows(3)
                    ->placeholder('Masukkan catatan tambahan jika diperlukan')
                    ->helperText('Catatan khusus mengenai absensi siswa')
                    ->maxLength(1000)
                    ->disabled(fn(?Model $record): bool => $record !== null && $record->exists)
                    ->dehydrated(fn(?Model $record): bool => $record === null || !$record->exists),
            ]);
    }

    /**
     * Get active periode ID
     */
    private static function getActivePeriodeId(): ?int
    {
        try {
            return Periode::where('is_active', true)->value('id');
        } catch (Exception $e) {
            Log::error('Failed to get active periode: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get periode options
     */
    private static function getPeriodeOptions(): array
    {
        try {
            return Periode::query()
                ->orderByDesc('is_active')
                ->orderByDesc('tahun_ajaran')
                ->get()
                ->mapWithKeys(function ($periode) {
                    $status = $periode->is_active ? ' (Aktif)' : '';
                    return [$periode->id => $periode->tahun_ajaran . ' - ' . $periode->semester . $status];
                })
                ->toArray();
        } catch (Exception $e) {
            Log::error('Failed to load periode options: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get kelas options by periode
     */
    private static function getKelasByPeriodeOptions(int $periodeId): array
    {
        try {
            return KelasSiswa::query()
                ->where('periode_id', $periodeId)
                ->where('status', 'aktif')
                ->with('kelas')
                ->get()
                ->groupBy('kelas_id')
                ->map(function ($group) {
                    $kelas = $group->first()->kelas;
                    $siswaCount = $group->count();
                    return $kelas ? "{$kelas->nama_kelas} ({$siswaCount} siswa)" : 'Kelas Tidak Ditemukan';
                })
                ->toArray();
        } catch (Exception $e) {
            Log::error('Failed to load kelas by periode options: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get siswa options by kelas and periode
     */
    private static function getSiswaByKelasAndPeriodeOptions(int $kelasId, int $periodeId): array
    {
        try {
            return KelasSiswa::query()
                ->where('kelas_id', $kelasId)
                ->where('periode_id', $periodeId)
                ->where('status', 'aktif')
                ->with('siswa:id,nama_lengkap,nis,jenis_kelamin')
                ->get()
                ->filter(fn($kelasSiswa) => $kelasSiswa->siswa)
                ->mapWithKeys(function ($kelasSiswa) {
                    $siswa = $kelasSiswa->siswa;
                    $info = [];
                    if ($siswa->nis) $info[] = "NIS: {$siswa->nis}";
                    if ($siswa->jenis_kelamin) $info[] = $siswa->jenis_kelamin;

                    $additionalInfo = !empty($info) ? ' (' . implode(', ', $info) . ')' : '';
                    return [$siswa->id => $siswa->nama_lengkap . $additionalInfo];
                })
                ->toArray();
        } catch (Exception $e) {
            Log::error('Failed to load siswa by kelas and periode: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get validation status for form
     */
    private static function getValidationStatus(Get $get): string
    {
        $siswaId = $get('siswa_id');
        $tanggal = $get('tanggal');
        $periodeId = $get('periode_id');
        $kelasId = $get('kelas_id');
        $recordId = $get('record_id'); // Get current record ID

        if (!$siswaId || !$tanggal || !$periodeId || !$kelasId) {
            return 'Lengkapi semua data untuk validasi';
        }

        try {
            $query = Absensi::where('siswa_id', $siswaId)
                ->where('periode_id', $periodeId)
                ->where('kelas_id', $kelasId)
                ->whereDate('tanggal', $tanggal);

            // Exclude current record if editing
            if ($recordId) {
                $query->where('id', '!=', $recordId);
            }

            $existingRecord = $query->first();

            if ($existingRecord) {
                return "⚠️ **Peringatan:** Data absensi untuk siswa ini sudah ada pada tanggal {$tanggal}! Silakan periksa data atau edit data yang ada.";
            }

            return "✅ **Valid:** Data dapat disimpan";
        } catch (Exception $e) {
            Log::error('Validation check error: ' . $e->getMessage());
            return 'Error saat validasi';
        }
    }

    /**
     * Validate unique absence record
     */
    public static function validateUniqueAbsence(?int $siswaId, ?string $tanggal, ?int $periodeId, ?int $kelasId, ?int $recordId = null): bool|string
    {
        if (!$siswaId || !$periodeId || !$kelasId || !$tanggal) {
            return true;
        }

        try {
            $query = Absensi::where('siswa_id', $siswaId)
                ->where('periode_id', $periodeId)
                ->where('kelas_id', $kelasId)
                ->whereDate('tanggal', $tanggal);

            // Exclude current record if editing
            if ($recordId) {
                $query->where('id', '!=', $recordId);
            }

            if ($query->exists()) {
                return 'Data absensi untuk siswa ini pada periode, kelas, dan tanggal yang sama sudah ada.';
            }

            return true;
        } catch (Exception $e) {
            Log::error('Unique validation error: ' . $e->getMessage());
            return 'Error saat validasi duplikasi';
        }
    }

    /**
     * Get periode information
     */
    private static function getPeriodeInfo(?int $periodeId): string
    {
        if (!$periodeId) {
            return 'Pilih periode untuk melihat informasi';
        }

        try {
            $periode = Periode::find($periodeId);
            if (!$periode) {
                return 'Data periode tidak ditemukan';
            }

            $status = $periode->is_active ? '✅ Aktif' : '❌ Tidak Aktif';
            return sprintf(
                "**Tahun Ajaran:** %s\n**Semester:** %s\n**Status:** %s",
                $periode->tahun_ajaran,
                $periode->semester,
                $status
            );
        } catch (Exception $e) {
            Log::error('Error loading periode info: ' . $e->getMessage());
            return 'Error memuat informasi periode';
        }
    }

    /**
     * Get kelas information
     */
    private static function getKelasInfo(?int $kelasId): string
    {
        if (!$kelasId) {
            return 'Pilih kelas untuk melihat informasi';
        }

        try {
            $kelas = Kelas::find($kelasId);
            if (!$kelas) {
                return 'Data kelas tidak ditemukan';
            }

            $namaKelas = $kelas->nama_kelas;
            $totalSiswa = $kelas->jumlah_siswa_aktif;

            return "**Nama Kelas:** $namaKelas\n**Total Siswa:** $totalSiswa siswa";
        } catch (Exception $e) {
            Log::error('Error loading kelas info: ' . $e->getMessage());
            return 'Error memuat informasi kelas';
        }
    }

    /**
     * Get siswa information
     */
    private static function getSiswaInfo(?int $siswaId): string
    {
        if (!$siswaId) {
            return 'Pilih siswa untuk melihat informasi';
        }

        try {
            $siswa = Siswa::find($siswaId);
            if (!$siswa) {
                return 'Data siswa tidak ditemukan';
            }

            return sprintf(
                "**Nama:** %s\n**NIS:** %s\n**NISN:** %s\n**Jenis Kelamin:** %s",
                $siswa->nama_lengkap,
                $siswa->nis ?? '-',
                $siswa->nisn ?? '-',
                $siswa->jenis_kelamin ?? '-'
            );
        } catch (Exception $e) {
            Log::error('Error loading siswa info: ' . $e->getMessage());
            return 'Error memuat informasi siswa';
        }
    }

    /**
     * Calculate total absence with status
     */
    private static function calculateTotalAbsence(Get $get): string
    {
        $sakit = (int) ($get('sakit') ?? 0);
        $izin = (int) ($get('izin') ?? 0);
        $alpha = (int) ($get('tanpa_keterangan') ?? 0);
        $total = $sakit + $izin + $alpha;

        $status = match (true) {
            $total > 15 => '🔴 Sangat Tinggi',
            $total > 10 => '🟡 Tinggi',
            $total > 5 => '🟠 Sedang',
            $total > 0 => '🟢 Rendah',
            default => '✅ Tidak Ada'
        };

        return "**{$total} hari** - Status: {$status}";
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('periode.tahun_ajaran')
                    ->label('Periode')
                    ->badge()
                    ->color(
                        fn(?Model $record): string =>
                        $record?->periode?->is_active ? 'success' : 'gray'
                    )
                    ->formatStateUsing(function (?Model $record): string {
                        if (!$record?->periode) {
                            return 'Tidak ada periode';
                        }

                        $tahunAjaran = $record->periode->tahun_ajaran ?? 'N/A';
                        $semester = $record->periode->semester ?? '';
                        $status = $record->periode->is_active ? ' (Aktif)' : '';

                        return $tahunAjaran . ' - ' . $semester . $status;
                    })
                    ->icon(
                        fn(?Model $record): string =>
                        $record?->periode?->is_active ? 'heroicon-m-check-circle' : 'heroicon-m-clock'
                    )
                    ->searchable(['periode.tahun_ajaran', 'periode.semester'])
                    ->placeholder('Tidak ada periode'),

                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->placeholder('Tidak ada kelas')
                    ->searchable(),

                TextColumn::make('siswa.nama_lengkap')
                    ->label('Nama Siswa')
                    ->searchable(['siswa.nama_lengkap', 'siswa.nis'])
                    ->sortable()
                    ->wrap()
                    ->placeholder('Tidak ada siswa')
                    ->description(
                        fn(?Model $record): ?string =>
                        $record?->siswa?->nis ? "NIS: {$record->siswa->nis}" : null
                    ),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->description(
                        fn(?Model $record): string =>
                        !$record?->tanggal
                            ? ''
                            : 'Bulan ' . Carbon::parse($record->tanggal)->format('F Y')
                    ),

                TextColumn::make('sakit')
                    ->label('Sakit')
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    ->color(fn(?int $state): string => self::getAbsenceColor($state ?? 0))
                    ->formatStateUsing(fn(?int $state): string => ($state ?? 0) . ' hari'),

                TextColumn::make('izin')
                    ->label('Izin')
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    ->color(
                        fn(?int $state): string => ($state ?? 0) > 5 ? 'danger' : (($state ?? 0) > 2 ? 'warning' : 'info')
                    )
                    ->formatStateUsing(fn(?int $state): string => ($state ?? 0) . ' hari'),

                TextColumn::make('tanpa_keterangan')
                    ->label('Alpha')
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    ->color(fn(?int $state): string => ($state ?? 0) > 0 ? 'danger' : 'success')
                    ->formatStateUsing(fn(?int $state): string => ($state ?? 0) . ' hari'),

                TextColumn::make('total_absen')
                    ->label('Total')
                    ->alignCenter()
                    ->getStateUsing(function (?Model $record): int {
                        if (!$record) return 0;
                        return ($record->sakit ?? 0) + ($record->izin ?? 0) + ($record->tanpa_keterangan ?? 0);
                    })
                    ->badge()
                    ->color(fn(int $state): string => self::getAbsenceColor($state))
                    ->formatStateUsing(fn(int $state): string => $state . ' hari')
                    ->description(function (?Model $record): string {
                        if (!$record) return '';

                        $total = ($record->sakit ?? 0) + ($record->izin ?? 0) + ($record->tanpa_keterangan ?? 0);
                        return match (true) {
                            $total > 15 => 'Sangat Tinggi',
                            $total > 10 => 'Tinggi',
                            $total > 5 => 'Sedang',
                            $total > 0 => 'Rendah',
                            default => 'Tidak Ada'
                        };
                    }),

                TextColumn::make('catatan')
                    ->label('Catatan')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return !empty($state) ? $state : null;
                    })
                    ->placeholder('Tidak ada catatan')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                SelectFilter::make('periode_id')
                    ->label('Periode')
                    ->options(fn() => self::getPeriodeOptions())
                    // ->default(self::getActivePeriodeId())
                    ->placeholder('Semua Periode'),

                // Tables\Filters\SelectFilter::make('kelas_id')
                //     ->label('Kelas')
                //     ->relationship('kelas', 'nama_kelas')
                //     ->default()
                //     ->searchable()
                //     ->preload()
                //     ->placeholder('Semua Kelas'),

                Filter::make('has_kelas')
                    ->label('Hanya yang memiliki Kelas')
                    ->schema([
                        Select::make('has_kelas')
                            ->options([
                                'yes' => 'Ya',
                                'no' => 'Tidak',
                            ])
                            ->required(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['has_kelas'] ?? null;
                        if ($value === 'yes') {
                            return $query->whereNotNull('kelas_id');
                        } elseif ($value === 'no') {
                            return $query->whereNull('kelas_id');
                        }
                        return $query;
                    }),

                SelectFilter::make('siswa_id')
                    ->label('Siswa')
                    ->relationship('siswa', 'nama_lengkap')
                    ->searchable()
                    ->placeholder('Semua Siswa'),

                Filter::make('periode_tanggal')
                    ->schema([
                        DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->native(false),
                        DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['dari_tanggal'] ?? null) {
                            $indicators[] = 'Dari: ' . Carbon::parse($data['dari_tanggal'])->format('d M Y');
                        }

                        if ($data['sampai_tanggal'] ?? null) {
                            $indicators[] = 'Sampai: ' . Carbon::parse($data['sampai_tanggal'])->format('d M Y');
                        }

                        return $indicators;
                    }),

                Filter::make('high_absence')
                    ->label('Absensi Tinggi (>10 hari)')
                    ->query(
                        fn(Builder $query): Builder =>
                        $query->whereRaw('COALESCE(sakit, 0) + COALESCE(izin, 0) + COALESCE(tanpa_keterangan, 0) > 10')
                    )
                    ->toggle(),

                Filter::make('alpha_only')
                    ->label('Ada Alpha')
                    ->query(
                        fn(Builder $query): Builder =>
                        $query->where('tanpa_keterangan', '>', 0)
                    )
                    ->toggle(),

                TernaryFilter::make('periode_status')
                    ->label('Status Periode')
                    ->placeholder('Semua periode')
                    ->trueLabel('Periode Aktif')
                    ->falseLabel('Periode Tidak Aktif')
                    ->queries(
                        true: fn(Builder $query) => $query->whereHas('periode', fn(Builder $query) => $query->where('is_active', true)),
                        false: fn(Builder $query) => $query->whereHas('periode', fn(Builder $query) => $query->where('is_active', false)),
                        blank: fn(Builder $query) => $query,
                    ),
            ])
            ->recordActions([
            ActionGroup::make([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])->label('Aksi')
                ->icon('heroicon-m-bars-3-center-left')
                ->size('sm')
                ->color('gray')
                ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make(),
            ])
            ->striped();
    }

    /**
     * Get appropriate color for absence count
     */
    private static function getAbsenceColor(int $count): string
    {
        return match (true) {
            $count > 15 => 'danger',
            $count > 10 => 'warning',
            $count > 5 => 'info',
            $count > 0 => 'success',
            default => 'gray'
        };
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAbsensis::route('/'),
            'create' => CreateAbsensi::route('/create'),
            'edit' => EditAbsensi::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            $count = static::getModel()::count();
            return $count > 0 ? (string) $count : null;
        } catch (Exception $e) {
            Log::error('Failed to get navigation badge: ' . $e->getMessage());
            return null;
        }
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }
}
