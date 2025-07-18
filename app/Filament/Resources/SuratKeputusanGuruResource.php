<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use App\Models\SuratKeputusanGuru;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SuratKeputusanGuruResource\Pages;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Filament\Resources\SuratKeputusanGuruResource\RelationManagers;
use Filament\Tables\Actions\ActionGroup;

class SuratKeputusanGuruResource extends Resource
{
    protected static ?string $model = SuratKeputusanGuru::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Surat Keputusan Guru';

    protected static ?string $modelLabel = 'Surat Keputusan Guru';

    protected static ?string $pluralModelLabel = 'Surat Keputusan Guru';

    protected static ?string $navigationGroup = 'Manajemen Surat';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'surat-keputusan-guru';

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


    private static function sanitizeFilename(string $filename): string
    {
        // Remove or replace invalid filename characters
        $invalidChars = ['/', '\\', ':', '*', '?', '"', '<', '>', '|'];
        $sanitized = str_replace($invalidChars, '_', $filename);

        // Remove file extension temporarily
        $pathInfo = pathinfo($sanitized);
        $nameWithoutExt = $pathInfo['filename'];
        $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';

        // Add timestamp to make filename unique
        return $nameWithoutExt . '-' . now()->format('YmdHis') . $extension;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Dasar Surat')
                    ->description('Masukkan informasi dasar surat keputusan')
                    ->icon('heroicon-o-document')
                    ->collapsible()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('nomor_surat')
                                    ->label('Nomor Surat')
                                    ->placeholder(fn($operation) => $operation === 'create' ? 'Akan dibuat otomatis' : '')
                                    ->disabled(fn($operation) => $operation === 'create')
                                    ->dehydrated(fn($operation) => $operation !== 'create')
                                    ->prefixIcon('heroicon-o-hashtag')
                                    ->helperText(fn($operation) => $operation === 'create' ? 'Nomor surat akan dibuat otomatis saat menyimpan' : ''),

                                Forms\Components\DatePicker::make('tanggal_surat')
                                    ->label('Tanggal Surat')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->prefixIcon('heroicon-o-calendar-days'),

                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->required()
                                    ->options([
                                        'draft' => 'Draft',
                                        'review' => 'Review',
                                        'approved' => 'Disetujui',
                                        'published' => 'Diterbitkan',
                                        'cancelled' => 'Dibatalkan',
                                    ])
                                    ->default('draft')
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-flag'),
                            ]),

                        Forms\Components\TextInput::make('perihal')
                            ->label('Perihal')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan perihal surat keputusan')
                            ->prefixIcon('heroicon-o-document-text')
                            ->columnSpanFull(),
                    ]),

                Section::make('Informasi Guru')
                    ->description('Pilih guru yang akan dikenai surat keputusan')
                    ->icon('heroicon-o-user')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('guru_id')
                                    ->label('Nama Guru')
                                    ->options(User::all()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-user'),

                                Forms\Components\Select::make('jenis_keputusan')
                                    ->label('Jenis Keputusan')
                                    ->required()
                                    ->options([
                                        'pengangkatan' => 'Pengangkatan',
                                        'promosi' => 'Promosi',
                                        'mutasi' => 'Mutasi',
                                        'pemberhentian' => 'Pemberhentian',
                                        'penugasan' => 'Penugasan',
                                        'sanksi' => 'Sanksi',
                                    ])
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-clipboard-document-list'),
                            ]),
                    ]),

                Section::make('Detail Kepegawaian')
                    ->description('Informasi detail perubahan kepegawaian')
                    ->icon('heroicon-o-briefcase')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('status_kepegawaian')
                                    ->label('Status Kepegawaian')
                                    ->required()
                                    ->options([
                                        'pns' => 'PNS',
                                        'pppk' => 'PPPK',
                                        'honorer' => 'Honorer',
                                        'kontrak' => 'Kontrak',
                                        'gty' => 'GTY',
                                        'gtt' => 'GTT',
                                    ])
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-identification'),

                                Forms\Components\DatePicker::make('tmt_berlaku')
                                    ->label('TMT Berlaku')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->prefixIcon('heroicon-o-calendar'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('jabatan_lama')
                                    ->label('Jabatan Lama')
                                    ->maxLength(255)
                                    ->placeholder('Jabatan sebelumnya (opsional)')
                                    ->prefixIcon('heroicon-o-arrow-left'),

                                Forms\Components\TextInput::make('jabatan_baru')
                                    ->label('Jabatan Baru')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Jabatan yang baru')
                                    ->prefixIcon('heroicon-o-arrow-right'),
                            ]),
                Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('unit_kerja_lama')
                            ->label('Unit Kerja Lama')
                            ->maxLength(255)
                            ->placeholder('Unit kerja sebelumnya (opsional)')
                            ->prefixIcon('heroicon-o-building-office'),

                        Forms\Components\TextInput::make('unit_kerja_baru')
                            ->label('Unit Kerja Baru')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Unit kerja yang baru')
                            ->prefixIcon('heroicon-o-building-office-2'),
                    ]),



                        Forms\Components\DatePicker::make('tmt_berakhir')
                            ->label('TMT Berakhir')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->prefixIcon('heroicon-o-calendar-days')
                            ->columnSpan(1),
                    ]),

                Section::make('Konten Surat')
                    ->description('Isi lengkap surat keputusan')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Textarea::make('dasar_hukum')
                            ->label('Dasar Hukum')
                            ->required()
                            ->rows(3)
                            ->placeholder('Masukkan dasar hukum yang menjadi acuan')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('pertimbangan')
                            ->label('Pertimbangan')
                            ->required()
                            ->rows(3)
                            ->placeholder('Masukkan pertimbangan dalam pengambilan keputusan')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('isi_keputusan')
                            ->label('Isi Keputusan')
                            ->required()
                            ->rows(4)
                            ->placeholder('Masukkan isi keputusan secara detail')
                            ->columnSpanFull(),
                    ]),

                Section::make('Penandatangan')
                    ->description('Informasi pejabat yang menandatangani surat')
                    ->icon('heroicon-o-pencil-square')
                    ->collapsible()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('pejabat_penandatangan')
                                    ->label('Nama Pejabat')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Nama lengkap pejabat')
                                    ->prefixIcon('heroicon-o-user-circle'),

                                Forms\Components\TextInput::make('jabatan_penandatangan')
                                    ->label('Jabatan Pejabat')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Jabatan pejabat penandatangan')
                                    ->prefixIcon('heroicon-o-identification'),

                                Forms\Components\TextInput::make('nip_penandatangan')
                                    ->label('NIP Pejabat')
                                    ->maxLength(255)
                                    ->placeholder('NIP pejabat (opsional)')
                                    ->prefixIcon('heroicon-o-hashtag'),
                            ]),
                    ]),

                Section::make('Lampiran & Persetujuan')
                    ->description('File lampiran dan informasi persetujuan')
                    ->icon('heroicon-o-paper-clip')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                    Forms\Components\FileUpload::make('file_surat')
                        ->label('File Surat')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(5120) // 5MB
                        ->directory('surat-keputusan')
                        ->visibility('private')
                        ->previewable(false)
                        ->downloadable()
                        ->getUploadedFileNameForStorageUsing(
                            fn(TemporaryUploadedFile $file): string => self::sanitizeFilename($file->getClientOriginalName())
                        ),


                    Forms\Components\Select::make('disetujui_oleh')
                                    ->label('Disetujui Oleh')
                                    ->options(User::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-check-circle'),
                            ]),

                        Forms\Components\DateTimePicker::make('tanggal_persetujuan')
                            ->label('Tanggal Persetujuan')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->prefixIcon('heroicon-o-clock')
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->rows(3)
                            ->placeholder('Catatan tambahan (opsional)')
                            ->columnSpanFull(),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_surat')
                    ->label('Nomor Surat')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->copyable()
                    ->copyMessage('Nomor surat berhasil disalin')
                    ->icon('heroicon-o-hashtag'),

                Tables\Columns\TextColumn::make('tanggal_surat')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar-days'),

                Tables\Columns\TextColumn::make('guru.name')
                    ->label('Nama Guru')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->icon('heroicon-o-user'),

                Tables\Columns\TextColumn::make('jenis_keputusan')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pengangkatan' => 'success',
                        'promosi' => 'info',
                        'mutasi' => 'warning',
                        'pemberhentian' => 'danger',
                        'penugasan' => 'primary',
                        'sanksi' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('jabatan_baru')
                    ->label('Jabatan Baru')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('unit_kerja_baru')
                    ->label('Unit Kerja')
                    ->searchable()
                    ->limit(25)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'review' => 'warning',
                        'approved' => 'success',
                        'published' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'draft' => 'heroicon-o-document',
                        'review' => 'heroicon-o-eye',
                        'approved' => 'heroicon-o-check-circle',
                        'published' => 'heroicon-o-paper-airplane',
                        'cancelled' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-document',
                    }),

                Tables\Columns\TextColumn::make('tmt_berlaku')
                    ->label('TMT Berlaku')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable()
                    ->icon('heroicon-o-calendar'),

                Tables\Columns\TextColumn::make('tmt_berakhir')
                    ->label('TMT Berakhir')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('Tidak ada')
                    ->icon('heroicon-o-calendar-days'),

                Tables\Columns\TextColumn::make('pejabat_penandatangan')
                    ->label('Pejabat Penandatangan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(25)
                    ->icon('heroicon-o-user-circle'),

                Tables\Columns\TextColumn::make('jabatan_penandatangan')
                    ->label('Jabatan Penandatangan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(25)
                    ->icon('heroicon-o-identification'),

                Tables\Columns\TextColumn::make('nip_penandatangan')
                    ->label('NIP Penandatangan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('Tidak ada')
                    ->icon('heroicon-o-hashtag'),

                Tables\Columns\IconColumn::make('file_surat')
                    ->label('File')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-arrow-down')
                    ->falseIcon('heroicon-o-document')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('tanggal_persetujuan')
                    ->label('Tanggal Persetujuan')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('Belum disetujui')
                    ->icon('heroicon-o-clock'),

                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Disetujui Oleh')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('Belum disetujui')
                    ->icon('heroicon-o-check-circle'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-plus-circle'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-pencil-square'),
            ])
            ->filters([
                SelectFilter::make('jenis_keputusan')
                    ->label('Jenis Keputusan')
                    ->options([
                        'pengangkatan' => 'Pengangkatan',
                        'promosi' => 'Promosi',
                        'mutasi' => 'Mutasi',
                        'pemberhentian' => 'Pemberhentian',
                        'penugasan' => 'Penugasan',
                        'sanksi' => 'Sanksi',
                    ])
                    ->multiple()
                    ->preload(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'review' => 'Review',
                        'approved' => 'Disetujui',
                        'published' => 'Diterbitkan',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->multiple()
                    ->preload(),

                SelectFilter::make('status_kepegawaian')
                    ->label('Status Kepegawaian')
                    ->options([
                        'pns' => 'PNS',
                        'pppk' => 'PPPK',
                        'honorer' => 'Honorer',
                        'kontrak' => 'Kontrak',
                        'gty' => 'GTY',
                        'gtt' => 'GTT',
                    ])
                    ->multiple()
                    ->preload(),



                // Then in your filters array, replace the DateFilter with:
                Filter::make('tanggal_surat')
                    ->form([
                        DatePicker::make('tanggal_surat')
                            ->label('Tanggal Surat'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tanggal_surat'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_surat', $date),
                            );
                    }),

                Filter::make('tmt_berlaku')
                    ->form([
                        DatePicker::make('tmt_berlaku')
                            ->label('TMT Berlaku'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tmt_berlaku'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tmt_berlaku', $date),
                            );
                    }),


                SelectFilter::make('guru_id')
                    ->label('Guru')
                    ->relationship('guru', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->actions([
                ActionGroup::make([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->color('info'),

                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning'),

            Tables\Actions\Action::make('print')
                ->label('Print Surat')
                ->tooltip('Print Rapor')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn($record): string => route('surat-keputusan.print', $record))
                ->tooltip('Print Surat')
                ->openUrlInNewTab(),

            Tables\Actions\Action::make('download')
                    ->label('Unduh')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->url(fn(SuratKeputusanGuru $record): string => route('surat-keputusan.download', $record))
                    ->tooltip('Unduh PDF Surat Keputusan'),

                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->color('danger'),
            ])
            ->label('Aksi')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('info')
                ->tooltip('Aksi')
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->icon('heroicon-o-trash'),

                    Tables\Actions\BulkAction::make('bulk_print')
                        ->label('Cetak Terpilih')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->action(function ($records) {
                            // You can implement bulk print functionality here
                            // For now, we'll redirect to individual prints
                            foreach ($records as $record) {
                                return redirect()->route('surat-keputusan.print', $record);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('bulk_approve')
                        ->label('Setujui Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 'approved',
                                    'tanggal_persetujuan' => now(),
                                    'disetujui_oleh' => auth()->id(),
                                ]);
                            }
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('bulk_publish')
                        ->label('Terbitkan Terpilih')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('info')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 'published',
                                ]);
                            }
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s')
            ->deferLoading()
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
            'index' => Pages\ListSuratKeputusanGurus::route('/'),
            'create' => Pages\CreateSuratKeputusanGuru::route('/create'),
            'view' => Pages\ViewSuratKeputusanGuru::route('/{record}'),
            'edit' => Pages\EditSuratKeputusanGuru::route('/{record}/edit'),
        ];
    }
}
