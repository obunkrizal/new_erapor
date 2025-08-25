<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SekolahResource\Pages;
use App\Filament\Resources\SekolahResource\RelationManagers;
use App\Models\Sekolah;
use App\Models\Guru;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Traits\AdminOnlyResource;

class SekolahResource extends Resource
{
    use AdminOnlyResource;

    protected static ?string $model = Sekolah::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Manajemen Sekolah';

    protected static ?string $modelLabel = 'Sekolah';

    protected static ?string $pluralModelLabel = 'Manajemen Sekolah';


    protected static ?string $navigationGroup = 'Data Master';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'nama_sekolah';

    // Hide navigation from guru - only show for admin
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    // Control access - only admin can access
    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar Sekolah')
                    ->description('Data identitas dan informasi dasar sekolah')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nama_sekolah')
                                    ->label('Nama Sekolah')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Masukkan nama sekolah')
                                    ->prefixIcon('heroicon-m-building-office-2')
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('npsn')
                                    ->label('NPSN (Nomor Pokok Sekolah Nasional)')
                                    ->required()
                                    ->maxLength(8)
                                    ->numeric()
                                    ->placeholder('12345678')
                                    ->prefixIcon('heroicon-m-identification')
                                    ->unique(ignoreRecord: true)
                                    ->helperText('8 digit nomor unik sekolah')
                                    ->rules(['digits:8']),

                                Forms\Components\Select::make('akreditasi')
                                    ->label('Akreditasi')
                                    ->options([
                                        'A' => 'A (Sangat Baik)',
                                        'B' => 'B (Baik)',
                                        'C' => 'C (Cukup)',
                                        'Belum Terakreditasi' => 'Belum Terakreditasi',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->prefixIcon('heroicon-m-star')
                                    ->placeholder('Pilih akreditasi sekolah'),
                            ]),
                    ])
                    ->collapsible()
                    ->columns(1),

                Forms\Components\Section::make('Alamat & Kontak')
                    ->description('Informasi alamat dan kontak sekolah')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->maxLength(500)
                            ->placeholder('Masukkan alamat lengkap sekolah')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('no_telp')
                                    ->label('Nomor Telepon')
                                    ->tel()
                                    ->required()
                                    ->maxLength(20)
                                    ->placeholder('(021) 1234-5678')
                                    ->prefixIcon('heroicon-m-phone')
                                    ->helperText('Format: (kode area) nomor telepon'),

                                Forms\Components\TextInput::make('email')
                                    ->label('Email Sekolah')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('info@sekolah.sch.id')
                                    ->prefixIcon('heroicon-m-envelope')
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Email resmi sekolah'),
                            ]),
                    ])
                    ->collapsible()
                    ->columns(1),

                Forms\Components\Section::make('Kepala Sekolah')
                    ->description('Informasi kepala sekolah dan pimpinan')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Forms\Components\Select::make('guru_id')
                            ->label('Kepala Sekolah')
                            ->options(function () {
                                return Guru::query()
                                    ->orderBy('nama_guru')
                                    ->pluck('nama_guru', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->prefixIcon('heroicon-m-user-circle')
                            ->helperText('Pilih guru yang menjabat sebagai kepala sekolah')
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $guru = Guru::find($state);
                                    if ($guru) {
$set('kepala_sekolah_info', $guru->nama_guru . ($guru->nuptk ? ' (NUPTK: ' . $guru->nuptk . ($guru->nip ? ', NIP: ' . $guru->nip : '') . ')' : ($guru->nip ? ' (NIP: ' . $guru->nip . ')' : '')));
                                    }
                                }
                            }),

