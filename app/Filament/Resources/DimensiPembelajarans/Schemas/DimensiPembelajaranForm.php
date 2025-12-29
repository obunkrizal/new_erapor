<?php

namespace App\Filament\Resources\DimensiPembelajarans\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class DimensiPembelajaranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->description('')
                    ->schema([
                        Section::make('Informasi Dasar')
                            ->description('Masukkan informasi dasar dimensi pembelajaran.')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('kode')
                                            ->label('Kode')
                                            ->required()
                                            ->placeholder('Masukkan kode unik'),
                                        TextInput::make('nama')
                                            ->label('Nama')
                                            ->required()
                                            ->placeholder('Masukkan nama dimensi'),
                                    ]),
                                Select::make('kategori')
                                    ->label('Kategori')
                                    ->default('dasar_literasi_matematika_sains')
                                    ->searchable()
                                    ->reactive()
                                    ->options([
                                        'dasar_literasi_matematika_sains' => 'Dasar Literasi Matematika Sains',
                                        'jati_diri' => 'Jati Diri',
                                        'nilai_agama_budi_pekerti' => 'Nilai Agama Budi Pekerti',
                                    ])
                                    ->required(),
                                Textarea::make('deskripsi')
                                    ->label('Deskripsi')
                                    ->rows(3)
                                    ->placeholder('Jelaskan dimensi pembelajaran ini'),
                            ]),
                        Section::make('Pengaturan')
                            ->description('Konfigurasi pengaturan tambahan.')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('urutan')
                                            ->label('Urutan')
                                            ->required()
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->placeholder('0'),
                                        Toggle::make('is_active')
                                            ->label('Aktif')
                                            ->required()
                                            ->default(true),
                                    ]),
                            ]),


                    ])
            ])
            ->columns(1);
    }
}
