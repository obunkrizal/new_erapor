<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class SuratKeputusanComingSoon extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Surat Keputusan Guru';
    protected static string | \UnitEnum | null $navigationGroup = 'Administrasi';
    protected string $view = 'filament.pages.surat-keputusan-coming-soon';
    protected static ?string $title = 'Coming Soon!!';

    public static function getNavigationBadge(): ?string
    {
        return 'Coming Soon';
    }

    public static function getNavigationBadgeColor(): ?string
    {
        // Example: Return 'warning' if count is greater than 10, otherwise 'primary'
        return 'warning';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user && $user->isAdmin();
    }
}
