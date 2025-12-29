<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\SignatureDate;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SignatureDateResource\Pages;
use App\Filament\Resources\SignatureDateResource\RelationManagers;

class SignatureDateResource extends Resource
{
    protected static ?string $model = SignatureDate::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Tanggal Rapor';

    protected static ?string $navigationGroup ='Data Master';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(12)
            ->schema([
                Section::make()
                    ->columns(12)
                    ->schema([
                        TextInput::make('place')
                            ->label('Tempat Tanda Tangan')
                            ->helperText('Masukkan tempat tanda tangan')
                            ->columnSpan(6)
                            ->required(),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal Tanda Tangan Raport')
                            ->helperText('Pilih tanggal tanda tangan')
                            ->required()
                            ->default(now()->format('d M Y'))
                            ->timezone('Asia/Jakarta')
                            ->native(false)
                            ->columnSpan(6)
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('place')
                    ->label('Tempat Tanda Tangan Raport')
                    ->sortable()
                    ->alignLeft()
                    ->color('primary')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal Tanda Tangan Raport')
                    ->date()
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignRight()
                    ->extraAttributes(['class' => 'text-xs text-gray-500']),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignRight()
                    ->extraAttributes(['class' => 'text-xs text-gray-500']),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSignatureDates::route('/'),
            'create' => Pages\CreateSignatureDate::route('/create'),
            'edit' => Pages\EditSignatureDate::route('/{record}/edit'),
        ];
    }

    // Add these methods for more robust access control
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && 
           method_exists(Auth::user(), 'isGuru') && 
           Auth::user()->isGuru();
    }

    public static function canAccess(): bool
    {
        return Auth::check() && 
           method_exists(Auth::user(), 'isGuru') && 
           Auth::user()->isGuru();
    }
}