Forms\Components\Placeholder::make('kepala_sekolah_info')
    ->label('Informasi Kepala Sekolah')
    ->content(function (Forms\Get $get) {
        if ($get('guru_id')) {
            $guru = Guru::find($get('guru_id'));
            if ($guru) {
                $info = "👤 Nama: {$guru->nama_guru}";
                if ($guru->nuptk) {
                    $info .= "\n🆔 NUPTK: {$guru->nuptk}";
                } elseif ($guru->nip) {
                    $info .= "\n🆔 NIP: {$guru->nip}";
                }
                if ($guru->jabatan) {
                    $info .= "\n💼 Jabatan: {$guru->jabatan}";
                }
                return $info;
            }
        }
        return 'Pilih kepala sekolah untuk melihat informasi';
    })
    ->visible(fn (Forms\Get $get): bool => (bool) $get('guru_id')),
                    ])
                    ->collapsible()
                    ->columns(1),

                Forms\Components\Section::make('Logo Sekolah')
                    ->description('Upload logo resmi sekolah')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        Forms\Components\FileUpload::make('logo')
                            ->label('Logo Sekolah')
                            ->image()
                            ->directory('sekolah/logo')
                            ->disk('public')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/svg+xml'])
                            ->maxSize(2048)
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '1:1',
                                '4:3',
                                '16:9',
                            ])
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('500')
                            ->imageResizeTargetHeight('500')
                            ->previewable()
                            ->downloadable()
                            ->helperText('Format: JPG, PNG, SVG. Maksimal 2MB. Rasio 1:1 disarankan.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->columns(1),
            Forms\Components\Section::make('Logo yayasan')
                ->description('Upload logo resmi Yayasan')
                ->icon('heroicon-o-photo')
                ->schema([
                    Forms\Components\FileUpload::make('logo_yayasan')
                        ->label('Logo Sekolah')
                        ->image()
                        ->directory('sekolah/logo_yayasan')
                        ->disk('public')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/svg+xml'])
                        ->maxSize(2048)
                        ->imageEditor()
                        ->imageEditorAspectRatios([
                            '1:1',
                            '4:3',
                            '16:9',
                        ])
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('1:1')
                        ->imageResizeTargetWidth('500')
                        ->imageResizeTargetHeight('500')
                        ->previewable()
                        ->downloadable()
                        ->helperText('Format: JPG, PNG, SVG. Maksimal 2MB. Rasio 1:1 disarankan.')
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->collapsed()
                ->columns(1),

                Forms\Components\Section::make('Informasi Tambahan')
                    ->description('Data tambahan dan keterangan sekolah')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('website')
                                    ->label('Website Sekolah')
                                    ->url()
                                    ->maxLength(255)
                                    ->placeholder('https://www.sekolah.sch.id')
                                    ->prefixIcon('heroicon-m-globe-alt')
                                    ->helperText('URL lengkap website sekolah'),

                                Forms\Components\Select::make('status')
                                    ->label('Status Sekolah')
                                    ->options([
                                        'Negeri' => 'Negeri',
                                        'Swasta' => 'Swasta',
                                    ])
                                    ->default('Negeri')
                                    ->native(false)
                                    ->prefixIcon('heroicon-m-building-office'),
                            ]),

                        Forms\Components\Textarea::make('visi')
                            ->label('Visi Sekolah')
                            ->maxLength(1000)
                            ->placeholder('Masukkan visi sekolah')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('misi')
                            ->label('Misi Sekolah')
                            ->maxLength(1000)
                            ->placeholder('Masukkan misi sekolah')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan Tambahan')
                            ->maxLength(500)
                            ->placeholder('Informasi tambahan tentang sekolah')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular()
                    ->size(60)
                    ->defaultImageUrl(url('/images/default-school-logo.png'))
                    ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\ImageColumn::make('logo_yayasan')
                ->label('Logo Yayasan')
                ->circular()
                ->size(60)
                ->defaultImageUrl(url('/images/default-school-logo.png'))
                ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('nama_sekolah')
                    ->label('Nama Sekolah')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->size('sm')
                    ->description(fn (Sekolah $record): string =>
                        'NPSN: ' . ($record->npsn ?? 'N/A')
                    )
                    ->wrap(),

                Tables\Columns\TextColumn::make('npsn')
                    ->label('NPSN')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('NPSN berhasil disalin!')
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-m-identification'),

                Tables\Columns\TextColumn::make('alamat')
                    ->label('Alamat')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) > 50) {
                            return $state;
                        }
                        return null;
                    })
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('no_telp')
                    ->label('Telepon')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Nomor telepon berhasil disalin!')
                    ->icon('heroicon-m-phone')
                    ->color('success'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email berhasil disalin!')
                    ->icon('heroicon-m-envelope')
                    ->color('info')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('akreditasi')
                    ->label('Akreditasi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'A' => 'success',
                        'B' => 'warning',
                        'C' => 'danger',
                        'Belum Terakreditasi' => 'gray',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'A' => 'heroicon-m-star',
                        'B' => 'heroicon-m-star',
                        'C' => 'heroicon-m-star',
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->sortable(),

Tables\Columns\TextColumn::make('guru.nama_guru')
    ->label('Kepala Sekolah')
    ->searchable()
    ->sortable()
    ->weight(FontWeight::SemiBold)
    ->description(fn (Sekolah $record): string =>
        $record->guru?->nuptk ? 'NUPTK: ' . $record->guru->nuptk : 'Tanpa NUPTK'
    )
    ->wrap(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListSekolahs::route('/'),
            'create' => Pages\CreateSekolah::route('/create'),
            'edit' => Pages\EditSekolah::route('/{record}/edit'),
        ];
    }
}
