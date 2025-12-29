<?php

namespace App\Filament\Resources\KelasSiswas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Carbon\Carbon;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;
use App\Filament\Resources\KelasSiswas\Pages\ListKelasSiswas;
use App\Filament\Resources\KelasSiswas\Pages\CreateKelasSiswa;
use App\Filament\Resources\KelasSiswas\Pages\ViewKelasSiswa;
use App\Filament\Resources\KelasSiswas\Pages\EditKelasSiswa;
use Exception;
use Filament\Forms;
use Filament\Tables;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Periode;
use App\Models\KelasSiswa;
use Filament\Tables\Table;
use App\Models\PindahKelas;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\KelasSiswaResource\Pages;

class KelasSiswaResource extends Resource
{
    protected static ?string $model = KelasSiswa::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Kelas Siswa';

    protected static ?string $modelLabel = 'Kelas Siswa';

    protected static ?string $pluralModelLabel = 'Kelas Siswa';

    protected static string | \UnitEnum | null $navigationGroup = 'Akademik';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'siswa.nama_lengkap';

    // Hide navigation for guru, only show for admin
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    // Only allow admin to access this resource
    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    // Main function to process class transfer - integrated from PindahKelasResource
    public static function processPindahKelas(int $siswaId, int $kelasAsalId, int $kelasTujuanId, int $periodeId, string $alasanPindah, ?string $catatan = null): bool
    {
        Log::info('Starting class transfer process', [
            'siswa_id' => $siswaId,
            'kelas_asal_id' => $kelasAsalId,
            'kelas_tujuan_id' => $kelasTujuanId,
            'periode_id' => $periodeId,
            'alasan_pindah' => $alasanPindah,
            'catatan' => $catatan,
            'user_id' => Auth::id(),
        ]);

        DB::beginTransaction();

        try {
            // Validate input data
            if ($kelasAsalId === $kelasTujuanId) {
                throw new Exception('Kelas asal dan kelas tujuan tidak boleh sama!');
            }

            // Get kelas information
            $kelasAsal = Kelas::find($kelasAsalId);
            $kelasTujuan = Kelas::find($kelasTujuanId);
            $siswa = Siswa::find($siswaId);
            $periode = Periode::find($periodeId);

            if (!$kelasAsal || !$kelasTujuan || !$siswa || !$periode) {
                throw new Exception('Data kelas, siswa, atau periode tidak ditemukan!');
            }

            // Check if student is currently active in the source class
            $currentKelasSiswa = KelasSiswa::where('siswa_id', $siswaId)
                ->where('kelas_id', $kelasAsalId)
                ->where('periode_id', $periodeId)
                ->where('status', 'aktif')
                ->first();

            if (!$currentKelasSiswa) {
                throw new Exception('Siswa tidak terdaftar aktif di kelas asal!');
            }

            // Check if student is already in target class
            $existingInTarget = KelasSiswa::where('siswa_id', $siswaId)
                ->where('kelas_id', $kelasTujuanId)
                ->where('periode_id', $periodeId)
                ->where('status', 'aktif')
                ->exists();

            if ($existingInTarget) {
                throw new Exception('Siswa sudah terdaftar di kelas tujuan!');
            }

            // Check if target class has capacity
            $currentStudentCount = KelasSiswa::where('kelas_id', $kelasTujuanId)
                ->where('periode_id', $periodeId)
                ->where('status', 'aktif')
                ->count();

            if ($kelasTujuan->kapasitas && $currentStudentCount >= $kelasTujuan->kapasitas) {
                throw new Exception("Kelas tujuan sudah penuh! Kapasitas: {$kelasTujuan->kapasitas}, Terisi: {$currentStudentCount}");
            }

            // Create PindahKelas record
            $pindahKelas = PindahKelas::create([
                'siswa_id' => $siswaId,
                'kelas_asal_id' => $kelasAsalId,
                'kelas_tujuan_id' => $kelasTujuanId,
                'periode_id' => $periodeId,
                'tanggal_pindah' => now(),
                'alasan_pindah' => $alasanPindah,
                'catatan' => $catatan,
                'status' => 'approved',
                'user_id' => Auth::id(),
            ]);

            // Update current active class record to 'pindah'
            $currentKelasSiswa->update([
                'status' => 'pindah',
                'tanggal_keluar' => now(),
                'keterangan' => "Pindah ke {$kelasTujuan->nama_kelas} - {$alasanPindah}" . ($catatan ? " | Catatan: {$catatan}" : ''),
                'updated_at' => now(),
            ]);

            // Create new record in target class
            KelasSiswa::create([
                'siswa_id' => $siswaId,
                'kelas_id' => $kelasTujuanId,
                'periode_id' => $periodeId,
                'status' => 'aktif',
                'tanggal_masuk' => now(),
                'keterangan' => "Pindahan dari {$kelasAsal->nama_kelas} - {$alasanPindah}" . ($catatan ? " | Catatan: {$catatan}" : ''),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            Notification::make()
                ->title('Perpindahan Kelas Berhasil')
                ->body("Siswa {$siswa->nama_lengkap} berhasil dipindahkan dari {$kelasAsal->nama_kelas} ke {$kelasTujuan->nama_kelas}")
                ->success()
                ->send();

            Log::info('Class transfer successful', [
                'siswa_id' => $siswaId,
                'siswa_name' => $siswa->nama_lengkap,
                'kelas_asal' => $kelasAsal->nama_kelas,
                'kelas_tujuan' => $kelasTujuan->nama_kelas,
                'periode' => $periode->nama_periode,
                'user_id' => Auth::id(),
            ]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Error Perpindahan Kelas')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->send();

            Log::error('Class transfer failed', [
                'siswa_id' => $siswaId,
                'kelas_asal_id' => $kelasAsalId,
                'kelas_tujuan_id' => $kelasTujuanId,
                'periode_id' => $periodeId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return false;
        }
    }

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
                                    $available = self::getAvailableSiswaOptions($get('kelas_id') ?? $record->kelas_id, $get('periode_id') ?? $record->periode_id, $record);
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
                    ->defaultImageUrl(url('/images/default-avatar.png'))
                    ->toggleable(isToggledHiddenByDefault: false),

            TextColumn::make('siswa.nama_lengkap')
                    ->label('Nama Siswa')
                    ->searchable(['nama_lengkap', 'nis'])
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                ->description(
                    fn(KelasSiswa $record): string =>
                        'NIS: ' . ($record->siswa?->nis ?? 'N/A')
                    )
                    ->formatStateUsing(function (KelasSiswa $record): string {
                        return $record->siswa?->nama_lengkap ?? $record->siswa?->nama_lengkap ?? 'N/A';
                    })
                    ->wrap(),

            TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(function (KelasSiswa $record): string {
                // Different colors based on class name patterns or specific classes
                $namaKelas = $record->kelas?->rentang_usia   ?? '';

                // Option 1: Color based on grade level
                if (str_contains($namaKelas, '2-3') || str_contains($namaKelas, '2-3    ')) {
                            return 'success'; // Green for grade 10
                } elseif (str_contains($namaKelas, '4-5') || str_contains($namaKelas, '4-5')) {
                            return 'warning'; // Yellow for grade 11
                } elseif (str_contains($namaKelas, '5-6') || str_contains($namaKelas, '5-6')) {
                            return 'danger'; // Red for grade 12
                        }

                // Option 2: Color based on specific class names
                if (str_contains($namaKelas, 'A') || str_contains($namaKelas, 'A')) {
                    return 'primary';
                        }

                        // Option 3: Random colors based on class ID for variety
                        $colors = ['primary', 'secondary', 'success', 'warning', 'danger', 'info'];
                        return $colors[($record->kelas_id ?? 0) % count($colors)];
                    })
                    ->icon(function (KelasSiswa $record): string {
                $namaKelas = $record->kelas?->rentang_usia ?? '';

                // Different icons based on class
                if (str_contains($namaKelas, '2-3') || str_contains($namaKelas, '2-3')) {
                            return 'heroicon-m-academic-cap';
                } elseif (str_contains($namaKelas, '4-5') || str_contains($namaKelas, '4-5')) {
                            return 'heroicon-m-book-open';
                } elseif (str_contains($namaKelas, '5-6') || str_contains($namaKelas, '5-6')) {
                            return 'heroicon-m-trophy';
                        }

                        return 'heroicon-m-building-library';
                    })
                    ->formatStateUsing(function (KelasSiswa $record): string {
                        // You can also format the text display
                        $namaKelas = $record->kelas?->nama_kelas ?? 'N/A';

                        // Add additional info to the display
                        if ($record->kelas?->kapasitas) {
                    $currentCount = KelasSiswa::where('kelas_id', $record->kelas_id)
                                ->where('status', 'aktif')
                                ->count();
                            return $namaKelas . " ({$currentCount}/{$record->kelas->kapasitas})";
                        }

                        return $namaKelas;
                })
                ->description(
                    fn(KelasSiswa $record): string =>
                    'Rentang Usia: ' . ($record->kelas?->rentang_usia ?? 'N/A')
                ),

            TextColumn::make('periode.nama_periode')
                    ->label('Periode')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->toggleable()
                ->description(
                    fn(KelasSiswa $record): string =>
                        'Semester: ' . ucfirst($record->periode?->semester ?? 'N/A')
                    ),

            TextColumn::make('periode.tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->sortable()
                    ->badge()
                ->color(
                    fn(KelasSiswa $record): string =>
                        $record->periode?->is_active ? 'success' : 'gray'
                    )
                ->formatStateUsing(
                    fn(KelasSiswa $record): string =>
                        $record->periode?->tahun_ajaran . ($record->periode?->is_active ? ' (Aktif)' : '')
                    )
                ->icon(
                    fn(KelasSiswa $record): string =>
                        $record->periode?->is_active ? 'heroicon-m-check-circle' : 'heroicon-m-clock'
                    ),

            TextColumn::make('periode.semester')
                    ->label('Semester')
                    ->badge()
                ->color(fn(?string $state): string => match ($state) {
                        'ganjil' => 'info',
                        'genap' => 'warning',
                        default => 'gray',
                    })
                ->formatStateUsing(fn(?string $state): string => $state ? ucfirst($state) : 'N/A'),

            TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                ->color(fn(string $state): string => match ($state) {
                        'aktif' => 'success',
                        'pindah' => 'warning',
                        'lulus' => 'info',
                        'keluar' => 'danger',
                        default => 'gray',
                    })
                ->icon(fn(string $state): string => match ($state) {
                        'aktif' => 'heroicon-m-check-circle',
                        'pindah' => 'heroicon-m-arrow-right-circle',
                        'lulus' => 'heroicon-m-academic-cap',
                        'keluar' => 'heroicon-m-x-circle',
                        default => 'heroicon-m-question-mark-circle',
                    })
                ->formatStateUsing(fn(string $state): string => ucfirst($state)),

            TextColumn::make('tanggal_masuk')
                    ->label('Tanggal Masuk')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable()
                    ->icon('heroicon-m-calendar'),

            TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(30)
                ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) > 30) {
                            return $state;
                        }
                        return null;
                    })
                    ->placeholder('Tidak ada keterangan')
                    ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since(),

            TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since(),
            ])
            ->filters([
            SelectFilter::make('periode_id')
                    ->label('Periode')
                    ->relationship('periode', 'nama_periode')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->default([self::getActivePeriodeId()]),


            SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->relationship('kelas', 'nama_kelas')
                    ->searchable()
                    ->preload()
                    ->multiple(),

            SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'aktif' => 'Aktif',
                        'pindah' => 'Pindah',
                        'lulus' => 'Lulus',
                        'keluar' => 'Keluar',
                    ])
                    ->multiple()
                    ->default(['aktif']),

