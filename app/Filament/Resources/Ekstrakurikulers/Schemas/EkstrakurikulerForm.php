<?php

namespace App\Filament\Resources\Ekstrakurikulers\Schemas;

use App\Models\Siswa;
use Filament\Schemas\Schema;
use App\Models\Ekstrakurikuler;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class EkstrakurikulerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ekstrakurikuler Details')
                    ->schema([

                        TextInput::make('nama_kegiatan')
                            ->label('Nama Kegiatan')
                            ->placeholder('Masukkan nama kegiatan ekstrakurikuler')
                            ->required()
                            ->maxLength(255),
                            Select::make('rentang_usia')
                            ->label('Rentang Usia')
                            ->options([
                                '2-3' => '2-3 Tahun',
                                '4-5' => '4-5 Tahun',
                                '5-6' => '5-6 Tahun',
                            ])
                            ->placeholder('Pilih rentang usia')
                            ->required(),
                            Select::make('jenis')
                            ->label('Jenis')
                            ->options([
                                'ekstrakurikuler' => 'Ekstrakurikuler',
                                'intrakurikuler' => 'Intrakurikuler',
                            ])
                            ->placeholder('Pilih jenis kegiatan')
                            ->required(),
                        Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->placeholder('Masukkan deskripsi kegiatan')
                            ->rows(4),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->required(),
                    ])
                    ->columnSpan(2),
            ]);
    }
}
