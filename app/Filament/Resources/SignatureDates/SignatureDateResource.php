<?php

namespace App\Filament\Resources\SignatureDates;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\SignatureDates\Pages\ListSignatureDates;
use App\Filament\Resources\SignatureDates\Pages\CreateSignatureDate;
use App\Filament\Resources\SignatureDates\Pages\EditSignatureDate;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\SignatureDate;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SignatureDateResource\Pages;
use App\Filament\Resources\SignatureDateResource\RelationManagers;

class SignatureDateResource extends Resource
{
    protected static ?string $model = SignatureDate::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Tanggal Rapor';

    protected static string | \UnitEnum | null $navigationGroup ='Data Master';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                Section::make()
                    ->columns(12)
                    ->schema([
                        TextInput::make('place')
                            ->label('Tempat Tanda Tangan')
                            ->helperText('Masukkan tempat tanda tangan')
                            ->columnSpan(6)
                            ->required(),
                        DatePicker::make('date')
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
                TextColumn::make('place')
                    ->label('Tempat Tanda Tangan Raport')
                    ->sortable()
                    ->alignLeft()
                    ->color('primary')
                    ->weight('bold'),
                TextColumn::make('date')
                    ->label('Tanggal Tanda Tangan Raport')
                    ->date()
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignRight()
                    ->extraAttributes(['class' => 'text-xs text-gray-500']),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignRight()
                    ->extraAttributes(['class' => 'text-xs text-gray-500']),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSignatureDates::route('/'),
            'create' => CreateSignatureDate::route('/create'),
            'edit' => EditSignatureDate::route('/{record}/edit'),
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
