<?php

namespace App\Filament\Resources\IndikatorCapaians\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IndikatorCapaiansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('dimensi.nama')
                    ->label('Dimensi Pembelajaran')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('kode_indikator')
                    ->label('Kode Indikator')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('rentang_usia')
                    ->label('Rentang Usia')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                '2-3' => 'danger',
                        '4-5' => 'info',
                        '5-6' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                '2-3' => '2 - 3 Tahun',
                        '4-5' => '4 - 5 Tahun',
                        '5-6' => '5 - 6 Tahun',
                        default => $state,
                    }),
                TextColumn::make('urutan')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
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
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('urutan', 'asc');
    }
}
