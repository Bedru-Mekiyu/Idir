<?php

namespace App\Providers\Filament;

use App\Filament\Pages\CommitteeLogin;
use App\Filament\Pages\Tenancy\EditIdirProfile;
use App\Filament\Pages\Tenancy\RegisterIdir;
use App\Models\Idir;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\Enums\ThemeMode;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class CommitteePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('committee')
            ->path('committee')
            ->login(CommitteeLogin::class)
            ->registration()
            ->tenant(Idir::class)
            ->tenantRegistration(RegisterIdir::class)
            ->tenantProfile(EditIdirProfile::class)
            ->defaultThemeMode(ThemeMode::Light)
            ->darkMode(false)
            ->colors([
                'primary' => Color::Indigo,
                'danger' => Color::Rose,
                'warning' => Color::Orange,
                'success' => Color::Emerald,
                'info' => Color::Blue,
            ])
            ->favicon(asset('favicon.svg'))
            ->brandLogo(fn () => view('components.logo'))
            ->brandLogoHeight('2rem')
            
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
