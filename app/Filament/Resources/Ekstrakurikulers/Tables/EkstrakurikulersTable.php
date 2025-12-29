<?php

namespace App\Filament\Resources\Ekstrakurikulers\Tables;

use Dom\Text;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EkstrakurikulersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_kegiatan')
                    ->label('Nama Kegiatan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rentang_usia')
                    ->label('Rentang Usia')
                    ->searchable()
                   ->badge()
                ->color(fn(string $state): string => match ($state) {
                    '2-3' => 'danger',
                    '3-4' => 'success',
                    '4-5' => 'info',
                    '5-6' => 'warning',
                    default => 'gray',
                })
                ->formatStateUsing(fn(string $state): string => match ($state) {
                    '2-3' => '2 - 3 Tahun',
                    '3-4' => '3 - 4 Tahun',
                    '4-5' => '4 - 5 Tahun',
                    '5-6' => '5 - 6 Tahun',
                    default => $state,
                })
                    ->sortable(),
                 TextColumn::make('jenis')
                    ->label('Jenis')
                    ->sortable(),
                    TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->wrap(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
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
