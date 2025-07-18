<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Guru;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Filament\Tables\Actions\Action;
use Laravolt\Indonesia\Models\City;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\ActionSize;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\GuruResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\GuruResource\RelationManagers;

class GuruResource extends Resource
{
    protected static ?string $model = Guru::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Manajemen Guru';

    protected static ?string $modelLabel = 'Guru';

    protected static ?string $pluralModelLabel = 'Manajemen Guru';

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?int $navigationSort = 2;

    // Hide navigation for guru
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    // Control access - only admin can access
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Akun User')
                    ->description('Buat akun login untuk guru')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Toggle::make('create_user_account')
                            ->label('Buat Akun User')
                            ->helperText('Aktifkan untuk membuat akun login guru')
                            ->live()
                            ->default(false)
                            ->visible(fn (string $operation): bool => $operation === 'create'),

                        TextInput::make('user_email')
                            ->label('Email Login')
                            ->email()
                            ->unique('users', 'email')
                            ->placeholder('email@example.com')
                            ->helperText('Email untuk login ke sistem')
                            ->visible(fn (Forms\Get $get, string $operation): bool =>
                                $operation === 'create' && $get('create_user_account'))
                            ->required(fn (Forms\Get $get, string $operation): bool =>
                                $operation === 'create' && $get('create_user_account')),

                        TextInput::make('user_password')
                            ->label('Password')
                            ->password()
                            ->placeholder('Minimal 8 karakter')
                            ->helperText('Kosongkan untuk generate otomatis')
                            ->visible(fn (Forms\Get $get, string $operation): bool =>
                                $operation === 'create' && $get('create_user_account')),

                        Forms\Components\Placeholder::make('existing_user')
                            ->label('Status Akun')
                            ->content(function (?Guru $record) {
                                if (!$record || !$record->user) {
                                    return 'Belum memiliki akun user';
                                }
                                return "Sudah memiliki akun: {$record->user->email}";
                            })
                            ->visible(fn (string $operation): bool => $operation === 'edit'),

