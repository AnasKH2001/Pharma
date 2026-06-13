<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PharmacyPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('pharmacy')
            ->path('pharmacy-panel')
            ->login()
            ->authGuard('web')
            ->colors(['primary' => Color::Blue])
            ->discoverResources(in: app_path('Filament/Pharmacy/Resources'), for: 'App\\Filament\\Pharmacy\\Resources')
            ->discoverPages(in: app_path('Filament/Pharmacy/Pages'), for: 'App\\Filament\\Pharmacy\\Pages')
            ->discoverWidgets(in: app_path('Filament/Pharmacy/Widgets'), for: 'App\\Filament\\Pharmacy\\Widgets')
            ->pages([Pages\Dashboard::class])
            ->widgets([
                \App\Filament\Pharmacy\Widgets\PharmacyStatsOverview::class,
                \App\Filament\Pharmacy\Widgets\SalesChartWidget::class,
                \App\Filament\Pharmacy\Widgets\LowStockWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class]);
    }
}
