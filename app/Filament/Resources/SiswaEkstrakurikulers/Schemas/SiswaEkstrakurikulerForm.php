<?php

namespace App\Filament\Resources\SiswaEkstrakurikulers\Schemas;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Periode;
use Filament\Schemas\Schema;
use App\Models\Ekstrakurikuler;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;

class SiswaEkstrakurikulerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Siswa Ekstrakurikuler Details')
                    ->schema([
                        Grid::make()
                            ->schema([
                                Select::make('kelas_id')
                                    ->label('Kelas')
                                    ->options(Kelas::all()->pluck('nama_kelas', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->placeholder('Pilih kelas')
                                    ->live()
                                    ->afterStateUpdated(fn($set) => $set('siswa_id', null)),
                                Select::make('siswa_id')
                                    ->label('Siswa')
                                    ->options(fn($get) => $get('kelas_id') ? \App\Models\KelasSiswa::where('kelas_id', $get('kelas_id'))
                                        ->where('status', 'aktif')
                                        ->with('siswa')
                                        ->get()
                                        ->pluck('siswa.nama_lengkap', 'siswa.id')
                                        ->filter()
                                        ->toArray() : [])
                                    ->searchable()
                                    ->required()
                                    ->placeholder('Pilih siswa')
                                    ->disabled(fn($get) => !$get('kelas_id')),
                                Select::make('periode_id')
                                    ->label('Periode')
                                    ->default(Periode::where('is_active', true)->first()?->id)
                                    ->options(Periode::where('is_active', true)->pluck('nama_periode', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->placeholder('Pilih periode'),
                            ])
                            ->columns(3),

                        Select::make('ekstrakurikuler_id')
                            ->label('Ekstrakurikuler')
                            ->options(function (callable $get) {
                                $kelas = Kelas::find($get('kelas_id'));
                                $rentangUsia = $kelas?->rentang_usia ?? '5-6';
                                return Ekstrakurikuler::where('rentang_usia', $rentangUsia)
                                    ->where('is_active', true)
                                    ->pluck('nama_kegiatan', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->placeholder('Pilih ekstrakurikuler'),

                        Textarea::make('capaian')
                            ->label('Capaian')
                            ->placeholder('Masukkan capaian siswa dalam kegiatan ekstrakurikuler')
                            ->rows(4),
                    ])
                    ->columnSpan(2),
            ]);
    }
}
