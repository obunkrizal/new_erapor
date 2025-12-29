<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Filament\Resources\SuratKeputusanGuruResource\Pages;

class SuratKeputusanGuruResource extends Resource
{
    protected static ?string $model = null; // Set to null temporarily
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Surat Keputusan Guru';
    protected static ?string $modelLabel = 'Surat Keputusan';
    protected static ?string $pluralModelLabel = 'Surat Keputusan Guru';
    protected static ?string $navigationGroup = 'Administrasi';

    public static function canAccess(): bool
    {
        // Show notification when trying to access
        // Notification::make()
        //     ->title('Fitur Belum Tersedia')
        //     ->body('Fitur Surat Keputusan Guru sedang dalam tahap pengembangan dan akan segera tersedia.')
        //     ->warning()
        //     ->duration(5000)
        //     ->send();

        return false; // Prevent access
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([]);
    }

    public static function getPages(): array
    {
        return [];
    }
}
