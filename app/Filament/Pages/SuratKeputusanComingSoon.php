<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class SuratKeputusanComingSoon extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Surat Keputusan Guru';
    protected static ?string $navigationGroup = 'Administrasi';
    protected static string $view = 'filament.pages.surat-keputusan-coming-soon';
    protected static ?string $title = 'Surat Keputusan Guru';

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }
}
