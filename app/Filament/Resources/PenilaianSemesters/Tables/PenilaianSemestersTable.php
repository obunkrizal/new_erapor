<?php

namespace App\Filament\Resources\PenilaianSemesters\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PenilaianSemestersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('siswa.nama_lengkap')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
            TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->sortable(),
                TextColumn::make('periode.nama_periode')
                    ->label('Periode')
                    ->sortable(),
                TextColumn::make('dimensi.nama')
                    ->label('Dimensi Pembelajaran')
                    ->sortable(),
                TextColumn::make('kategori_akhir')
                    ->label('Kategori Akhir')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'BB' => 'danger',
                        'MB' => 'warning',
                        'BSH' => 'success',
                        'BSB' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'BB' => 'Belum Berkembang',
                        'MB' => 'Mulai Berkembang',
                        'BSH' => 'Berkembang Sesuai Harapan',
                        'BSB' => 'Berkembang Sangat Baik',
                        default => $state,
                    }),
                TextColumn::make('narasi_final')
                    ->label('Narasi')
                    ->getStateUsing(fn ($record) => $record->getNarasiFinal())
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                IconColumn::make('is_approved')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('approver.name')
                    ->label('Disetujui Oleh')
                    ->sortable(),
                TextColumn::make('approved_at')
                    ->label('Tanggal Persetujuan')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kelas')
                    ->relationship('kelas', 'nama_kelas')
                    ->label('Kelas'),
                SelectFilter::make('periode')
                    ->relationship('periode', 'nama_periode')
                    ->label('Periode'),
                SelectFilter::make('dimensi')
                    ->relationship('dimensi', 'nama')
                    ->label('Dimensi Pembelajaran'),
                SelectFilter::make('kategori_akhir')
                    ->label('Kategori Akhir')
                    ->options([
                        'BB' => 'Belum Berkembang',
                        'MB' => 'Mulai Berkembang',
                        'BSH' => 'Berkembang Sesuai Harapan',
                        'BSB' => 'Berkembang Sangat Baik',
                    ]),
                SelectFilter::make('is_approved')
                    ->label('Status Persetujuan')
                    ->options([
                        true => 'Disetujui',
                        false => 'Belum Disetujui',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
