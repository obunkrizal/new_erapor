<?php

namespace App\Filament\Resources\PindahKelas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\PindahKelas\Pages\ListPindahKelas;
use App\Filament\Resources\PindahKelas\Pages\CreatePindahKelas;
use App\Filament\Resources\PindahKelas\Pages\EditPindahKelas;
use App\Filament\Resources\PindahKelasResource\Pages;
use App\Models\PindahKelas;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\KelasSiswa;
use App\Models\Periode;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Exception;

class PindahKelasResource extends Resource
{
    protected static ?string $model = PindahKelas::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel = 'Pindah Kelas';
    protected static ?string $modelLabel = 'Pindah Kelas';
    protected static ?string $pluralModelLabel = 'Pindah Kelas';
    protected static string | \UnitEnum | null $navigationGroup = 'Akademik';
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

    // Main function to process class transfer
    public static function processPindahKelas(PindahKelas $record, string $status, ?string $catatan = null): void
    {
        DB::beginTransaction();

        try {
            // Update status and notes
            $record->update([
                'status' => $status,
                'catatan' => $catatan ?? $record->catatan,
                'user_id' => Auth::id(),
            ]);

            if ($status === 'approved') {
                // Check if target class has capacity
                $kelasTujuan = Kelas::find($record->kelas_tujuan_id);
                $currentStudentCount = KelasSiswa::where('kelas_id', $record->kelas_tujuan_id)
                    ->where('status', 'aktif')
                    ->count();

                if ($kelasTujuan->kapasitas && $currentStudentCount >= $kelasTujuan->kapasitas) {
                    throw new Exception('Kelas tujuan sudah penuh!');
                }

                // Update current active class record to 'pindah'
                KelasSiswa::where('siswa_id', $record->siswa_id)
                    ->where('kelas_id', $record->kelas_asal_id)
                    ->where('status', 'aktif')
                    ->update([
                        'status' => 'pindah',
                        'tanggal_keluar' => $record->tanggal_pindah,
                        'keterangan' => 'Pindah ke ' . $kelasTujuan->nama_kelas . ' - ' . $record->alasan_pindah,
                        'updated_at' => now(),
                    ]);

                // Create new record in target class
                KelasSiswa::create([
                    'siswa_id' => $record->siswa_id,
                    'kelas_id' => $record->kelas_tujuan_id,
                    'periode_id' => $record->periode_id,
                    'status' => 'aktif',
                    'tanggal_masuk' => $record->tanggal_pindah,
                    'keterangan' => 'Pindahan dari ' . $record->kelasAsal->nama_kelas . ' - ' . $record->alasan_pindah,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::commit();

                Notification::make()
                    ->title('Perpindahan Kelas Berhasil')
                    ->body("Siswa {$record->siswa->nama_lengkap} berhasil dipindahkan dari {$record->kelasAsal->nama_kelas} ke {$record->kelasTujuan->nama_kelas}")
                    ->success()
                    ->send();

            } else {
                DB::commit();

                Notification::make()
                    ->title('Perpindahan Kelas Ditolak')
                    ->body("Perpindahan siswa {$record->siswa->nama_lengkap} telah ditolak")
                    ->warning()
                    ->send();
            }

        } catch (Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Error')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pindah Kelas')
                    ->description('Pilih siswa dan kelas tujuan untuk perpindahan')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                Grid::make(2)
                            ->schema([
                    Select::make('siswa_id')
                                    ->label('Siswa')
                                    ->options(function () {
                                        return Siswa::whereHas('kelasSiswa', function ($query) {
                                            $query->where('status', 'aktif');
                                        })->get()->mapWithKeys(function ($siswa) {
                                            $kelasAktif = $siswa->kelasSiswa()
                                                ->where('status', 'aktif')
                                                ->with('kelas')
                                                ->first();

                                            $kelasInfo = $kelasAktif ? ' - ' . $kelasAktif->kelas->nama_kelas : ' - Tidak ada kelas';

                                            return [$siswa->id => $siswa->nama_lengkap . ' (' . ($siswa->nis ?? 'N/A') . ')' . $kelasInfo];
                                        });
                                    })
                                    ->required()
                                    ->searchable()
                                    ->live()
                        ->afterStateUpdated(function (Set $set, $state) {
                                        if ($state) {
                                            $siswa = Siswa::find($state);
                                            $kelasAktif = $siswa->kelasSiswa()
                                                ->where('status', 'aktif')
                                                ->with('kelas')
                                                ->first();

                                            if ($kelasAktif) {
                                                $set('kelas_asal_id', $kelasAktif->kelas_id);
                                                $set('periode_id', $kelasAktif->kelas->periode_id);
                                            }
                                        }
                                    }),

                    Select::make('kelas_asal_id')
                                    ->label('Kelas Asal')
                                    ->relationship('kelasAsal', 'nama_kelas')
                                    ->disabled()
                                    ->dehydrated(),

                    Select::make('kelas_tujuan_id')
                                    ->label('Kelas Tujuan')
                        ->options(function (Get $get) {
                                        $kelasAsalId = $get('kelas_asal_id');
                                        $periodeId = $get('periode_id');

                                        if (!$periodeId) return [];

                                        return Kelas::where('periode_id', $periodeId)
                                            ->where('status', 'aktif')
                                            ->when($kelasAsalId, fn($query) => $query->where('id', '!=', $kelasAsalId))
                                            ->withCount(['kelasSiswa' => fn($query) => $query->where('status', 'aktif')])
                                            ->get()
                                            ->mapWithKeys(function ($kelas) {
                                                $siswaAktif = $kelas->kelas_siswa_count;
                                                $kapasitas = $kelas->kapasitas ?? 0;
                                                $sisa = $kapasitas - $siswaAktif;

                                                $info = " ({$siswaAktif}/{$kapasitas} siswa)";
                                                if ($sisa <= 0) {
                                                    $info .= " - PENUH";
                                                }

                                                return [$kelas->id => $kelas->nama_kelas . $info];
                                            });
                                    })
                                    ->required()
                                    ->searchable()
                        ->disabled(fn(Get $get) => empty($get('siswa_id')))
                                    ->helperText('Pilih kelas tujuan yang masih memiliki kapasitas'),

                    Select::make('periode_id')
                                    ->label('Periode')
                                    ->relationship('periode', 'nama_periode')
                                    ->disabled()
                                    ->dehydrated(),

                    DatePicker::make('tanggal_pindah')
                                    ->label('Tanggal Pindah')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->helperText('Tanggal efektif perpindahan kelas'),

                    Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'approved' => 'Disetujui',
                                        'rejected' => 'Ditolak',
                                    ])
                                    ->default('pending')
                                    ->required()
                                    ->native(false),
                            ]),

                Textarea::make('alasan_pindah')
                            ->label('Alasan Pindah Kelas')
                            ->required()
                            ->rows(3)
                            ->placeholder('Masukkan alasan perpindahan kelas (contoh: permintaan orang tua, penyesuaian kemampuan, dll)')
                            ->columnSpanFull(),

                Textarea::make('catatan')
                            ->label('Catatan Tambahan')
                            ->rows(3)
                            ->placeholder('Catatan tambahan dari admin (opsional)')
                            ->columnSpanFull(),

                Hidden::make('user_id')
                            ->default(Auth::id()),
                    ]),
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

            TextColumn::make('kelasAsal.nama_kelas')
                    ->label('Kelas Asal')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

            IconColumn::make('transfer_arrow')
                    ->label('')
                    ->icon('heroicon-o-arrow-right')
                    ->color('primary')
                    ->alignCenter(),

            TextColumn::make('kelasTujuan.nama_kelas')
                    ->label('Kelas Tujuan')
                    ->badge()
                    ->color('success')
                    ->sortable(),

            TextColumn::make('tanggal_pindah')
                    ->label('Tanggal Pindah')
                    ->date('d M Y')
                    ->sortable(),

            TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => $state,
                    })
                    ->sortable(),

            TextColumn::make('alasan_pindah')
                    ->label('Alasan')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->alasan_pindah)
                    ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('user.name')
                    ->label('Diproses Oleh')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->native(false),

            SelectFilter::make('kelas_asal_id')
                    ->label('Kelas Asal')
                    ->relationship('kelasAsal', 'nama_kelas')
                    ->searchable()
                    ->preload(),

            SelectFilter::make('kelas_tujuan_id')
                    ->label('Kelas Tujuan')
                    ->relationship('kelasTujuan', 'nama_kelas')
                    ->searchable()
                    ->preload(),

            SelectFilter::make('periode_id')
                    ->label('Periode')
                    ->relationship('periode', 'nama_periode')
                    ->default(fn() => Periode::where('is_active', true)->value('id')),
            ])
            ->recordActions([
                ViewAction::make(),

            EditAction::make()
                    ->visible(fn(PindahKelas $record): bool => $record->status === 'pending'),

            Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (PindahKelas $record) {
                        self::processPindahKelas($record, 'approved');
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Pindah Kelas')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui perpindahan kelas ini? Siswa akan dipindahkan ke kelas tujuan.')
                    ->modalSubmitActionLabel('Ya, Setujui')
                    ->visible(fn(PindahKelas $record): bool => $record->status === 'pending'),

            Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                ->schema([
                    Textarea::make('catatan')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3)
                            ->placeholder('Masukkan alasan penolakan'),
                    ])
                    ->action(function (PindahKelas $record, array $data) {
                        self::processPindahKelas($record, 'rejected', $data['catatan']);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Pindah Kelas')
                    ->modalDescription('Apakah Anda yakin ingin menolak perpindahan kelas ini?')
                    ->modalSubmitActionLabel('Ya, Tolak')
                    ->visible(fn(PindahKelas $record): bool => $record->status === 'pending'),

            DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Data Pindah Kelas')
                    ->modalDescription('Apakah Anda yakin ingin menghapus data perpindahan kelas ini?')
                    ->modalSubmitActionLabel('Ya, Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_approve')
                        ->label('Setujui Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $approved = 0;
                            foreach ($records as $record) {
                                if ($record->status === 'pending') {
                                    self::processPindahKelas($record, 'approved');
                                    $approved++;
                                }
                            }

                            Notification::make()
                                ->title("{$approved} perpindahan kelas berhasil disetujui")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Setujui Perpindahan Kelas Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menyetujui semua perpindahan kelas yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Setujui Semua'),

                BulkAction::make('bulk_reject')
                        ->label('Tolak Terpilih')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                    Textarea::make('catatan')
                                ->label('Alasan Penolakan')
                                ->required()
                                ->rows(3)
                                ->placeholder('Masukkan alasan penolakan untuk semua yang dipilih'),
                        ])
                        ->action(function ($records, array $data) {
                            $rejected = 0;
                            foreach ($records as $record) {
                                if ($record->status === 'pending') {
                                    self::processPindahKelas($record, 'rejected', $data['catatan']);
                                    $rejected++;
                                }
                            }

                            Notification::make()
                                ->title("{$rejected} perpindahan kelas berhasil ditolak")
                                ->warning()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Tolak Perpindahan Kelas Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menolak semua perpindahan kelas yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Tolak Semua'),

                DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPindahKelas::route('/'),
            'create' => CreatePindahKelas::route('/create'),
            'edit' => EditPindahKelas::route('/{record}/edit'),
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