            Filter::make('active_periode')
                    ->label('Periode Aktif')
                ->query(
                    fn(Builder $query): Builder =>
                    $query->whereHas(
                        'periode',
                        fn(Builder $q) =>
                            $q->where('is_active', true)
                        )
                    )
                    ->toggle()
                    ->default(true),

            Filter::make('tanggal_masuk')
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
                                $data['dari_tanggal'],
                    fn(Builder $query, $date): Builder => $query->whereDate('tanggal_masuk', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                    fn(Builder $query, $date): Builder => $query->whereDate('tanggal_masuk', '<=', $date),
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

            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),

                // Add Pindah Kelas Action
                Action::make('pindah_kelas')
                        ->label('Pindah Kelas')
                        ->icon('heroicon-o-arrow-right-circle')
                        ->color('warning')
                    ->visible(fn(KelasSiswa $record): bool => $record->status === 'aktif')
                    ->schema([
                    Section::make('Informasi Siswa')
                        ->schema([
                            Placeholder::make('siswa_info')
                                        ->label('Siswa')
                            ->content(
                                fn(KelasSiswa $record): string =>
                                            $record->siswa->nama_lengkap . ' (NIS: ' . $record->siswa->nis . ')'
                                        ),
                        Placeholder::make('kelas_asal')
                                        ->label('Kelas Asal')
                            ->content(
                                fn(KelasSiswa $record): string =>
                                            $record->kelas->nama_kelas
                                        ),
                                ])
                                ->columns(2),

                    Section::make('Tujuan Pindah')
                                ->schema([
                        Select::make('kelas_tujuan_id')
                                        ->label('Kelas Tujuan')
                                        ->options(function (KelasSiswa $record) {
                                            return Kelas::where('periode_id', $record->periode_id)
                                                ->where('id', '!=', $record->kelas_id)
                                                ->pluck('nama_kelas', 'id')
                                                ->toArray();
                                        })
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->helperText('Pilih kelas tujuan untuk perpindahan'),

                        Select::make('alasan_pindah')
                                        ->label('Alasan Pindah')
                                        ->options([
                                            'Permintaan Orang Tua' => 'Permintaan Orang Tua',
                                            'Prestasi Akademik' => 'Prestasi Akademik',
                                            'Masalah Adaptasi' => 'Masalah Adaptasi',
                                            'Kapasitas Kelas' => 'Kapasitas Kelas',
                                            'Lainnya' => 'Lainnya',
                                        ])
                                        ->required()
                                        ->native(false),

                        Textarea::make('catatan')
                                        ->label('Catatan')
                                        ->placeholder('Catatan tambahan (opsional)')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ])
                        ->action(function (KelasSiswa $record, array $data): void {
                            $success = self::processPindahKelas(
                                siswaId: $record->siswa_id,
                                kelasAsalId: $record->kelas_id,
                                kelasTujuanId: $data['kelas_tujuan_id'],
                                periodeId: $record->periode_id,
                                alasanPindah: $data['alasan_pindah'],
                                catatan: $data['catatan'] ?? null
                            );

                            if ($success) {
                                $record->refresh();
                                notyf()->success('Perpindahan kelas berhasil dilakukan');
                            } else {

                                notyf()->error(' Perpindahan kelas gagal dilakukan, Kelas sudah penuh');
                    }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Pindah Kelas Siswa')
                        ->modalDescription('Pastikan data perpindahan kelas sudah benar sebelum melanjutkan.')
                        ->modalSubmitActionLabel('Pindahkan'),

                DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.'),
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
                        ->requiresConfirmation()
                        ->modalDescription('Apakah Anda yakin ingin menghapus data yang dipilih? Tindakan ini tidak dapat dibatalkan.'),

                BulkAction::make('update_status')
                        ->label('Update Status')
                        ->icon('heroicon-o-pencil-square')
                        ->form([
                    Select::make('status')
                                ->label('Status Baru')
                                ->options([
                                    'aktif' => 'Aktif',
                                    'pindah' => 'Pindah',
                                    'lulus' => 'Lulus',
                                    'keluar' => 'Keluar',
                                ])
                                ->required()
                                ->native(false),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                        })
                        ->requiresConfirmation()
                        ->modalDescription('Status akan diubah untuk semua data yang dipilih.')
                        ->color('warning'),

                BulkAction::make('export')
                        ->label('Export Data')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function ($records) {
                            return response()->streamDownload(function () use ($records) {
                                echo "Nama Siswa,NIS,Kelas,Periode,Status,Tanggal Masuk\n";
                                foreach ($records as $record) {
                                    $siswaName = $record->siswa?->nama_lengkap ?? $record->siswa?->nama_lengkap ?? 'N/A';
                                    echo implode(',', [
                                        $siswaName,
                                        $record->siswa?->nis ?? 'N/A',
                                        $record->kelas?->nama_kelas ?? 'N/A',
                                        $record->periode?->nama_periode ?? 'N/A',
                                        ucfirst($record->status),
                                        $record->tanggal_masuk?->format('d/m/Y') ?? 'N/A',
                                    ]) . "\n";
                                }
                            }, 'kelas-siswa-' . now()->format('Y-m-d') . '.csv');
                        })
                        ->color('success')
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKelasSiswas::route('/'),
            'create' => CreateKelasSiswa::route('/create'),
            'view' => ViewKelasSiswa::route('/{record}'),
            'edit' => EditKelasSiswa::route('/{record}/edit'),
        ];
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

    public function update(Request $request, KelasSiswa $kelasSiswa)
    {
        try {
            // Code that's causing the error
        } catch (Exception $e) {
            return response()->json(['error' => 'Gagal memproses perpindahan kelas, kelas sudah penuh'], 422);
        }

        return parent::update($request, $kelasSiswa);
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
            ->whereNotIn('id', function ($subQuery) use ($periodeId, $currentRecord) {
                $subQuery->select('siswa_id')
                    ->from('kelas_siswas')
                    ->where('periode_id', $periodeId)
                    ->where('status', 'aktif');

                // Exclude current record's siswa_id in edit mode by using whereRaw with OR condition
                if ($currentRecord) {
                    $subQuery->where(function ($query) use ($currentRecord) {
                        $query->where('siswa_id', '!=', $currentRecord->siswa_id)
                        ->orWhereNull('siswa_id');
                    });
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
            ->whereNotIn('id', function ($subQuery) use ($periodeId, $currentRecord) {
                $subQuery->select('siswa_id')
                    ->from('kelas_siswas')
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
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Admin can see all kelas siswa
        if (Auth::user()?->isAdmin()) {
            return $query->with(['siswa', 'kelas', 'kelas.guru', 'kelas.periode']);
        }

        // If somehow a non-admin accesses this, return empty query
        return $query->whereRaw('1 = 0');
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


    public static function getNavigationBadge(): ?string
    {
        if (!Auth::user()?->isAdmin()) {
            return null;
        }

        return static::getModel()::count();
    }
}
