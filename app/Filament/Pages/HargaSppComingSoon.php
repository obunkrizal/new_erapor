<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class HargaSppComingSoon extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Harga SPP';
    protected static string | \UnitEnum | null $navigationGroup = 'Transaksi SPP';
    protected string $view = 'filament.pages.spp-coming-soon';
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
