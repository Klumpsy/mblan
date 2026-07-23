<?php

namespace App\Providers\Filament;

use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('MBLAN26')
            ->defaultThemeMode(ThemeMode::Dark)
            ->font('Chakra Petch')
            ->colors([
                'primary' => Color::hex('#37c26f'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->authGuard('web')
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString(<<<'HTML'
                    <link rel="preconnect" href="https://fonts.bunny.net">
                    <link href="https://fonts.bunny.net/css?family=press-start-2p:400&display=swap" rel="stylesheet" />
                    <style>
                        .fi-sidebar-header .fi-logo,
                        .fi-topbar .fi-logo {
                            font-family: 'Press Start 2P', monospace !important;
                            font-size: 0.8rem !important;
                            letter-spacing: 0.05em;
                            color: #65E59A !important;
                            text-shadow: 0 0 10px rgba(101, 229, 154, 0.5);
                        }
                        .fi-sidebar-nav-group-label { font-family: 'Press Start 2P', monospace; font-size: 0.55rem; letter-spacing: 0.1em; }
                        .fi-topbar { border-bottom: 1px solid rgba(101, 229, 154, 0.22); }
                        .fi-main { background-image: radial-gradient(900px 400px at 50% -10%, rgba(101,229,154,0.06), transparent 60%); }
                    </style>
                HTML)
            )
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
