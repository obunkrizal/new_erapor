<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms;
use Filament\Tables;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Periode;
use Filament\Forms\Form;
use App\Models\KelasSiswa;
use Filament\Tables\Table;
use App\Models\DataMedisSiswa;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use App\Filament\Resources\DataMedisSiswaResource\Pages;

class DataMedisSiswaResource extends Resource
{
    protected static ?string $model = DataMedisSiswa::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationLabel = 'Manajemen Medis Siswa';

    protected static ?string $modelLabel = 'Manajemen Medis Siswa';

    protected static ?string $pluralModelLabel = 'Manajemen Medis Siswa';

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?int $navigationSort = 4;

    // Hide navigation from guru - only show for admin
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->isGuru() ?? false;
    }

    // Control access - only admin can access
    public static function canAccess(): bool
    {
        return Auth::user()?->isGuru() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                self::createPeriodeKelasSection(),
                self::createInformasiDasarSection(),
                self::createDetailFisikSection(),
                self::createRiwayatCatatanSection(),
            ]);
    }

    /**
     * Create periode and kelas selection section
     */
    private static function createPeriodeKelasSection(): Section
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
                    ->disabled(function (?Model $record) {
                        // Disable in edit mode when record exists
                        return $record !== null;
                    })
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $set('kelas_id', null);
                        $set('siswa_id', null);
                    }),

            Select::make('kelas_id')
                ->label('Kelas')
                ->options(function (Forms\Get $get) {
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
                ->default(function (Forms\Get $get) {
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
                ->disabled(function (Forms\Get $get) {
                    $periodeId = $get('periode_id');
                    $guruId = Auth::user()?->guru?->id;

                    // Disable if periode not selected OR in edit mode OR guru has no kelas for periode
                    if (empty($periodeId) || request()->routeIs('filament.admin.resources.data-medis-siswa.edit')) {
                        return true;
                    }

                    $kelasCount = Kelas::where('periode_id', $periodeId)
                        ->where('guru_id', $guruId)
                        ->count();

                    return $kelasCount === 0;
                })
                ->live()
                ->afterStateUpdated(function ($state, Forms\Set $set) {
                    $set('siswa_id', null);
                    if ($state) {
                        $set('tanggal', now()->format('Y-m-d'));
                    }
                }),


            Select::make('siswa_id')
                    ->label('Siswa')
                    ->options(function (Forms\Get $get, ?Model $record) {
                        $kelasId = $get('kelas_id') ?? $record?->kelas_id;
                        $periodeId = $get('periode_id') ?? $record?->periode_id;

                        return ($kelasId && $periodeId)
                            ? self::getSiswaByKelasAndPeriodeOptions($kelasId, $periodeId)
                            : [];
                    })
                    ->searchable()
                    ->required()
                    ->placeholder(function (Forms\Get $get, ?Model $record) {
                        if ($record) return 'Siswa tidak dapat diubah';
                        if (!$get('periode_id')) return 'Pilih periode terlebih dahulu';
                        if (!$get('kelas_id')) return 'Pilih kelas terlebih dahulu';
                        return 'Pilih Siswa';
                    })
                    ->helperText('Siswa akan muncul setelah memilih periode dan kelas')
                    ->disabled(function (Forms\Get $get, ?Model $record) {
                        // Disable if requirements not met OR in edit mode
                        return $record !== null ||
                            empty($get('kelas_id')) ||
                            empty($get('periode_id'));
                    })
                    ->suffixIcon('heroicon-m-user')
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        if ($state) {
                            $set('tanggal_pemeriksaan', now()->format('Y-m-d'));
                        }
                    }),
            ])->columns(3);
    }


    /**
     * Create informasi dasar section
     */
    private static function createInformasiDasarSection(): Section
    {
        return Section::make('Informasi Pemeriksaan')
            ->description('Detail tanggal dan validasi data medis')
            ->schema([
                DatePicker::make('tanggal_pemeriksaan')
                    ->label('Tanggal Pemeriksaan')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->default(now())
                    ->helperText('Tanggal pemeriksaan medis siswa')
                    ->disabled(function (?Model $record) {
                        // Disable tanggal field in edit mode
                        return $record !== null;
                    })
                    ->live(),

                Placeholder::make('validation_info')
                    ->label('Status Validasi')
                    ->content(function (Forms\Get $get, ?Model $record): string {
                        if ($record !== null) {
                            return "ℹ️ **Mode Edit:** Siswa dan tanggal tidak dapat diubah";
                        }
                        return self::getValidationStatus($get);
                    })
                    ->columnSpan(2),
            ])
            ->columns(3)
            ->visible(fn(Forms\Get $get, ?Model $record) => !empty($get('siswa_id')) || $record !== null);
    }
    /**
     * Create detail fisik section
     */
    private static function createDetailFisikSection(): Section
    {
        return Section::make('Detail Fisik')
            ->description('Data antropometri dan golongan darah siswa')
            ->schema([
                TextInput::make('tinggi_badan')
                    ->label('Tinggi Badan')
                    ->numeric()
                    ->suffix(' cm')
                    ->nullable()
                    ->placeholder('Masukkan tinggi badan')
                    ->helperText('Tinggi badan siswa dalam centimeter')
                    ->minValue(0)
                    ->maxValue(300)
                    ->step(0.1)
                    ->live(onBlur: true),

                TextInput::make('berat_badan')
                    ->label('Berat Badan')
                    ->numeric()
                    ->suffix(' kg')
                    ->nullable()
                    ->placeholder('Masukkan berat badan')
                    ->helperText('Berat badan siswa dalam kilogram')
                    ->minValue(0)
                    ->maxValue(200)
                    ->step(0.1)
                    ->live(onBlur: true),

                Select::make('golongan_darah')
                    ->label('Golongan Darah')
                    ->options(self::getGolonganDarahOptions())
                    ->nullable()
                    ->placeholder('Pilih golongan darah')
                    ->native(false)
                    ->helperText('Golongan darah siswa'),

                Placeholder::make('bmi_info')
                    ->label('Indeks Massa Tubuh (BMI)')
                    ->content(function (Forms\Get $get): string {
                        return self::calculateBMI($get);
                    }),
            ])->columns(4);
    }

    /**
     * Create riwayat dan catatan section
     */
    private static function createRiwayatCatatanSection(): Section
    {
        return Section::make('Riwayat dan Catatan Medis')
            ->description('Informasi riwayat penyakit dan catatan tambahan')
            ->schema([
                Textarea::make('riwayat_penyakit')
                    ->label('Riwayat Penyakit')
                    ->rows(4)
                    ->nullable()
                    ->placeholder('Masukkan riwayat penyakit atau kondisi medis khusus')
                    ->helperText('Riwayat penyakit yang pernah diderita siswa')
                    ->maxLength(2000),

                Textarea::make('catatan')
                    ->label('Catatan Medis')
                    ->rows(4)
                    ->nullable()
                    ->placeholder('Masukkan catatan tambahan dari pemeriksaan')
                    ->helperText('Catatan penting lainnya terkait kondisi medis siswa')
                    ->maxLength(2000),
            ])->columns(2);
    }

    /**
     * Get active periode ID
     */
    private static function getActivePeriodeId(): ?int
    {
        try {
            return Periode::where('is_active', true)->value('id');
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
                ->with('siswa:id,nama_lengkap,nis,jenis_kelamin,tanggal_lahir')
                ->get()
                ->filter(fn($kelasSiswa) => $kelasSiswa->siswa)
                ->mapWithKeys(function ($kelasSiswa) {
                    $siswa = $kelasSiswa->siswa;
                    $info = [];
                    if ($siswa->nis) $info[] = "NIS: {$siswa->nis}";
                    if ($siswa->jenis_kelamin) $info[] = $siswa->jenis_kelamin;
                    if ($siswa->tanggal_lahir) {
                        $age = \Carbon\Carbon::parse($siswa->tanggal_lahir)->age;
                        $info[] = "{$age} tahun";
                    }

                    $additionalInfo = !empty($info) ? ' (' . implode(', ', $info) . ')' : '';
                    return [$siswa->id => $siswa->nama_lengkap . $additionalInfo];
                })
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to load siswa by kelas and periode: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get validation status for form
     */
    private static function getValidationStatus(Forms\Get $get, ?Model $record = null): string
    {
        $siswaId = $get('siswa_id');
        $tanggalPemeriksaan = $get('tanggal_pemeriksaan');
        $periodeId = $get('periode_id');
        $kelasId = $get('kelas_id');

        if (!$siswaId || !$tanggalPemeriksaan || !$periodeId || !$kelasId) {
            return 'Lengkapi semua data untuk validasi';
        }

        try {
            $query = DataMedisSiswa::where('siswa_id', $siswaId)
                ->where('periode_id', $periodeId)
                ->where('kelas_id', $kelasId)
                ->whereDate('tanggal_pemeriksaan', $tanggalPemeriksaan);

            // Exclude current record if editing
            if ($record) {
                $query->where('id', '!=', $record->id);
            }

            $existingRecord = $query->first();

            if ($existingRecord) {
                return "⚠️ **Peringatan:** Data medis untuk siswa ini sudah ada pada tanggal {$tanggalPemeriksaan}!";
            }

            return "✅ **Valid:** Data dapat disimpan";
        } catch (\Exception $e) {
            Log::error('Validation check error: ' . $e->getMessage());
            return 'Error saat validasi';
        }
    }
    /**
     * Validate unique medical record
     */
    private static function validateUniqueMedicalRecord(Forms\Get $get, ?Model $record = null): bool|string
    {
        $siswaId = $get('siswa_id');
        $periodeId = $get('periode_id');
        $kelasId = $get('kelas_id');
        $tanggalPemeriksaan = $get('tanggal_pemeriksaan');

        if (!$siswaId || !$periodeId || !$kelasId || !$tanggalPemeriksaan) {
            return true;
        }

        try {
            $query = DataMedisSiswa::where('siswa_id', $siswaId)
                ->where('periode_id', $periodeId)
                ->where('kelas_id', $kelasId)
                ->whereDate('tanggal_pemeriksaan', $tanggalPemeriksaan);

            // Exclude current record if editing
            if ($record) {
                $query->where('id', '!=', $record->id);
            }

            if ($query->exists()) {
                return 'Data Medis untuk siswa ini pada periode, kelas, dan tanggal yang sama sudah ada.';
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Unique validation error: ' . $e->getMessage());
            return 'Error saat validasi duplikasi';
        }
    }

    /**
     * Calculate BMI
     */
    private static function calculateBMI(Forms\Get $get): string
    {
        $tinggi = (float) ($get('tinggi_badan') ?? 0);
        $berat = (float) ($get('berat_badan') ?? 0);

        if ($tinggi <= 0 || $berat <= 0) {
            return 'Masukkan tinggi dan berat badan untuk menghitung BMI';
        }

        $tinggiMeter = $tinggi / 100;
        $bmi = $berat / ($tinggiMeter * $tinggiMeter);

        $kategori = match (true) {
            $bmi < 18.5 => '📉 Kurus',
            $bmi < 25 => '✅ Normal',
            $bmi < 30 => '⚠️ Gemuk',
            default => '🔴 Obesitas'
        };

        return sprintf("**BMI: %.1f** - %s", $bmi, $kategori);
    }

    /**
     * Get available blood type options
     */
    private static function getGolonganDarahOptions(): array
    {
        return [
            'Tidak Tahu'=>'Tidak Tahu',
            'A' => 'A',
            'B' => 'B',
            'AB' => 'AB',
            'O' => 'O',
        ];
    }

    /**
     * Get color for blood type badge
     */
    public static function getGolonganDarahColor(?string $state): string
    {
        if (empty($state)) {
            return 'gray';
        }

        return match (strtoupper(trim($state))) {
            'Tidak Tahu'=>'purple',
            'A' => 'success',
            'B' => 'info',
            'AB' => 'warning',
            'O' => 'danger',
            default => 'gray'
        };
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('periode.tahun_ajaran')
                    ->label('Periode')
                    ->badge()
                    ->color(function (?Model $record): string {
                        return $record?->periode?->is_active ? 'success' : 'gray';
                    })
                    ->formatStateUsing(function (?Model $record): string {
                        if (!$record?->periode) return 'Tidak ada periode';

                        $tahunAjaran = $record->periode->tahun_ajaran ?? 'N/A';
                        $semester = $record->periode->semester ?? '';
                        $status = $record->periode->is_active ? ' (Aktif)' : '';

                        return $tahunAjaran . ' - ' . $semester . $status;
                    })
                    ->searchable(['periode.tahun_ajaran', 'periode.semester'])
                    ->placeholder('Tidak ada periode'),

                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('siswa.nama_lengkap')
                    ->label('Nama Siswa')
                    ->searchable(['siswa.nama_lengkap', 'siswa.nis'])
                    ->sortable()
                    ->wrap()
                    ->description(function (?Model $record): ?string {
                        return $record?->siswa?->nis ? "NIS: {$record->siswa->nis}" : null;
                    }),

                TextColumn::make('tanggal_pemeriksaan')
                    ->label('Tgl. Pemeriksaan')
                    ->date('d M Y')
                    ->sortable()
                    ->description(function (?Model $record): string {
                        if (!$record?->tanggal_pemeriksaan) return '';
                        return 'Bulan ' . \Carbon\Carbon::parse($record->tanggal_pemeriksaan)->format('F Y');
                    }),

                TextColumn::make('tinggi_badan')
                    ->label('Tinggi')
                    ->formatStateUsing(function (?string $state): string {
                        return $state ? $state . ' cm' : '-';
                    })
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('berat_badan')
                    ->label('Berat')
                    ->formatStateUsing(function (?string $state): string {
                        return $state ? $state . ' kg' : '-';
                    })
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('bmi')
                    ->label('BMI')
                    ->getStateUsing(function (?Model $record): string {
                        if (!$record || !$record->tinggi_badan || !$record->berat_badan) {
                            return '-';
                        }

                        $tinggiMeter = $record->tinggi_badan / 100;
                        $bmi = $record->berat_badan / ($tinggiMeter * $tinggiMeter);

                        return number_format($bmi, 1);
                    })
                    ->badge()
                    ->color(function (?Model $record): string {
                        if (!$record || !$record->tinggi_badan || !$record->berat_badan) {
                            return 'gray';
                        }

                        $tinggiMeter = $record->tinggi_badan / 100;
                        $bmi = $record->berat_badan / ($tinggiMeter * $tinggiMeter);

                        return match (true) {
                            $bmi < 18.5 => 'warning',
                            $bmi < 25 => 'success',
                            $bmi < 30 => 'warning',
                            default => 'danger'
                        };
                    })
                    ->alignCenter(),

                TextColumn::make('golongan_darah')
                    ->label('Gol. Darah')
                    ->sortable()
                    ->badge()
                    ->color(function (?string $state): string {
                        return self::getGolonganDarahColor($state);
                    })
                    ->formatStateUsing(function (?string $state): string {
                        return $state ?? '-';
                    })
                    ->alignCenter(),

                TextColumn::make('riwayat_penyakit')
                    ->label('Riwayat Penyakit')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return !empty($state) ? $state : null;
                    })
                    ->placeholder('Tidak ada riwayat')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('catatan')
                    ->label('Catatan Medis')
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
            ->defaultSort('tanggal_pemeriksaan', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('periode_id')
                    ->label('Periode')
                    ->options(fn() => self::getPeriodeOptions())
                    ->default(self::getActivePeriodeId())
                    ->placeholder('Semua Periode'),

                Tables\Filters\SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->relationship('kelas', 'nama_kelas')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua Kelas'),

                Tables\Filters\SelectFilter::make('golongan_darah')
                    ->label('Golongan Darah')
                    ->options(self::getGolonganDarahOptions())
                    ->placeholder('Semua Golongan Darah'),

                Tables\Filters\Filter::make('tanggal_pemeriksaan')
                    ->form([
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
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_pemeriksaan', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_pemeriksaan', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['dari_tanggal'] ?? null) {
                            $indicators[] = 'Dari: ' . \Carbon\Carbon::parse($data['dari_tanggal'])->format('d M Y');
                        }

                        if ($data['sampai_tanggal'] ?? null) {
                            $indicators[] = 'Sampai: ' . \Carbon\Carbon::parse($data['sampai_tanggal'])->format('d M Y');
                        }

                        return $indicators;
                    }),

                Tables\Filters\Filter::make('bmi_status')
                    ->form([
                        Select::make('bmi_category')
                            ->label('Kategori BMI')
                            ->options([
                                'underweight' => 'Kurus (BMI < 18.5)',
                                'normal' => 'Normal (BMI 18.5-24.9)',
                                'overweight' => 'Gemuk (BMI 25-29.9)',
                                'obese' => 'Obesitas (BMI ≥ 30)',
                            ])
                            ->placeholder('Semua Kategori'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['bmi_category'] ?? null,
                            function (Builder $query, $category) {
                                $query->whereNotNull('tinggi_badan')
                                    ->whereNotNull('berat_badan')
                                    ->where('tinggi_badan', '>', 0)
                                    ->where('berat_badan', '>', 0);

                                return match ($category) {
                                    'underweight' => $query->whereRaw('(berat_badan / POW(tinggi_badan / 100, 2)) < 18.5'),
                                    'normal' => $query->whereRaw('(berat_badan / POW(tinggi_badan / 100, 2)) BETWEEN 18.5 AND 24.9'),
                                    'overweight' => $query->whereRaw('(berat_badan / POW(tinggi_badan / 100, 2)) BETWEEN 25 AND 29.9'),
                                    'obese' => $query->whereRaw('(berat_badan / POW(tinggi_badan / 100, 2)) >= 30'),
                                    default => $query,
                                };
                            }
                        );
                    }),
            ])
            ->actions([
                ActionGroup::make([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                ])->label('Aksi')
                ->icon('heroicon-m-bars-3-center-left')
                ->size('sm')
                ->color('gray')
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->striped();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDataMedisSiswas::route('/'),
            'create' => Pages\CreateDataMedisSiswa::route('/create'),
            'edit' => Pages\EditDataMedisSiswa::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            $count = static::getModel()::count();
            return $count > 0 ? (string) $count : null;
        } catch (\Exception $e) {
            Log::error('Failed to get navigation badge: ' . $e->getMessage());
            return null;
        }
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
