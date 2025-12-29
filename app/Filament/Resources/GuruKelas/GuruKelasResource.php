<?php

namespace App\Filament\Resources\GuruKelas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Actions\BulkActionGroup;
use App\Filament\Resources\GuruKelas\Pages\ListGuruKelas;
use Exception;
use Filament\Forms;
use Filament\Tables;
use App\Models\Kelas;
use App\Models\Periode;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\GuruKelasResource\Pages;

class GuruKelasResource extends Resource
{
    protected static ?string $model = Kelas::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Kelas Saya';
    protected static ?string $modelLabel = 'Kelas';
    protected static ?string $pluralModelLabel = 'Kelas Saya';
    protected static string | \UnitEnum | null $navigationGroup = 'Guru';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->isGuru() ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if (!$user || !$user->isGuru()) {
            return $query->whereRaw('1 = 0');
        }

        // Get guru profile
        $guru = $user->guru;

        if (!$guru) {
            Log::warning('No guru profile found for user', ['user_id' => $user->id]);
            return $query->whereRaw('1 = 0');
        }

        return $query->where('guru_id', $guru->id)
                    ->with(['periode', 'guru'])
                    ->withCount([
                        'kelasSiswa as siswa_aktif_count' => function (Builder $query) {
                            $query->where('status', 'aktif');
                        }
                    ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kelas')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('nama_kelas')
                                    ->label('Nama Kelas')
                                    ->disabled(),

                                TextInput::make('guru.nama_guru')
                                    ->label('Wali Kelas')
                                    ->disabled(),

                                TextInput::make('periode.nama_periode')
                                    ->label('Periode')
                                    ->disabled(),

                                TextInput::make('status')
                                    ->label('Status')
                                    ->disabled(),
                            ]),
                    ]),

                Section::make('Statistik Siswa')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Placeholder::make('total_siswa')
                                    ->label('Total Siswa')
                                    ->content(fn(?Kelas $record) => self::getActiveStudentCount($record)),

                                Placeholder::make('gender_distribution')
                                    ->label('Distribusi Gender')
                                    ->content(fn(?Kelas $record) => self::getGenderDistribution($record)),

                                Placeholder::make('kapasitas')
                                    ->label('Kapasitas Kelas')
                                    ->content(fn(?Kelas $record) => $record?->kapasitas ? "{$record->kapasitas} siswa" : 'Tidak ditentukan'),
                            ]),
                    ])
                    ->visible(fn(?Kelas $record) => $record !== null),
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

                TextColumn::make('periode.nama_periode')
                    ->label('Periode')
                    ->badge()
                    ->color('success')
                    ->description(fn(Kelas $record): string =>
                        'Tahun: ' . ($record->periode?->tahun_ajaran ?? 'N/A')
                    )
                    ->sortable(),

                TextColumn::make('siswa_aktif_count')
                    ->label('Jumlah Siswa')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn($state) => "{$state} siswa")
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('kapasitas')
                    ->label('Kapasitas')
                    ->formatStateUsing(fn($state) => $state ? "{$state} siswa" : 'Tidak ditentukan')
                    ->color('warning')
                    ->alignCenter()
                    ->placeholder('Tidak ditentukan'),

                TextColumn::make('utilization')
                    ->label('Utilisasi')
                    ->state(function (Kelas $record): string {
                        $siswaCount = $record->siswa_aktif_count ?? 0;
                        $kapasitas = $record->kapasitas;

                        if (!$kapasitas) {
                            return 'N/A';
                        }

                        $percentage = round(($siswaCount / $kapasitas) * 100, 1);
                        return "{$percentage}%";
                    })
                    ->badge()
                    ->color(function (Kelas $record): string {
                        $siswaCount = $record->siswa_aktif_count ?? 0;
                        $kapasitas = $record->kapasitas;

                        if (!$kapasitas) return 'gray';

                        $percentage = ($siswaCount / $kapasitas) * 100;

                        if ($percentage >= 90) return 'danger';
                        if ($percentage >= 75) return 'warning';
                        if ($percentage >= 50) return 'success';
                        return 'info';
                    })
                    ->alignCenter(),

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
            ])
            ->filters([
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
                    ]),

                Filter::make('kapasitas_penuh')
                    ->label('Kapasitas Penuh')
                    ->query(fn (Builder $query): Builder =>
                        $query->whereRaw('(SELECT COUNT(*) FROM kelas_siswas WHERE kelas_id = kelas.id AND status = "aktif") >= kapasitas')
                    )
                    ->toggle(),
            ])
            ->recordActions([
                ActionGroup::make([
                ViewAction::make(),

                Action::make('manage_students')
                    ->label('Kelola Siswa')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->url(
                        fn(Kelas $record): string =>
                        route('filament.admin.resources.guru-siswa-kelas.index', [
                            'kelas' => $record->id
                        ])
                    ),

                Action::make('create_assessment')
                    ->label('Buat Penilaian')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->url(
                        fn(Kelas $record): string =>
                        route('filament.admin.resources.guru-nilais.create', [
                            'kelas_id' => $record->id,
                            'periode_id' => $record->periode_id
                        ])
                    ),

                Action::make('class_report')
                    ->label('Laporan Kelas')
                    ->icon('heroicon-o-document-chart-bar')
                    ->color('warning')
                    ->action(function (Kelas $record) {
                        // You can implement class report generation here
                        Notification::make()
                            ->title('Laporan Kelas')
                            ->body("Generating report for {$record->nama_kelas}")
                            ->info()
                            ->send();
                    }),
                ])->label('Aksi')
                ->icon('heroicon-m-bars-3-center-left')
                ->size('sm')
                ->color('gray')
                ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Add bulk actions if needed
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGuruKelas::route('/'),
        ];
    }

    private static function getActiveStudentCount(?Kelas $record): string
    {
        if (!$record) return '0 siswa';

        try {
            $count = $record->kelasSiswa()->where('status', 'aktif')->count();
            return "{$count} siswa aktif";
        } catch (Exception $e) {
            Log::error('Error getting student count', [
                'kelas_id' => $record->id,
                'error' => $e->getMessage()
            ]);
            return 'Error loading data';
        }
    }

    private static function getGenderDistribution(?Kelas $record): string
    {
        if (!$record) return 'Data tidak tersedia';

        try {
            $stats = $record->kelasSiswa()
                ->where('status', 'aktif')
                ->with('siswa:id,jenis_kelamin')
                ->get()
                ->groupBy('siswa.jenis_kelamin')
                ->map->count();

            $stats = $stats->mapWithKeys(fn($value, $key) => [strtolower($key) => $value]);

            $laki = $stats['laki-laki'] ?? $stats['l'] ?? 0;
            $perempuan = $stats['perempuan'] ?? $stats['p'] ?? 0;

            return "Laki-laki: {$laki} | Perempuan: {$perempuan}";
        } catch (Exception $e) {
            Log::error('Error getting gender distribution', [
                'kelas_id' => $record->id,
                'error' => $e->getMessage()
            ]);
            return 'Error loading data';
        }
    }

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();
        if (!$user || !$user->isGuru() || !$user->guru) {
            return null;
        }

        return static::getModel()::where('guru_id', $user->guru->id)
            ->where('status', 'aktif')
            ->count();
    }
}
