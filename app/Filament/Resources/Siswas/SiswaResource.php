<?php

namespace App\Filament\Resources\Siswas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Support\Enums\Size;
use Exception;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Siswas\Pages\ListSiswas;
use App\Filament\Resources\Siswas\Pages\CreateSiswa;
use App\Filament\Resources\Siswas\Pages\EditSiswa;
use App\Filament\Resources\Siswas\Pages\PrintReport;
use Filament\Forms;
use Filament\Tables;
use App\Models\Siswa;
use App\Models\KelasSiswa;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Laravolt\Indonesia\Models\City;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Provinsi;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SiswaResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SiswaResource\RelationManagers;
use App\Imports\SiswaImport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload as FilamentFileUpload;

class SiswaResource extends Resource
{
    protected static ?string $model = Siswa::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Manajemen Siswa';

    protected static ?string $modelLabel = 'Siswa';

    protected static ?string $pluralModelLabel = 'Manajemen Siswa';

    protected static string | \UnitEnum | null $navigationGroup = 'Data Master';

    protected static ?int $navigationSort = 1;

    // Hide navigation for guru if you want
    // public static function shouldRegisterNavigation(): bool
    // {
    //     // Show for admin, hide for guru (optional)
    //     return Auth::user()?->isGuru() ?? false;
    // }

    // // Control access
    // public static function canAccess(): bool
    // {
    //     return Auth::user()?->isGuru() ?? false;
    // }

