<?php

namespace App\Filament\Resources\TemplateNarasis\Schemas;

use Filament\Schemas\Schema;
use App\Models\DimensiPembelajaran;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;

class TemplateNarasiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Template Narasi Pembelajaran')
                    ->description('')
                    ->schema([
                        Grid::make()
                            ->schema([
                                Select::make('dimensi_id')
                                    ->label('Dimensi Pembelajaran')
                                    ->options(DimensiPembelajaran::where('is_active', true)->pluck('nama', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->helperText('Pilih dimensi pembelajaran yang akan diberi template narasi'),

                                Select::make('kategori_penilaian')
                                    ->label('Kategori Penilaian')
                                    ->options([
                                        'BSB' => 'Belum Berkembang Sesuai Harapan',
                                        'BSH' => 'Berkembang Sesuai Harapan',
                                        'MB' => 'Mulai Berkembang',
                                        'BB' => 'Belum Berkembang'
                                    ])
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Pilih kategori penilaian yang sesuai dengan template ini'),
                            ]),


                        Textarea::make('template_kalimat')
                            ->label('Template Kalimat Narasi')
                            ->required()
                            ->columnSpanFull()
                            ->rows(4)
                            ->helperText('Gunakan placeholder seperti {nama}, {nama_panggilan}, atau placeholder kustom dari opsi di bawah. Contoh: "{nama} dapat {kemampuan} dengan {kualitas}"'),

                        KeyValue::make('placeholder_options')
                            ->label('Opsi Placeholder')
                            ->columnSpanFull()
                            ->keyLabel('Nama Placeholder')
                            ->valueLabel('Opsi Nilai (pisahkan dengan koma)')
                            ->helperText('Tambahkan placeholder kustom dengan opsi nilai. Contoh: kemampuan -> mengenal huruf, menulis nama, membaca kata')
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $formatted = [];
                                    foreach ($state as $key => $value) {
                                        if (is_string($value)) {
                                            $formatted[$key] = array_map('trim', explode(',', $value));
                                        }
                                    }
                                    $set('placeholder_options', $formatted);
                                }
                            }),
                    ])
                    ->columnSpan(2),

            ]);
    }
}
