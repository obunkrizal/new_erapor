<?php

namespace App\Filament\Resources\ObservasiHarians\Schemas;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Periode;
use Filament\Schemas\Schema;
use App\Models\IndikatorCapaian;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;

class ObservasiHarianForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->description('')
                    ->schema([
                        Section::make('Informasi Siswa')
                            ->description('Pilih siswa dan guru yang terlibat dalam observasi.')
                            ->schema([
                                Grid::make(2)
                                    ->schema([

                        Select::make('kelas_id')
                                            ->label('Kelas')
                                            ->options(Kelas::all()->pluck('nama_kelas', 'id'))
                                            ->searchable()
                                            ->required()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $set('siswa_ids', null);
                                                $set('siswa_id', null);
                                                $set('guru_id', null);
                                                $set('indikator_id', null);

                            // Auto-set guru_id if kelas has a guru, otherwise set default guru
                            $kelasId = $state;
                                                if ($kelasId) {
                                                    $kelas = \App\Models\Kelas::find($kelasId);
                                                    if ($kelas && $kelas->guru_id) {
                                                        $set('guru_id', $kelas->guru_id);
                                } else {
                                    // Set default guru if kelas has no guru assigned
                                    $defaultGuru = \App\Models\Guru::first();
                                    if ($defaultGuru) {
                                        $set('guru_id', $defaultGuru->id);
                                    }
                                                    }
                                                }
                            }),
                        Select::make('periode_id')
                            ->label('Periode')
                            ->options(Periode::all()->pluck('nama_periode', 'id'))
                            ->default(fn() => Periode::where('is_active', true)->first()?->id)
                            ->searchable()
                            ->required()
                            ->preload(),
                        Select::make('guru_id')
                            ->label('Guru')
                            ->options(\App\Models\Guru::pluck('nama_guru', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn($get) => filled($get('guru_id')))
                            ->dehydrated(true)
                            ->placeholder('Pilih Guru')
                                ->helperText('Guru akan otomatis terisi berdasarkan kelas yang dipilih.'),
                            Toggle::make('multiple_selection')
                                ->label('Pilih Multiple Siswa')
                                ->default(true)
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('siswa_ids', null);
                                    $set('siswa_id', null);
                                })
                                ->helperText('Aktifkan untuk memilih lebih dari satu siswa sekaligus'),
                            // Multiple selection
                            Select::make('siswa_ids')
                                ->label('Pilih Siswa (Multiple)')
                                ->options(fn($get) => $get('kelas_id') ? \App\Models\KelasSiswa::where('kelas_id', $get('kelas_id'))
                                    ->where('status', 'aktif')
                                    ->with('siswa')
                                    ->get()
                                    ->pluck('siswa.nama_lengkap', 'siswa.id')
                                    ->filter()
                                    ->toArray() : [])
                                ->multiple()
                                ->searchable()
                                ->required(fn($get) => $get('multiple_selection'))
                                ->preload()
                                ->visible(fn($get) => $get('multiple_selection'))
                                ->disabled(fn($get) => !$get('kelas_id'))
                                ->helperText('Pilih satu atau lebih siswa yang akan diobservasi')
                                ->columnSpanFull(),

                            // Single selection
                            Select::make('siswa_id')
                                ->label('Pilih Siswa')
                                ->options(fn($get) => $get('kelas_id') ? \App\Models\KelasSiswa::where('kelas_id', $get('kelas_id'))
                                    ->where('status', 'aktif')
                                    ->with('siswa')
                                    ->get()
                                    ->pluck('siswa.nama_lengkap', 'siswa.id')
                                    ->filter()
                                    ->toArray() : [])
                                ->searchable()
                                ->required(fn($get) => !$get('multiple_selection'))
                                ->preload()
                                ->visible(fn($get) => !$get('multiple_selection'))
                                ->disabled(fn($get) => !$get('kelas_id'))
                                ->helperText('Pilih satu siswa yang akan diobservasi'),

                        ]),
                            ]),

                        Section::make('Detail Observasi')
                            ->description('Isi detail observasi harian siswa.')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('indikator_id')
                                            ->label('Indikator Capaian')
                                            ->options(function (callable $get) {
                                                $kelasId = $get('kelas_id');
                                                if ($kelasId) {
                                                    $kelas = \App\Models\Kelas::find($kelasId);
                                                    $rentangUsia = $kelas?->rentang_usia ?? '5-6';
                                                    return IndikatorCapaian::where('rentang_usia', $rentangUsia)
                                                        ->orderBy('urutan')
                                                        ->pluck('deskripsi', 'id')
                                                        ->toArray();
                                                }
                                                return [];
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->placeholder('Pilih indikator capaian')
                                            ->helperText('Indikator capaian berdasarkan rentang usia kelas.')
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                // Optional: Add logic to update other fields if needed
                                            }),
                                        DatePicker::make('tanggal_observasi')
                                            ->label('Tanggal Observasi')
                                            ->required()
                                            ->placeholder('Pilih tanggal')
                                            ->helperText('Tanggal ketika observasi dilakukan.'),
                                    ]),
                                Select::make('kategori_penilaian')
                                    ->label('Kategori Penilaian')
                                    ->options([
                                        'BB' => 'Belum Berkembang (BB)',
                                        'MB' => 'Mulai Berkembang (MB)',
                                        'BSH' => 'Berkembang Sesuai Harapan (BSH)',
                                        'BSB' => 'Berkembang Sangat Baik (BSB)'
                                    ])
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->placeholder('Pilih kategori penilaian')
                                    ->helperText('Pilih kategori penilaian berdasarkan perkembangan siswa.'),
                                Textarea::make('catatan_guru')
                                    ->label('Catatan Guru')
                                    ->placeholder('Masukkan catatan observasi...')
                                    ->helperText('Catatan tambahan dari guru mengenai observasi siswa.')
                                    ->rows(4)
                                    ->columnSpanFull(),
                                FileUpload::make('foto_dokumentasi')
                                    ->label('Foto Dokumentasi')
                                    ->image()
                                    ->directory('observasi-harian')
                                    ->visibility('public')
                                    ->placeholder('Upload foto dokumentasi')
                                    ->helperText('Upload foto yang mendukung observasi (opsional).')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif'])
                                    ->maxSize(2048)
                                    ->imagePreviewHeight('250')
                                    ->loadingIndicatorPosition('left')
                                    ->panelAspectRatio('2:1')
                                    ->panelLayout('integrated')
                                    ->removeUploadedFileButtonPosition('right')
                                    ->uploadButtonPosition('left')
                                    ->uploadProgressIndicatorPosition('left'),
                            ]),
                    ])
                    ->columnSpan(2),


            ]);
    }
}