                        // Action to create user for existing guru
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('create_user')
                                ->label('Buat Akun User')
                                ->icon('heroicon-o-user-plus')
                                ->color('success')
                                ->visible(fn (?Guru $record): bool =>
                                    $record && !$record->user_id)
                                ->form([
                                    TextInput::make('email')
                                        ->label('Email')
                                        ->email()
                                        ->required()
                                        ->unique('users', 'email')
                                        ->default(fn (?Guru $record) => $record?->nama_guru ?
                                            strtolower(str_replace(' ', '', $record->nama_guru)) . '@school.com' : ''),

                                    TextInput::make('password')
                                        ->label('Password')
                                        ->password()
                                        ->helperText('Kosongkan untuk generate otomatis'),
                                ])
                                ->action(function (array $data, ?Guru $record) {
                                    if (!$record) return;

                                    $password = $data['password'] ?: Str::random(8);

                                    $user = User::create([
                                        'name' => $record->nama_guru,
                                        'email' => $data['email'],
                                        'password' => Hash::make($password),
                                        'role' => 'guru',
                                        'email_verified_at' => now(),
                                    ]);

                                    $record->update(['user_id' => $user->id]);

                                    Notification::make()
                                        ->title('Akun user berhasil dibuat!')
                                        ->body("Email: {$data['email']}, Password: {$password}")
                                        ->success()
                                        ->persistent()
                                        ->send();
                                })
                        ])
                        ->visible(fn (string $operation): bool => $operation === 'edit'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Informasi Guru')
                    ->description('Data dasar guru dan informasi kepegawaian')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nama_guru')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama lengkap guru'),

                        TextInput::make('nip')
                            ->label('NIP')
                            ->placeholder('Nomor Induk Pegawai')
                            ->maxLength(18)
                            ->unique(ignoreRecord: true)
                            ->helperText('18 digit NIP (opsional)'),

                        TextInput::make('nuptk')
                            ->label('NUPTK')
                            ->placeholder('Nomor Unik Pendidik dan Tenaga Kependidikan')
                            ->maxLength(16)
                            ->unique(ignoreRecord: true)
                            ->helperText('16 digit NUPTK (opsional)'),

                        Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->native(false)
                            ->placeholder('Pilih jenis kelamin')
                            ->options([
                                'L' => 'Laki-laki',
                                'P' => 'Perempuan',
                            ])
                            ->required(),

                        Select::make('agama')
                            ->label('Agama')
                            ->native(false)
                            ->placeholder('Pilih agama')
                            ->options([
                                'Islam' => 'Islam',
                                'Kristen' => 'Kristen',
                                'Katolik' => 'Katolik',
                                'Hindu' => 'Hindu',
                                'Budha' => 'Budha',
                                'Konghucu' => 'Konghucu',
                            ])
                            ->default('Islam'),

                        Select::make('jabatan')
                            ->label('Jabatan')
                            ->native(false)
                            ->placeholder('Pilih jabatan')
                            ->options([
                                'Guru Kelas' => 'Guru Kelas',
                                'Guru Mapel' => 'Guru Mapel',
                                'Kepala Sekolah' => 'Kepala Sekolah',
                                'Waka Kurikulum' => 'Waka Kurikulum',
                                'Waka Kesiswaan' => 'Waka Kesiswaan',
                                'Waka Humas' => 'Waka Humas',
                                'Waka Sarana Prasarana' => 'Waka Sarana Prasarana',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->searchable(),

                        Select::make('pendidikan_terakhir')
                            ->label('Pendidikan Terakhir')
                            ->native(false)
                            ->placeholder('Pilih pendidikan terakhir')
                            ->options([
                                'SD' => 'SD', 'SMP' => 'SMP', 'SMA' => 'SMA',
                                'D1' => 'D1', 'D2' => 'D2', 'D3' => 'D3',
                                'S1' => 'S1', 'S2' => 'S2', 'S3' => 'S3',
                            ]),

                        Select::make('status_kepegawaian')
                            ->label('Status Kepegawaian')
                            ->placeholder('Pilih status kepegawaian')
                            ->native(false)
                            ->options([
                                'PNS' => 'PNS',
                                'Non PNS' => 'Non PNS',
                                'Honorer' => 'Honorer',
                                'GTY' => 'GTY (Guru Tetap Yayasan)',
                                'GTT' => 'GTT (Guru Tidak Tetap)',
                            ]),

                        TextInput::make('tempat_lahir')
                            ->label('Tempat Lahir')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan tempat lahir'),

                        DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->native(false)
                            ->maxDate(now()->subYears(17))
                            ->placeholder('Pilih Tanggal Lahir')
                            ->displayFormat('d F Y')
                            ->required(),

                        TextInput::make('telepon')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->maxLength(15)
                            ->placeholder('Contoh: 08123456789'),
                    ]),

                Section::make('Alamat Guru')
                    ->description('Informasi alamat lengkap guru')
                    ->icon('heroicon-o-map')
                    ->schema([
                        Textarea::make('alamat')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->maxLength(500)
                            ->rows(3)
                            ->placeholder('Masukkan alamat lengkap guru')
                            ->columnSpanFull(),

                        Select::make('provinsi_id')
                            ->label('Provinsi')
                            ->placeholder('Pilih Provinsi')
                            ->options(fn () => Province::pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('kota_id', null);
                                $set('kecamatan_id', null);
                                $set('kelurahan_id', null);
                            }),

                        Select::make('kota_id')
                            ->label('Kota/Kabupaten')
                            ->placeholder('Pilih Kota/Kabupaten')
                            ->options(function (Forms\Get $get) {
                                $provinceId = $get('provinsi_id');
                                if (!$provinceId) return [];
                                return Province::find($provinceId)?->cities()->pluck('name', 'id') ?? [];
                            })
                            ->searchable()
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('kecamatan_id', null);
                                $set('kelurahan_id', null);
                            })
                            ->disabled(fn(Forms\Get $get): bool => !$get('provinsi_id')),

                        Select::make('kecamatan_id')
                            ->label('Kecamatan')
                            ->placeholder('Pilih Kecamatan')
                            ->options(function (Forms\Get $get) {
                                $cityId = $get('kota_id');
                                if (!$cityId) return [];
                                return City::find($cityId)?->districts()->pluck('name', 'id') ?? [];
                            })
                            ->searchable()
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('kelurahan_id', null);
                            })
                            ->disabled(fn(Forms\Get $get): bool => !$get('kota_id')),

                        Select::make('kelurahan_id')
                            ->label('Kelurahan/Desa')
                            ->placeholder('Pilih Kelurahan/Desa')
                            ->options(function (Forms\Get $get) {
                                $districtId = $get('kecamatan_id');
                                if (!$districtId) return [];
                                return District::find($districtId)?->villages()->pluck('name', 'id') ?? [];
                            })
                            ->searchable()
                            ->required()
                            ->native(false)
                            ->disabled(fn(Forms\Get $get): bool => !$get('kecamatan_id')),
                    ])
                    ->collapsible()
                    ->columns(2),

                Section::make('Foto Guru')
                    ->description('Upload foto profil guru')
                    ->icon('heroicon-o-camera')
                    ->schema([
                        FileUpload::make('foto')
                            ->label('Foto Profil')
                            ->image()
                            ->directory('guru')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048)
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1', '4:3'])
                            ->helperText('Maksimal 2MB. Format: JPG, PNG, WEBP')
                            ->columnSpanFull(),
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

                TextColumn::make('nama_guru')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->size('sm')
                    ->description(
                        fn(Guru $record): string => ($record->nip ? 'NIP: ' . $record->nip : 'Belum ada NIP')
                    )
                    ->wrap()
                ->badge()
                ->icon('heroicon-m-identification')
                ->color('info')
                ->weight('medium'),

                TextColumn::make('nuptk')
                    ->label('NUPTK')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->copyable()
                    ->copyMessage('NUPTK berhasil disalin!')
                    ->placeholder('Belum ada NUPTK')
                ->badge()
                ->icon('heroicon-m-identification')
                ->color('info')
                ->weight('medium'),

                TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Kepala Sekolah' => 'danger',
                        'Waka Kurikulum', 'Waka Kesiswaan', 'Waka Humas', 'Waka Sarana Prasarana' => 'warning',
                        'Guru Kelas' => 'primary',
                        'Guru Mapel' => 'primary',
                        'Lainnya' => 'secondary',
                        default => 'secondary',
                    })
                    ->icon('heroicon-m-briefcase')
                    ->searchable()
                    ->sortable(),

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
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('telepon')
                    ->label('Nomor Telepon')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('printAllGuru')
                    ->label('Print All Data Guru')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(route('guru.print-report'))
                    ->openUrlInNewTab()
            ])
            ->actions([
                ActionGroup::make([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->color('warning')
                    ->visible(fn() => Auth::user()?->isAdmin()),
                Tables\Actions\Action::make('print')
                    ->label('Print')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn(Guru $record): string => route('guru.print', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Data Siswa')
                    ->modalDescription('Apakah Anda yakin ingin menghapus data siswa ini? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->visible(fn() => Auth::user()?->isAdmin()),
                ])
                ->button()
                ->size(ActionSize::Small)
                ->label('Aksi')
                ->tooltip('Aksi')

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => Auth::user()?->isAdmin()),
                ]),
            ]);
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
            'index' => Pages\ListGurus::route('/'),
            'create' => Pages\CreateGuru::route('/create'),
            // 'view' => Pages\ViewGuru::route('/{record}'),
            'edit' => Pages\EditGuru::route('/{record}/edit'),
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
