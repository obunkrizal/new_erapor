<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Nilai;
use Filament\Forms\Form;
use App\Models\KelasSiswa;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\GuruNilaiResource\Pages;

class GuruNilaiResource extends Resource
{
    protected static ?string $model = Nilai::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Penilaian Saya';
    protected static ?string $modelLabel = 'Penilaian';
    protected static ?string $pluralModelLabel = 'Penilaian Saya';
    protected static ?string $navigationGroup = 'Guru';
    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && method_exists(Auth::user(), 'isGuru') && Auth::user()->isGuru();
    }

    public static function getNavigationBadge(): ?string
    {
        if (!Auth::check() || !method_exists(Auth::user(), 'isGuru') || !Auth::user()->isGuru()) {
            return null;
        }

        $guru = Auth::user()->guru;
        if (!$guru) {
            return '0';
        }

        // Count total assessments created by current guru
        $totalCount = static::getModel()::where('guru_id', $guru->id)->count();

        return $totalCount > 0 ? (string) $totalCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        if (!Auth::user()?->isGuru()) {
            return null;
        }

        $guru = Auth::user()->guru;
        if (!$guru) {
            return 'gray';
        }

        $totalCount = static::getModel()::where('guru_id', $guru->id)->count();

        // Color coding based on count
        if ($totalCount >= 50) {
            return 'success'; // Green for high productivity
        } elseif ($totalCount >= 20) {
            return 'warning'; // Yellow for moderate
        } elseif ($totalCount >= 1) {
            return 'info'; // Blue for some assessments
        }

        return 'gray'; // Gray for no assessments
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Restrict to assessments created by current guru
        if (Auth::check() && Auth::user()->isGuru()) {
            $guru = Auth::user()->guru;
            if ($guru) {
                $query->where('guru_id', $guru->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query->with(['siswa', 'kelas', 'periode']);
    }

    public static function form(Form $form): Form
    {
        $guru = Auth::user()?->guru;

        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Penilaian')
                    ->schema([
                        Forms\Components\Select::make('kelas_id')
                            ->label('Kelas')
                            ->native(false)
                            ->options(function () use ($guru) {
                                if (!$guru) return [];

                                return $guru->kelas()
                                    ->whereHas('periode', fn($q) => $q->where('is_active', true))
                                    ->where('status', 'aktif')
                                    ->pluck('nama_kelas', 'id');
                            })
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('siswa_id', null);
                            })
                            ->default(fn() => request()->get('kelas_id')),

                        Forms\Components\Select::make('siswa_id')
                            ->label('Siswa')
                            ->options(function (Forms\Get $get) {
                                $kelasId = $get('kelas_id');
                                if (!$kelasId) return [];

                                $kelas = \App\Models\Kelas::find($kelasId);
                                $periodeId = $kelas?->periode_id;

                                // Get siswa_ids who already have nilai for this kelas and periode
                                $excludedSiswaIds = \App\Models\Nilai::where('kelas_id', $kelasId)
                                    ->where('periode_id', $periodeId)
                                    ->pluck('siswa_id')
                                    ->toArray();

                                return KelasSiswa::where('kelas_id', $kelasId)
                                    ->where('status', 'aktif')
                                    ->whereNotIn('siswa_id', $excludedSiswaIds)
                                    ->with('siswa')
                                    ->get()
                                    ->mapWithKeys(function ($kelasSiswa) {
                                        $siswa = $kelasSiswa->siswa;
                                        if ($siswa) {
                                            return [$siswa->id => $siswa->nama_lengkap . ' (' . ($siswa->nis ?? 'N/A') . ')'];
                                        }
                                        return [];
                                    });
                            })
                            ->required()
                            ->searchable()
                            ->disabled(fn(Forms\Get $get) => empty($get('kelas_id')))
                            ->default(fn() => request()->get('siswa_id'))
                            ->live(),

                        Forms\Components\Hidden::make('guru_id')
                            ->default($guru?->id),

                        Forms\Components\Hidden::make('periode_id')
                            ->default(function (Forms\Get $get) {
                                $kelasId = $get('kelas_id');
                                if ($kelasId) {
                                    $kelas = \App\Models\Kelas::find($kelasId);
                                    return $kelas?->periode_id;
                                }
                                return null;
                            }),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Nilai Agama dan Budi Pekerti')
                    ->schema([
                        Forms\Components\Textarea::make('nilai_agama')
                            ->label('Nilai Agama dan Budi Pekerti')
                            ->rows(4)
                            ->placeholder('Masukkan penilaian agama dan budi pekerti siswa...')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('fotoAgama')
                            ->label('Foto Dokumentasi Agama')
                            ->image()
                            ->multiple()
                            ->directory('nilai/agama')
                            ->maxFiles(5)
                            ->imageEditor()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Nilai Jati Diri')
                    ->schema([
                        Forms\Components\Textarea::make('nilai_jatiDiri')
                            ->label('Nilai Jati Diri')
                            ->rows(4)
                            ->placeholder('Masukkan penilaian jati diri siswa...')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('fotoJatiDiri')
                            ->label('Foto Dokumentasi Jati Diri')
                            ->image()
                            ->multiple()
                            ->directory('nilai/jati-diri')
                            ->maxFiles(5)
                            ->imageEditor()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Nilai Literasi')
                    ->schema([
                        Forms\Components\Textarea::make('nilai_literasi')
                            ->label('Nilai Dasar-Dasar Literasi, Matematika, Sains, Rekayasa, Teknologi, dan Seni')
                            ->rows(4)
                            ->placeholder('Masukkan penilaian literasi siswa...')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('fotoLiterasi')
                            ->label('Foto Dokumentasi Literasi')
                            ->image()
                            ->multiple()
                            ->directory('nilai/literasi')
                            ->maxFiles(5)
                            ->imageEditor()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Narasi Pembelajaran')
                    ->schema([
                        Forms\Components\Textarea::make('nilai_narasi')
                            ->label('Narasi Pembelajaran')
                            ->rows(4)
                            ->placeholder('Masukkan narasi pembelajaran siswa...')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('fotoNarasi')
                            ->label('Foto Dokumentasi Narasi')
                            ->image()
                            ->multiple()
                            ->directory('nilai/narasi')
                            ->maxFiles(5)
                            ->imageEditor()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Refleksi Guru')
                    ->schema([
                        Forms\Components\Textarea::make('refleksi_guru')
                            ->label('Informasi Perkembangan Anak Didik')
                            ->rows(4)
                            ->placeholder('Masukkan refleksi dan informasi perkembangan anak didik...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Student Information Section
                Tables\Columns\ImageColumn::make('siswa.foto')
                    ->label('Foto')
                    ->circular()
                    ->size(60)
                    ->defaultImageUrl(url('/images/default-avatar.png'))
                    ->extraAttributes(['class' => 'shadow-md'])
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('siswa.nama_lengkap')
                    ->label('Data Siswa')
                    ->searchable(['siswa.nama_lengkap', 'siswa.name', 'siswa.nis'])
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->color('primary')
                    ->formatStateUsing(function (Nilai $record): string {
                        return $record->siswa?->nama_lengkap ?? $record->siswa?->name ?? 'N/A';
                    })
                    ->description(function (Nilai $record): string {
                        $nis = $record->siswa?->nis ?? 'N/A';
                        $kelas = $record->kelas?->nama_kelas ?? 'N/A';
                        return "NIS: {$nis} • Kelas: {$kelas}";
                    })
                    ->wrap(),



                Tables\Columns\TextColumn::make('guru.nama_guru')
                    ->label('Guru Pengajar')
                    ->searchable(['guru.nama_guru', 'guru.name'])
                    ->sortable()
                    ->formatStateUsing(function (Nilai $record): string {
                        return $record->guru?->nama_guru ?? $record->guru?->name ?? 'N/A';
                    })
                ->description(function (Nilai $record): string {
                    $tahun = $record->periode?->tahun_ajaran ?? 'N/A';
                    $semester = $record->periode?->semester ?? 'N/A';
                    return "TA: {$tahun} • Semester: " . ucfirst($semester);
                })
                    ->icon('heroicon-m-user')
                    ->color('info')
                    ->toggleable(),
            Tables\Columns\TextColumn::make('nilai_icons')
                ->label('Status Nilai')
                ->tooltip('Jika Ada Tanda Silang Mohon dilengkapi dulu')
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
                ->sortable(),

            Tables\Columns\TextColumn::make('photo_summary')
                ->label('Dokumentasi')
                ->getStateUsing(function (Nilai $record): string {
                    try {
                        $fields = ['fotoAgama', 'fotoJatiDiri', 'fotoLiterasi', 'fotoNarasi'];
                        $photoDetails = [];
                        $totalCount = 0;

                        foreach ($fields as $field) {
                            $images = $record->getImageUrls($field);
                            $count = count($images);
                            $totalCount += $count;

                            if ($count > 0) {
                                $label = match ($field) {
                                    'fotoAgama' => 'Agama',
                                    'fotoJatiDiri' => 'Jati Diri',
                                    'fotoLiterasi' => 'Literasi',
                                    'fotoNarasi' => 'Narasi',
                                    default => $field
                                };
                                $photoDetails[] = "{$label}: {$count}";
                            }
                        }

                        if ($totalCount === 0) {
                            return 'Tidak ada foto';
                        }

                        return "{$totalCount} foto total";
                    } catch (\Exception $e) {
                        Log::error("Error getting photo summary: " . $e->getMessage());
                        return 'Error loading photos';
                    }
                })
                ->badge()
                ->color(function (string $state): string {
                    if (str_contains($state, 'Tidak ada') || str_contains($state, 'Error')) {
                        return 'gray';
                    }

                    $count = (int) explode(' ', $state)[0];
                    return match (true) {
                        $count >= 8 => 'success',
                        $count >= 4 => 'info',
                        $count >= 1 => 'warning',
                        default => 'gray'
                    };
                })
                ->icon(function (string $state): string {
                    return str_contains($state, 'Tidak ada') ? 'heroicon-m-photo' : 'heroicon-m-camera';
                })
                ->tooltip(function (Nilai $record): string {
                    try {
                        $fields = ['fotoAgama', 'fotoJatiDiri', 'fotoLiterasi', 'fotoNarasi'];
                        $details = [];

                        foreach ($fields as $field) {
                            $images = $record->getImageUrls($field);
                            $count = count($images);

                            if ($count > 0) {
                                $label = match ($field) {
                                    'fotoAgama' => 'Agama',
                                    'fotoJatiDiri' => 'Jati Diri',
                                    'fotoLiterasi' => 'Literasi',
                                    'fotoNarasi' => 'Narasi',
                                    default => $field
                                };
                                $details[] = "{$label}: {$count} foto";
                            }
                        }

                        return $details ? implode(' | ', $details) : 'Tidak ada dokumentasi foto';
                    } catch (\Exception $e) {
                        return 'Error loading photo details';
                    }
                })
                ->toggleable(),
            // Nilai Section - Grouped Layout
            Tables\Columns\TextColumn::make('status_check')
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
                    } catch (\Exception $e) {
                        return 'Error: ' . $e->getMessage();
                    }
                })
                ->html()
                ->wrap()
                ->sortable(),
                // Tables\Columns\TextColumn::make('nilai_agama')
                //     ->label('Agama')
                //     ->badge()
                //     ->color(fn(?string $state): string => self::getNilaiColor($state))
                //     ->formatStateUsing(fn(?string $state): string => $state ?? '-')
                //     ->alignCenter(),

                // Tables\Columns\TextColumn::make('nilai_jatiDiri')
                //     ->label('Jati Diri')
                //     ->badge()
                //     ->color(fn(?string $state): string => self::getNilaiColor($state))
                //     ->formatStateUsing(fn(?string $state): string => $state ?? '-')
                //     ->alignCenter(),

                // Tables\Columns\TextColumn::make('nilai_literasi')
                //     ->label('Literasi')
                //     ->badge()
                //     ->color(fn(?string $state): string => self::getNilaiColor($state))
                //     ->formatStateUsing(fn(?string $state): string => $state ?? '-')
                //     ->alignCenter(),

                // Tables\Columns\TextColumn::make('nilai_narasi')
                //     ->label('Narasi')
                //     ->badge()
                //     ->color(fn(?string $state): string => self::getNilaiColor($state))
                //     ->formatStateUsing(fn(?string $state): string => $state ?? '-')
                //     ->alignCenter(),

                // // Refleksi Section
                // Tables\Columns\TextColumn::make('refleksi_guru')
                //     ->label('Refleksi Guru')
                //     ->limit(40)
                //     ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                //         $state = $column->getState();
                //         return !empty($state) && strlen($state) > 40 ? $state : null;
                //     })
                //     ->placeholder('Belum ada refleksi')
                //     ->wrap()
                //     ->icon('heroicon-m-chat-bubble-left-ellipsis')
                //     ->toggleable(isToggledHiddenByDefault: true),


                // Documentation Section with better error handling
                // Replace the ViewColumn with this TextColumn approach:


                // Timestamps
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->color('gray')
                    ->icon('heroicon-m-clock')
                    ->since()
                    ->tooltip(fn(Nilai $record): string => $record->created_at?->format('d F Y, H:i') ?? '')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->color('gray')
                    ->icon('heroicon-m-pencil-square')
                    ->since()
                    ->tooltip(fn(Nilai $record): string => $record->updated_at?->format('d F Y, H:i') ?? '')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('periode_id')
                    ->label('Periode')
                    ->relationship('periode', 'nama_periode')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->placeholder('Semua Periode'),

                Tables\Filters\SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->relationship('kelas', 'nama_kelas')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->placeholder('Semua Kelas'),

                Tables\Filters\SelectFilter::make('guru_id')
                    ->label('Guru')
                    ->relationship('guru', 'nama_guru')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->placeholder('Semua Guru'),

                Tables\Filters\Filter::make('has_photos')
                    ->label('Ada Dokumentasi')
                    ->query(function (Builder $query): Builder {
                        return $query->where(function ($q) {
                            $q->whereNotNull('fotoAgama')
                                ->orWhereNotNull('fotoJatiDiri')
                                ->orWhereNotNull('fotoLiterasi')
                                ->orWhereNotNull('fotoNarasi');
                        });
                    })
                    ->toggle(),

                Tables\Filters\Filter::make('complete_nilai')
                    ->label('Nilai Lengkap')
                    ->query(function (Builder $query): Builder {
                        return $query->whereNotNull('nilai_agama')
                            ->whereNotNull('nilai_jatiDiri')
                            ->whereNotNull('nilai_literasi')
                            ->whereNotNull('nilai_narasi');
                    })
                    ->toggle(),

                Tables\Filters\Filter::make('created_date')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dari Tanggal')
                            ->native(false),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Sampai Tanggal')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = 'Dari: ' . \Carbon\Carbon::parse($data['created_from'])->format('d M Y');
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = 'Sampai: ' . \Carbon\Carbon::parse($data['created_until'])->format('d M Y');
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('print')
                        ->label('Print Raport')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->url(fn(Nilai $record): string => route('nilai.print', ['nilai' => $record]))
                        ->openUrlInNewTab(),


                    Tables\Actions\Action::make('view_photos')
                        ->label('Lihat Foto')
                        ->icon('heroicon-o-photo')
                        ->color('info')
                        ->modalHeading('Dokumentasi Pembelajaran')
                        ->modalContent(fn(Nilai $record) => view('filament.modals.photo-gallery', [
                            'record' => $record,
                            'fields' => ['fotoAgama', 'fotoJatiDiri', 'fotoLiterasi', 'fotoNarasi']
                        ]))
                        ->modalWidth('5xl')
                        ->visible(function (Nilai $record): bool {
                            $fields = ['fotoAgama', 'fotoJatiDiri', 'fotoLiterasi', 'fotoNarasi'];
                            foreach ($fields as $field) {
                                if (!empty($record->getImageUrls($field))) {
                                    return true;
                                }
                            }
                            return false;
                        }),

                    Tables\Actions\ViewAction::make()
                        ->color('gray'),

                    Tables\Actions\EditAction::make()
                        ->color('warning'),

                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('Apakah Anda yakin ingin menghapus data nilai ini? Tindakan ini tidak dapat dibatalkan.')
                        ->modalHeading('Hapus Data Nilai')
                        ->color('danger'),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
            ])
            ->headerActions([
            Tables\Actions\CreateAction::make()
                ->label('Buat Penilaian Baru')
                ->icon('heroicon-m-document-plus'),
                Tables\Actions\Action::make('export_all')
                    ->label('Export Semua Data')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function () {
                        // Implement export functionality
                        \Filament\Notifications\Notification::make()
                            ->title('Export Data')
                            ->body('Fitur export akan segera tersedia')
                            ->info()
                            ->send();
                    }),
                Tables\Actions\Action::make('refresh')
                    ->label('Refresh')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('Apakah Anda yakin ingin menghapus semua data nilai yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalHeading('Hapus Data Nilai Terpilih'),

                    Tables\Actions\BulkAction::make('export_excel')
                        ->label('Export Excel')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->action(function ($records) {
                            // Implement Excel export functionality
                            return response()->streamDownload(function () use ($records) {
                                echo self::generateCSVContent($records);
                            }, 'nilai-export-' . now()->format('Y-m-d-His') . '.csv');
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->extremePaginationLinks()
            ->poll('30s'); // Auto-refresh every 30 seconds
    }

    /**
     * Get color for nilai badges based on value
     */
    private static function getNilaiColor(?string $state): string
    {
        if (empty($state)) {
            return 'gray';
        }

        // Convert to uppercase for consistency
        $nilai = strtoupper(trim($state));

        return match ($nilai) {
            'A', 'SANGAT BAIK' => 'success',
            'B', 'BAIK' => 'info',
            'C', 'CUKUP' => 'warning',
            'D', 'KURANG' => 'danger',
            default => 'gray',
        };
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuruNilais::route('/'),
            'create' => Pages\CreateGuruNilai::route('/create'),
            'edit' => Pages\EditGuruNilai::route('/{record}/edit'),
        ];
    }
}
