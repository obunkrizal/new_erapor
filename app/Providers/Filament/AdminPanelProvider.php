<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use App\Filament\Pages\Auth\Login;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Auth;
use App\Filament\Widgets\NilaiStatsWidget;
use App\Filament\Widgets\SiswaStatsWidget;
use Filament\Http\Middleware\Authenticate;
use CWSPS154\AppSettings\AppSettingsPlugin;
use App\Filament\Resources\GuruKelasResource;
use App\Filament\Resources\GuruNilaiResource;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'danger' => Color::Rose,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'primary' => Color::Indigo,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                // QuickActionsWidget::class,
                // PeriodeOverviewWidget::class,
                SiswaStatsWidget::class,
                // GuruStatsWidget::class,
                // NilaiStatsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\CheckUserActive::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->resources([
                // Admin-only resources will be auto-discovered
                // Guru-specific resources
                GuruKelasResource::class,
                GuruNilaiResource::class,
            ])
            ->plugins([
                AppSettingsPlugin::make()
                    ->canAccess(function () {
                        // Temporarily allow access to all logged-in users for testing
                        return true;
                        // To restrict to admin, use:
                        // return Auth::check() && Auth::user()->role === 'admin';
                    })
                    ->canAccessAppSectionTab(function () {
                        // Temporarily allow access to all logged-in users for testing
                        return true;
                        // To restrict to admin, use:
                        // return Auth::check() && Auth::user()->role === 'admin';
                    })
                    ->appAdditionalField([])
            ]);
    }
}
