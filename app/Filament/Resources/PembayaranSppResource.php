<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Periode;
use App\Models\HargaSpp;
use Filament\Forms\Form;
use App\Models\KelasSiswa;
use Filament\Tables\Table;
use App\Models\PembayaranSpp;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Actions\Action;
use App\Filament\Resources\PembayaranSppResource\Pages;
use Filament\Tables\Actions\Action as ActionsAction;
use Filament\Tables\Actions\Modal\Actions\Action as ModalActionsAction;

class PembayaranSppResource extends Resource
{
    protected static ?string $model = PembayaranSpp::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Pembayaran SPP';

    protected static ?string $modelLabel = 'Pembayaran SPP';

    protected static ?string $pluralModelLabel = 'Pembayaran SPP';

    protected static ?string $navigationGroup = 'Transaksi SPP';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        return $user && $user->isAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Periode & Kelas')
                    ->description('Pilih periode dan kelas untuk mengelola siswa')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        Forms\Components\Select::make('periode_id')
                            ->label('Periode')
                            ->options(self::getPeriodeOptions())
                            ->default(self::getActivePeriodeId())
                            ->afterStateUpdated(function (callable $set, $state) {
                                if ($state === null) {
                                    $set('kelas_id', null);
                                    $set('siswa_id', null);
                                    $set('amount', null);
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->helperText('Pilih periode akademik yang aktif'),

                        Forms\Components\Select::make('kelas_id')
                            ->label('Kelas')
                            ->options(function (callable $get) {
                                $periodeId = $get('periode_id');
                                return self::getKelasOptions($periodeId);
                            })
                            ->afterStateUpdated(function (callable $set, $state, callable $get) {
                                if ($state === null) {
                                    $set('siswa_id', null);
                                    $set('amount', null);
                                } else {
                                    // Auto-populate amount based on selected class
                                    $periodeId = $get('periode_id');
                                    $harga = self::getHargaSppForKelas($periodeId, $state);
                                    if ($harga > 0) {
                                        $set('amount', $harga);
                                    }
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->disabled(function (callable $get) {
                                $periodeId = $get('periode_id');
                                return empty($periodeId);
                            })
                            ->helperText('Pilih kelas berdasarkan periode yang dipilih'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Informasi Siswa')
                    ->schema([
                        Forms\Components\Select::make('siswa_id')
                            ->label('Siswa')
                            ->options(function (Forms\Get $get) {
                                $kelasId = $get('kelas_id');
                                if (!$kelasId) return [];

                                return KelasSiswa::where('kelas_id', $kelasId)
                                    ->where('status', 'aktif')
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
                            ->disabled(fn(Forms\Get $get) => empty($get('kelas_id'))),

                        TextInput::make('no_inv')
                            ->label('Nomor Invoice')
                            ->default(fn(Forms\Get $get) => $get('no_inv') ?? 'INV-' . now()->format('Ymd') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT))
                            ->columnSpan(2)
                            ->suffixAction(
                                Action::make('refreshInvoice')
                                    ->label('Generate')
                                    ->icon('heroicon-o-arrow-path')
                                    ->action(function (callable $set) {
                                        $newInvoice = 'INV-' . now()->format('Ymd') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
                                        $set('no_inv', $newInvoice);
                                    })
                            ),
                    ]),
            // Add section to show current SPP prices for reference
            Section::make('Referensi Harga SPP')
                ->schema([
                    Forms\Components\Placeholder::make('harga_spp_info')
                        ->label('')
                        ->content(function (Forms\Get $get) {
                            $periodeId = $get('periode_id');
                            if (!$periodeId) {
                                return 'Pilih periode untuk melihat daftar harga SPP';
                            }

                            $hargaSpp = HargaSpp::where('periode_id', $periodeId)
                                ->where('is_active', true)
                                ->with(['kelas'])
                                ->get();

                            if ($hargaSpp->isEmpty()) {
                                return 'Belum ada harga SPP yang diset untuk periode ini';
                            }

                            $html = '<div class="space-y-2">';
                            $html .= '<h4 class="text-sm font-semibold">Daftar Harga SPP:</h4>';

                            foreach ($hargaSpp as $harga) {
                                $kelasInfo = $harga->kelas ? $harga->kelas->nama_kelas : 'Tingkat ' . $harga->tingkat_kelas;
                                $formattedPrice = 'Rp ' . number_format($harga->harga, 0, ',', '.');
                                $html .= "<div class='flex justify-between text-sm'>";
                                $html .= "<span>{$kelasInfo}</span>";
                                $html .= "<span class='font-medium'>{$formattedPrice}</span>";
                                $html .= "</div>";
                            }

                            $html .= '</div>';
                            return new \Illuminate\Support\HtmlString($html);
                        })
                ])
                ->collapsible()
                ->collapsed(),
                Section::make('Informasi Pembayaran')
                    ->schema([
                        TextInput::make('amount')
                            ->label('Jumlah Pembayaran')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->live()
                            ->afterStateHydrated(function (Forms\Components\TextInput $component, Forms\Get $get, ?Model $record) {
                                // Only auto-populate for new records
                                if (!$record) {
                                    $kelasId = $get('kelas_id');
                                    $periodeId = $get('periode_id');

                                    if ($kelasId && $periodeId) {
                                        $harga = self::getHargaSppForKelas($periodeId, $kelasId);
                                        if ($harga > 0) {
                                            $component->state($harga);
                                        }
                                    }
                                }
                            })
                            ->suffixAction(
                                Action::make('loadHargaSpp')
                                    ->label('Load Harga')
                                    ->icon('heroicon-o-currency-dollar')
                                    ->action(function (callable $set, callable $get) {
                                        $kelasId = $get('kelas_id');
                                        $periodeId = $get('periode_id');

                                        if ($kelasId && $periodeId) {
                                            $harga = self::getHargaSppForKelas($periodeId, $kelasId);
                                            if ($harga > 0) {
                                                $set('amount', $harga);
                                            } else {
                                                // Show notification if no price found
                                                \Filament\Notifications\Notification::make()
                                                    ->title('Harga SPP tidak ditemukan')
                                                    ->body('Silakan set harga SPP untuk kelas ini di menu Harga SPP')
                                                    ->warning()
                                                    ->send();
                                            }
                                        }
                                    })
                            )
                            ->columnSpanFull()
                            ->helperText(function (Forms\Get $get) {
                                $kelasId = $get('kelas_id');
                                $periodeId = $get('periode_id');

                                if ($kelasId && $periodeId) {
                                    $harga = self::getHargaSppForKelas($periodeId, $kelasId);
                                    if ($harga > 0) {
                                        return 'Harga SPP untuk kelas ini: Rp ' . number_format($harga, 0, ',', '.');
                                    } else {
                                        return 'Harga SPP belum diset untuk kelas ini';
                                    }
                                }
                                return 'Pilih periode dan kelas untuk melihat harga SPP';
                            }),
                        DatePicker::make('payment_date')
                            ->label('Tanggal Pembayaran')
                            ->native(false)
                            ->timezone('Asia/Jakarta')
                            ->default(now())
                            ->required(),

                        Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->options([
                                'cash' => 'Cash',
                                'transfer' => 'Transfer',
                            ])
                            ->native(false)
                            ->default('cash')
                            ->required(),

                        Select::make('month')
                            ->label('Untuk Bulan')
                            ->options([
                                'january' => 'Januari',
                                'february' => 'Februari',
                                'march' => 'Maret',
                                'april' => 'April',
                                'may' => 'Mei',
                                'june' => 'Juni',
                                'july' => 'Juli',
                                'august' => 'Agustus',
                                'september' => 'September',
                                'october' => 'Oktober',
                                'november' => 'November',
                                'december' => 'Desember',
                            ])
                            ->native(false)
                            ->default(strtolower(now()->format('F')))
                            ->required(),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                            ])
                            ->native(false)
                            ->default('paid')
                            ->required(),


                        Textarea::make('catatan')
                            ->label('Keterangan atau Catatan')
                            ->columnSpanFull()
                            ->placeholder('Catatan tambahan untuk pembayaran ini'),
                    ])
                    ->columns(4)
                    ->collapsible(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_inv')
                    ->label('No. Invoice')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('siswa.nama_lengkap')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('periode.nama_periode')
                    ->label('Periode')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Jumlah Pembayaran')
                    ->money('idr', true)
                    ->sortable(),

                TextColumn::make('month')
                    ->label('Bulan')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'january' => 'Januari',
                        'february' => 'Februari',
                        'march' => 'Maret',
                        'april' => 'April',
                        'may' => 'Mei',
                        'june' => 'Juni',
                        'july' => 'Juli',
                        'august' => 'Agustus',
                        'september' => 'September',
                        'october' => 'Oktober',
                        'november' => 'November',
                        'december' => 'Desember',
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'january',
                        'secondary' => 'february',
                        'success' => 'march',
                        'warning' => 'april',
                        'danger' => 'may',
                        'info' => 'june',
                    ])
                    ->icon('heroicon-o-calendar')
                    ->sortable(),


                TextColumn::make('payment_date')
                    ->label('Tanggal Pembayaran')
                    ->date('d F Y')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'cash' => 'Cash',
                        'transfer' => 'Transfer',
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'cash',
                        'secondary' => 'transfer',
                    ])
                    ->icon(fn($state) => match ($state) {
                        'cash' => 'heroicon-o-banknotes',
                        'transfer' => 'heroicon-o-banknotes',
                        default => null,
                    })
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                    ])
                    ->icon(fn($state) => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'paid' => 'heroicon-o-check-circle',
                        'failed' => 'heroicon-o-x-circle',
                        default => null,
                    })
                    ->tooltip(fn($state) => match ($state) {
                        'pending' => 'Pembayaran sedang diproses',
                        'paid' => 'Pembayaran sudah lunas',
                        'failed' => 'Pembayaran gagal',
                        default => '',
                    })
                    ->extraAttributes(['class' => 'font-semibold'])
                    ->sortable(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Pembayaran')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ])
                    ->placeholder('Semua Status')
                    ->native(false),

                Tables\Filters\SelectFilter::make('periode_id')
                    ->label('Periode')
                    ->relationship('periode', 'nama_periode')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('siswa_id')
                    ->label('Siswa')
                    ->relationship('siswa', 'nama_lengkap')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->color('info'),

                    Tables\Actions\Action::make('printInvoice')
                        ->label('Cetak Struk')
                        ->icon('heroicon-o-printer')
                        ->url(fn($record) => route('pembayaran-spp.print-invoice', $record->id))
                        ->openUrlInNewTab()
                        ->color('secondary'),

                    Tables\Actions\EditAction::make()
                        ->color('warning'),

                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Data Pembayaran SPP')
                        ->modalDescription('Apakah Anda yakin ingin menghapus data pembayaran ini? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                ])
                    ->label('Aksi')
            ])
            ->headerActions([
                Tables\Actions\Action::make('print-report')
                    ->label('Print Report All')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                ->url(route('pembayaran-spp.print-laporan'))
                    ->openUrlInNewTab(),
                  Tables\Actions\Action::make('refresh')
                    ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Data Pembayaran SPP Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus semua data pembayaran yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus Semua'),
                ]),
            ])
            ->defaultSort('payment_date', 'desc')
            ->paginated([10, 25, 50, 100])
            ->striped()
            ->searchable()
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPembayaranSpps::route('/'),
            'create' => Pages\CreatePembayaranSpp::route('/create'),
            'edit' => Pages\EditPembayaranSpp::route('/{record}/edit'),
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
        } catch (\Exception $e) {
            Log::error('Failed to load kelas options: ' . $e->getMessage());
            return [];
        }
    }

    private static function getAvailableSiswaOptions(?int $kelasId, ?int $periodeId, ?Model $currentRecord = null): array
    {
        return self::getAllSiswaOptions();
    }

    private static function getAllSiswaOptions(): array
    {
        return Siswa::orderBy('nama_lengkap')
            ->get()
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
        $query = Siswa::query()
            ->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
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

    /**
     * Get SPP price for a specific class and period
     *
     * @param int|null $periodeId
     * @param int|null $kelasId
     * @return float
     */
    private static function getHargaSppForKelas(?int $periodeId, ?int $kelasId): float
    {
        if (!$periodeId || !$kelasId) {
            return 0;
        }

        try {
            // First, try to find SPP price by specific class ID
            $hargaSpp = HargaSpp::where('periode_id', $periodeId)
                ->where('kelas_id', $kelasId)
                ->where('is_active', true)
                ->first();

            if ($hargaSpp) {
                return (float) $hargaSpp->harga;
            }

            // If not found by class ID, try to find by tingkat_kelas
            $kelas = Kelas::find($kelasId);
            if ($kelas && $kelas->tingkat_kelas) {
                $hargaSpp = HargaSpp::where('periode_id', $periodeId)
                    ->where('tingkat_kelas', $kelas->tingkat_kelas)
                    ->where('is_active', true)
                    ->first();

                if ($hargaSpp) {
                    return (float) $hargaSpp->harga;
                }
            }

            return 0;
        } catch (\Exception $e) {
            Log::error('Failed to get SPP price for class: ' . $e->getMessage(), [
                'periode_id' => $periodeId,
                'kelas_id' => $kelasId
            ]);
            return 0;
        }
    }

    /**
     * Get all active SPP prices for a specific period
     *
     * @param int|null $periodeId
     * @return array
     */
    private static function getAllHargaSppForPeriode(?int $periodeId): array
    {
        if (!$periodeId) {
            return [];
        }

        try {
            return HargaSpp::where('periode_id', $periodeId)
                ->where('is_active', true)
                ->with(['kelas'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to get all SPP prices for period: ' . $e->getMessage(), [
                'periode_id' => $periodeId
            ]);
            return [];
        }
    }

    /**
     * Check if SPP price exists for a class and period
     *
     * @param int|null $periodeId
     * @param int|null $kelasId
     * @return bool
     */
    private static function hasSppPriceForKelas(?int $periodeId, ?int $kelasId): bool
    {
        return self::getHargaSppForKelas($periodeId, $kelasId) > 0;
    }
}
