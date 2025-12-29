<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PindahKelasResource\Pages;
use App\Models\PindahKelas;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\KelasSiswa;
use App\Models\Periode;
use Filament\Forms;
use Filament\Forms\Form;
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
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel = 'Pindah Kelas';
    protected static ?string $modelLabel = 'Pindah Kelas';
    protected static ?string $pluralModelLabel = 'Pindah Kelas';
    protected static ?string $navigationGroup = 'Akademik';
    protected static ?int $navigationSort = 6;

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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pindah Kelas')
                    ->description('Pilih siswa dan kelas tujuan untuk perpindahan')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('siswa_id')
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
                                    ->afterStateUpdated(function (Forms\Set $set, $state) {
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

                                Forms\Components\Select::make('kelas_asal_id')
                                    ->label('Kelas Asal')
                                    ->relationship('kelasAsal', 'nama_kelas')
                                    ->disabled()
                                    ->dehydrated(),

                                Forms\Components\Select::make('kelas_tujuan_id')
                                    ->label('Kelas Tujuan')
                                    ->options(function (Forms\Get $get) {
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
                                    ->disabled(fn(Forms\Get $get) => empty($get('siswa_id')))
                                    ->helperText('Pilih kelas tujuan yang masih memiliki kapasitas'),

                                Forms\Components\Select::make('periode_id')
                                    ->label('Periode')
                                    ->relationship('periode', 'nama_periode')
                                    ->disabled()
                                    ->dehydrated(),

                                Forms\Components\DatePicker::make('tanggal_pindah')
                                    ->label('Tanggal Pindah')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->helperText('Tanggal efektif perpindahan kelas'),

                                Forms\Components\Select::make('status')
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

                        Forms\Components\Textarea::make('alasan_pindah')
                            ->label('Alasan Pindah Kelas')
                            ->required()
                            ->rows(3)
                            ->placeholder('Masukkan alasan perpindahan kelas (contoh: permintaan orang tua, penyesuaian kemampuan, dll)')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan Tambahan')
                            ->rows(3)
                            ->placeholder('Catatan tambahan dari admin (opsional)')
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('user_id')
                            ->default(Auth::id()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('siswa.nama_lengkap')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => 'NIS: ' . ($record->siswa?->nis ?? 'N/A'))
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('kelasAsal.nama_kelas')
                    ->label('Kelas Asal')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                Tables\Columns\IconColumn::make('transfer_arrow')
                    ->label('')
                    ->icon('heroicon-o-arrow-right')
                    ->color('primary')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('kelasTujuan.nama_kelas')
                    ->label('Kelas Tujuan')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_pindah')
                    ->label('Tanggal Pindah')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
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

                Tables\Columns\TextColumn::make('alasan_pindah')
                    ->label('Alasan')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->alasan_pindah)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Diproses Oleh')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('kelas_asal_id')
                    ->label('Kelas Asal')
                    ->relationship('kelasAsal', 'nama_kelas')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('kelas_tujuan_id')
                    ->label('Kelas Tujuan')
                    ->relationship('kelasTujuan', 'nama_kelas')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('periode_id')
                    ->label('Periode')
                    ->relationship('periode', 'nama_periode')
                    ->default(fn() => Periode::where('is_active', true)->value('id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make()
                    ->visible(fn(PindahKelas $record): bool => $record->status === 'pending'),

                Tables\Actions\Action::make('approve')
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

                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('catatan')
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

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Data Pindah Kelas')
                    ->modalDescription('Apakah Anda yakin ingin menghapus data perpindahan kelas ini?')
                    ->modalSubmitActionLabel('Ya, Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_approve')
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

                    Tables\Actions\BulkAction::make('bulk_reject')
                        ->label('Tolak Terpilih')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('catatan')
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

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPindahKelas::route('/'),
            'create' => Pages\CreatePindahKelas::route('/create'),
            'edit' => Pages\EditPindahKelas::route('/{record}/edit'),
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
