<?php

namespace App\Filament\Resources\PenilaianSemesters\Schemas;

use App\Models\User;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Periode;
use Filament\Forms\Set;
use Filament\Schemas\Schema;
use App\Models\DimensiPembelajaran;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DateTimePicker;

class PenilaianSemesterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->description('')
                    ->schema([
                        Section::make('Informasi Dasar')
                            ->schema([
                                Select::make('kelas_id')
                                    ->label('Kelas')
                                    ->options(Kelas::all()->pluck('nama_kelas', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn ($set) => $set('siswa_id', null)),
                                Select::make('siswa_id')
                                    ->label('Siswa')
                                    ->options(fn ($get) => $get('kelas_id') ? \App\Models\KelasSiswa::where('kelas_id', $get('kelas_id'))
                                        ->where('status', 'aktif')
                                        ->with('siswa')
                                        ->get()
                                        ->pluck('siswa.nama_lengkap', 'siswa.id')
                                        ->filter()
                                        ->toArray() : [])
                                    ->searchable()
                                    ->required()
                                    ->preload()
                                    ->disabled(fn ($get) => !$get('kelas_id')),
                                Select::make('periode_id')
                                    ->label('Periode')
                                    ->options(Periode::all()->pluck('nama_periode', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->preload(),
                                Select::make('dimensi_id')
                                    ->label('Dimensi Pembelajaran')
                                    ->options(DimensiPembelajaran::all()->pluck('nama', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->preload(),
                            ])
                            ->columns(4),

                        Section::make('Penilaian')
                            ->schema([
                                Select::make('kategori_akhir')
                                    ->label('Kategori Akhir')
                                    ->options([
                                        'BB' => 'Belum Berkembang (BB)',
                                        'MB' => 'Mulai Berkembang (MB)',
                                        'BSH' => 'Berkembang Sesuai Harapan (BSH)',
                                        'BSB' => 'Berkembang Sangat Baik (BSB)',
                                    ])
                                    ->required()
                                    ->native(false),
                                Textarea::make('narasi_auto')
                                    ->label('Narasi Otomatis')
                                    ->rows(4)
                                    ->columnSpanFull()
                                    ->helperText('Narasi yang dihasilkan secara otomatis berdasarkan data penilaian.'),
                                Textarea::make('narasi_manual')
                                    ->label('Narasi Manual')
                                    ->rows(4)
                                    ->columnSpanFull()
                                    ->helperText('Narasi yang dapat diedit secara manual. Jika diisi, akan menggantikan narasi otomatis.'),
                            ]),

                        Section::make('Persetujuan')
                            ->schema([
                        Toggle::make('is_approved')
                            ->label('Disetujui')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($set, $state) {
                                if ($state) {
                                    $set('approved_at', now());
                                    $set('approved_by', auth()->id());
                                } else {
                                    $set('approved_at', null);
                                    $set('approved_by', null);
                                }
                            }),
                                DateTimePicker::make('approved_at')
                                    ->label('Tanggal Persetujuan')
                                    ->disabled()
                                    ->dehydrated(),
                                Select::make('approved_by')
                                    ->label('Disetujui Oleh')
                                    ->options(User::where('role', 'guru')->pluck('name', 'id'))
                                    ->searchable()
                                    ->disabled()
                                    ->dehydrated(),
                            ])
                            ->columns(3),
                    ])
                    ->columnSpan(2),

            ]);
    }
}
