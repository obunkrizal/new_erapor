<?php

namespace App\Filament\Resources\IndikatorCapaians\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class IndikatorCapaianForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Indikator Capaian')
                    ->description('Masukkan informasi dasar indikator capaian.')
                    ->schema([
                        Select::make('dimensi_id')
                            ->label('Dimensi Pembelajaran')
                            ->relationship('dimensi', 'nama')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Pilih dimensi pembelajaran'),
                        TextInput::make('kode_indikator')
                            ->label('Kode Indikator')
                            ->required()
                            ->placeholder('Masukkan kode indikator'),
                        Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->required()
                            ->rows(3)
                            ->placeholder('Jelaskan indikator capaian ini'),
                    ]),
                Section::make('Pengaturan')
                    ->description('Konfigurasi pengaturan tambahan.')
                    ->schema([
                        Select::make('rentang_usia')
                            ->label('Rentang Usia')
                            ->options([
                                '2-3' => '2 - 3 Tahun',
                                '4-5' => '4 - 5 Tahun',
                                '5-6' => '5 - 6 Tahun',
                            ])
                            ->searchable()
                            ->default('5-6')
                            ->required(),
                        TextInput::make('urutan')
                            ->label('Urutan')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->placeholder('0'),
                    ]),
            ]);
    }
}