    // If you want guru to see siswa but not modify
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canEdit($record): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canDelete($record): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Siswa')
                    ->description('Informasi dasar siswa')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('nis')
                                    ->label('Nomor Induk Siswa (NIS)')
                                    ->placeholder('NIS akan dibuat otomatis')
                                    ->default(fn() => Siswa::getNextNIS())
                                    ->required()
                                    ->maxLength(9) // Format: YYYY.XXXX (9 characters)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Format: YYYY.XXXX (contoh: 2526.0001)')
                                    ->suffixAction(
                                        Action::make('generate')
                                            ->icon('heroicon-o-arrow-path')
                                            ->action(function (Set $set) {
                                                $set('nis', Siswa::generateNISByPeriode());
                                            })
                                            ->tooltip('Generate NIS baru')
                                    )
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        // Validate NIS format
                                        if ($state && !preg_match('/^\d{4}\.\d{4}$/', $state)) {
                                            $set('nis', Siswa::generateNISByPeriode());
                                        }
                                    }),

                                Placeholder::make('nis_info')
                                    ->label('Info NIS')
                                    ->content(function (Get $get) {
                                        $nis = $get('nis');
                                        if ($nis && preg_match('/^(\d{4})\.(\d{4})$/', $nis, $matches)) {
                                            $yearCode = $matches[1];
                                            $sequence = $matches[2];
                                            $year1 = '20' . substr($yearCode, 0, 2);
                                            $year2 = '20' . substr($yearCode, 2, 2);
                                            return "Tahun Ajaran: {$year1}/{$year2}, Urutan: {$sequence}";
                                        }
                                        return 'Format: YYYY.XXXX';
                                    })
                                    ->helperText('Informasi tahun ajaran dan urutan siswa'),
                            ]),

                        TextInput::make('nisn')
                            ->label('Nomor Induk Siswa Nasional (NISN)')
                            ->placeholder('Nomor Induk Siswa Nasional (NISN)')
                            ->maxLength(10)
                            ->numeric()
                            ->unique(ignoreRecord: true),

                        TextInput::make('nik')
                            ->label('Nomor Induk Kependudukan (NIK)')
                            ->maxLength(16)
                            ->numeric()
                            ->unique(ignoreRecord: true)
                            ->placeholder('Nomor Induk Kependudukan (NIK)'),

                        TextInput::make('kk')
                            ->label('Nomor Kartu Keluarga (KK)')
                            ->maxLength(16)
                            ->numeric()
                            ->unique(ignoreRecord: true)
                            ->placeholder('Nomor Kartu Keluarga (KK)'),

                        TextInput::make('nama_lengkap')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('tempat_lahir')
                            ->label('Tempat Lahir')
                            ->required()
                            ->maxLength(255),

                        DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->native(false)
                            ->maxDate(now())
                            ->displayFormat('d F Y')
                            ->placeholder('Pilih Tanggal Lahir')
                            ->required(),

                        Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->options([
                                'L' => 'Laki-laki',
                                'P' => 'Perempuan',
                            ])
                            ->placeholder('Pilih Jenis Kelamin')
                            ->native(false)
                            ->required(),

                        Select::make('agama')
                            ->label('Agama')
                            ->options([
                                'Islam' => 'Islam',
                                'Kristen' => 'Kristen',
                                'Katolik' => 'Katolik',
                                'Hindu' => 'Hindu',
                                'Buddha' => 'Buddha',
                                'Konghucu' => 'Konghucu',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->default('Islam')
                            ->placeholder('Pilih Agama')
                            ->native(false)
                            ->required(),
                    ])
                    ->collapsible()
                    ->columns(2),

                Section::make('Data Orang Tua')
                    ->description('Informasi orang tua siswa')
                    ->schema([
                        TextInput::make('nama_ayah')
                            ->label('Nama Ayah')
                            ->maxLength(255),

                        TextInput::make('nama_ibu')
                            ->label('Nama Ibu')
                            ->maxLength(255),

                        Select::make('pekerjaan_ayah')
                            ->label('Pekerjaan Ayah')
                            ->options([
                                'Tidak Bekerja' => 'Tidak Bekerja',
                                'PNS' => 'PNS',
                                'TNI' => 'TNI',
                                'Polri' => 'Polri',
                                'Wiraswasta' => 'Wiraswasta',
                                'Buruh' => 'Buruh',
                                'Petani' => 'Petani',
                                'Pedagang' => 'Pedagang',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->placeholder('Pilih Pekerjaan Ayah')
                            ->native(false),

                        Select::make('pekerjaan_ibu')
                            ->label('Pekerjaan Ibu')
                            ->options([
                                'Tidak Bekerja' => 'Tidak Bekerja',
                                'PNS' => 'PNS',
                                'TNI' => 'TNI',
                                'Polri' => 'Polri',
                                'Wiraswasta' => 'Wiraswasta',
                                'Buruh' => 'Buruh',
                                'Petani' => 'Petani',
                                'Pedagang' => 'Pedagang',
                                'Ibu Rumah Tangga' => 'Ibu Rumah Tangga',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->placeholder('Pilih Pekerjaan Ibu')
                            ->native(false),

                        Select::make('pendidikan_ayah')
                            ->label('Pendidikan Ayah')
                            ->options([
                                'Tidak Sekolah' => 'Tidak Sekolah',
                                'SD/MI' => 'SD/MI',
                                'SMP/MTs' => 'SMP/MTs',
                                'SMA/MA' => 'SMA/MA',
                                'D1' => 'D1',
                                'D2' => 'D2',
                                'D3' => 'D3',
                                'D4' => 'D4',
                                'S1' => 'S1',
                                'S2' => 'S2',
                                'S3' => 'S3',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->placeholder('Pilih Pendidikan Ayah')
                            ->native(false),

                        Select::make('pendidikan_ibu')
                            ->label('Pendidikan Ibu')
                            ->options([
                                'Tidak Sekolah' => 'Tidak Sekolah',
                                'SD/MI' => 'SD/MI',
                                'SMP/MTs' => 'SMP/MTs',
                                'SMA/MA' => 'SMA/MA',
                                'D1' => 'D1',
                                'D2' => 'D2',
                                'D3' => 'D3',
                                'D4' => 'D4',
                                'S1' => 'S1',
                                'S2' => 'S2',
                                'S3' => 'S3',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->placeholder('Pilih Pendidikan Ibu')
                            ->native(false),

                        TextInput::make('telepon')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->numeric()
                            ->maxLength(16),
                    ])
                    ->collapsible()
                    ->columns(2),

                Section::make('Alamat Siswa')
                    ->description('Informasi alamat lengkap siswa')
                    ->schema([
                        Textarea::make('alamat')
                            ->label('Alamat')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan Alamat Siswa')
                            ->rows(3),

                        Select::make('provinsi_id')
                            ->label('Provinsi')
                            ->options(function () {
                                return Provinsi::pluck('name', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn(Set $set) => $set('kota_id', null)),

                        Select::make('kota_id')
                            ->label('Kota/Kabupaten')
                            ->options(function (Get $get) {
                                $provinceId = $get('provinsi_id');
                                if (!$provinceId) {
                                    return [];
                                }
                                $province = Provinsi::find($provinceId);
                                return $province ? $province->cities()->pluck('name', 'id') : [];
                            })
                            ->searchable()
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn(Set $set) => $set('kecamatan_id', null)),

                        Select::make('kecamatan_id')
                            ->label('Kecamatan')
                            ->options(function (Get $get) {
                                $cityId = $get('kota_id');
                                if (!$cityId) {
                                    return [];
                                }
                                $city = City::find($cityId);
                                return $city ? $city->districts()->pluck('name', 'id') : [];
                            })
                            ->searchable()
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn(Set $set) => $set('kelurahan_id', null)),

                        Select::make('kelurahan_id')
                            ->label('Kelurahan/Desa')
                            ->options(function (Get $get) {
                                $districtId = $get('kecamatan_id');
                                if (!$districtId) {
                                    return [];
                                }
                                $district = District::find($districtId);
                                return $district ? $district->villages()->pluck('name', 'id') : [];
                            })
                            ->searchable()
                            ->required()
                            ->native(false),
                    ])
                    ->collapsible()
                    ->columns(2),

                Section::make('Foto Siswa')
                    ->description('Upload foto siswa')
                    ->schema([
                        FilamentFileUpload::make('foto')
                            ->label('Unggah Foto Siswa')
                            ->image()
                            ->directory('siswa')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048)
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '1:1',
                                '4:3',
                                '3:4',
                            ]),
                    ])
                    ->collapsible()
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto')
                    ->label('Foto')
                    ->circular()
                    ->size(60)
                    ->defaultImageUrl(url('/images/default-avatar.png'))
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('NIS berhasil disalin!')
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-m-identification')
                    ->weight('medium'),

                TextColumn::make('nisn')
                    ->label('NISN')
                    ->searchable()
                    ->toggleable()
                    ->copyable()
                    ->copyMessage('NISN berhasil disalin!')
                    ->placeholder('Belum ada NISN')
                    ->badge()
                    ->icon('heroicon-m-identification')
                    ->color('info')
                ->weight('medium'),

                TextColumn::make('nama_lengkap')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->size('sm')
                    ->description(
                        fn(Siswa $record): string =>
                        'NIS: ' . ($record->nis ?? 'N/A')
                    )
                    ->wrap(),

                TextColumn::make('tempat_lahir')
                    ->label('Tempat Lahir')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->limit(20)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) > 20) {
                            return $state;
                        }
                        return null;
                    }),

                TextColumn::make('tanggal_lahir')
                    ->label('Tanggal Lahir')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->icon('heroicon-m-calendar-days')
                    ->description(function (Siswa $record): string {
                        if ($record->tanggal_lahir) {
                            $age = $record->tanggal_lahir->age;
                            return $age . ' tahun';
                        }
                        return '';
                    }),

                TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'L' => 'blue',
                        'P' => 'pink',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'L' => 'heroicon-m-user',
                        'P' => 'heroicon-m-user',
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                        default => 'Tidak Diketahui',
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('agama')
                    ->label('Agama')
                    ->badge()
                    ->color('success')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('nama_ayah')
                    ->label('Nama Ayah')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('Tidak ada data')
                    ->limit(25),

                TextColumn::make('nama_ibu')
                    ->label('Nama Ibu')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('Tidak ada data')
                    ->limit(25),

                TextColumn::make('telepon')
                    ->label('Telepon')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable()
                    ->copyMessage('Nomor telepon berhasil disalin!')
                    ->icon('heroicon-m-phone')
                    ->placeholder('Tidak ada nomor'),

                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) > 30) {
                            return $state;
                        }
                        return null;
                    })
                    ->wrap(),

                TextColumn::make('provinsi.name')
                    ->label('Provinsi')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge()
                    ->color('info')
                    ->placeholder('Tidak ada data'),

                TextColumn::make('kota.name')
                    ->label('Kota/Kabupaten')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge()
                    ->color('warning')
                    ->placeholder('Tidak ada data'),

                TextColumn::make('kecamatan.name')
                    ->label('Kecamatan')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('Tidak ada data'),

                TextColumn::make('kelurahan.name')
                    ->label('Kelurahan/Desa')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('Tidak ada data'),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since()
                    ->icon('heroicon-m-clock'),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since()
                    ->icon('heroicon-m-pencil-square'),
            ])
            ->filters([
                SelectFilter::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ])
                    ->placeholder('Semua Jenis Kelamin')
                    ->native(false),

                SelectFilter::make('agama')
                    ->label('Agama')
                    ->options([
                        'Islam' => 'Islam',
                        'Kristen' => 'Kristen',
                        'Katolik' => 'Katolik',
                        'Hindu' => 'Hindu',
                        'Buddha' => 'Buddha',
                        'Konghucu' => 'Konghucu',
                        'Lainnya' => 'Lainnya',
                    ])
                    ->placeholder('Semua Agama')
                    ->native(false),

                SelectFilter::make('provinsi_id')
                    ->label('Provinsi')
                    ->relationship('provinsi', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('created_from')
                            ->label('Terdaftar dari'),
                        DatePicker::make('created_until')
                            ->label('Terdaftar sampai'),
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
                    }),

                TernaryFilter::make('has_phone')
                    ->label('Memiliki Nomor Telepon')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('telepon'),
                        false: fn(Builder $query) => $query->whereNull('telepon'),
                    ),
            ])
            // Add this to your existing actions array in the table() method (around line 580)

            ->recordActions([

                ActionGroup::make([
                    ViewAction::make()
                        ->color('info'),
                    EditAction::make()
                        ->color('warning')
                        ->visible(fn() => Auth::user()?->isAdmin()),
                    Action::make('print-cover')
                        ->label('Print Cover')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->url(fn(Siswa $record): string => route('siswa.print-cover', $record))
                        ->openUrlInNewTab(),
                    Action::make('print')
                        ->label('Print')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->url(fn(Siswa $record): string => route('siswa.print', $record))
                        ->openUrlInNewTab(),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Data Siswa')
                        ->modalDescription('Apakah Anda yakin ingin menghapus data siswa ini? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->visible(fn() => Auth::user()?->isAdmin()),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size(Size::Small)
                    ->color('info')
                    ->button()
                    ->tooltip('Aksi')
            ])
            ->headerActions([
                Action::make('print-report')
                    ->label('Print Report All')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(route('filament.resources.siswas.print-report'))
                    ->openUrlInNewTab(),


            Action::make('download-template-excel')
                ->label('Download Template Excel')
                ->tooltip('Download Template Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->url('/templates/siswa_import_template.xlsx')
                ->openUrlInNewTab(),


            Action::make('import')
                ->label('Import Data')
                ->modalDescription('Pilih file CSV atau Excel untuk mengimpor data siswa.')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->modalWidth('sm')
                ->schema([
                    FilamentFileUpload::make('file')
                        ->label('File CSV or Excel')
                        ->required()
                        ->acceptedFileTypes([
                            'text/csv',
                            'application/vnd.ms-excel',
                            'text/plain',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel.sheet.macroEnabled.12',
                        ])
                        ->maxSize(10240), // 10MB max
                ])
                ->action(function (array $data) {
                    try {
                        Excel::import(new SiswaImport, $data['file']->getRealPath());

                        Notification::make()
                            ->title('Import berhasil')
                            ->success()
                            ->send();
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Import gagal: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->modalHeading('Import Data Siswa')
                ->modalSubmitActionLabel('Import'),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Data Siswa Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus semua data siswa yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus Semua')
                        ->visible(fn() => Auth::user()?->isAdmin()),

                    BulkAction::make('export')
                        ->label('Export Data')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('success')
                        ->action(function (Collection $records) {
                            // Add your export logic here
                            // For example, you can use Laravel Excel or generate CSV
                            return response()->streamDownload(function () use ($records) {
                                $csv = fopen('php://output', 'w');

                                // Header
                                fputcsv($csv, [
                                    'NIS',
                                    'NISN',
                                    'Nama Lengkap',
                                    'Tempat Lahir',
                                    'Tanggal Lahir',
                                    'Jenis Kelamin',
                                    'Agama',
                                    'Nama Ayah',
                                    'Nama Ibu',
                                    'Telepon',
                                    'Alamat'
                                ]);

                                // Data
                                foreach ($records as $record) {
                                    fputcsv($csv, [
                                        $record->nis,
                                        $record->nisn,
                                        $record->nama_lengkap,
                                        $record->tempat_lahir,
                                        $record->tanggal_lahir?->format('d-m-Y'),
                                        $record->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
                                        $record->agama,
                                        $record->nama_ayah,
                                        $record->nama_ibu,
                                        $record->telepon,
                                        $record->alamat,
                                    ]);
                                }

                                fclose($csv);
                            }, 'data-siswa-' . now()->format('Y-m-d') . '.csv');
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Export Data Siswa')
                        ->modalDescription('Data siswa yang dipilih akan diexport ke file CSV.')
                        ->modalSubmitActionLabel('Export'),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Tambah Siswa Pertama')
                    ->icon('heroicon-m-plus'),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s') // Auto refresh every 30 seconds
            ->deferLoading()
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->extremePaginationLinks()
            ->recordTitleAttribute('nama_lengkap')
            ->searchOnBlur()
            ->emptyStateHeading('Belum ada data siswa')
            ->emptyStateDescription('Mulai dengan menambahkan siswa pertama ke dalam sistem.')
            ->emptyStateIcon('heroicon-o-user-group');
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
            'index' => ListSiswas::route('/'),
            'create' => CreateSiswa::route('/create'),
            // 'view' => Pages\ViewSiswa::route('/{record}'),
            'edit' => EditSiswa::route('/{record}/edit'),
            'print-report' => PrintReport::route('/print-report'),
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
