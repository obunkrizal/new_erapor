<?php

namespace App\Filament\Resources\ObservasiHarians\Tables;

use App\Models\ObservasiHarian;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ObservasiHariansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
            TextColumn::make('siswa.nama_lengkap')
                ->label('Nama Siswa')
                    ->sortable(),
            TextColumn::make('guru.nama_guru')
                ->label('Nama Guru')
                ->description(fn($record) => ($record->kelas_id && $record->kelas) ? $record->kelas->nama_kelas : '-')
                ->sortable(),
            TextColumn::make('indikator.kode_indikator')
                ->label('Indikator')
                ->description(fn($record) => ($record->indikator) ? $record->indikator->deskripsi : '-')
                ->wrap()
                    ->sortable(),
                TextColumn::make('tanggal_observasi')
                ->label('Tanggal')
                    ->date()
                    ->sortable(),
                TextColumn::make('kategori_penilaian')
                ->label('Kategori Penilaian')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'BB' => 'danger',
                    'MB' => 'warning',
                    'BSB' => 'primary',
                    'BSH' => 'info',
                    default => 'gray',
                })
                ->formatStateUsing(
                    function (ObservasiHarian $record): string {
                        // You can also format the text display
                        return match ($record->kategori_penilaian) {
                            'BB' => '[BB] Belum Berkembang',
                            'MB' => '[MB] Mulai Berkembang',
                            'BSB' => '[BSB] Berkembang Sangat Baik',
                            'BSH' => '[BSH] Berkembang Sesuai Harapan',
                            default => $record->kategori_penilaian,
                        };
                    },
                ),
            ImageColumn::make('foto_dokumentasi')
                ->label('Dokumentasi'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
