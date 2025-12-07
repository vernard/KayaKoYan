<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class WorkerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('worker')
            ->path('worker')
            ->login()
            ->emailVerification()
            ->colors([
                'primary' => Color::Amber,
            ])
->viteTheme('resources/css/filament/worker/theme.css')
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn (): string => Blade::render('<span class="text-sm text-gray-600 dark:text-gray-400 mr-2">Hi, {{ auth()->user()->name }}</span>')
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => Blade::render('
                    <audio id="notification-sound" src="/sounds/notification.mp3" preload="auto"></audio>
                    <script>window.userId = {{ auth()->id() }};</script>
                    <script>
                        window.REVERB_APP_KEY = "{{ config(\'reverb.apps.apps.0.key\') }}";
                        window.REVERB_HOST = "{{ config(\'reverb.apps.apps.0.options.host\') }}";
                        window.REVERB_PORT = "{{ config(\'reverb.apps.apps.0.options.port\') }}";
                        window.REVERB_SCHEME = "{{ config(\'reverb.apps.apps.0.options.scheme\') }}";
                    </script>
                    @vite(\'resources/js/bootstrap.js\')
                ')
            )
            ->brandName('Kaya Ko Yan - Worker')
            ->discoverResources(in: app_path('Filament/Worker/Resources'), for: 'App\\Filament\\Worker\\Resources')
            ->discoverPages(in: app_path('Filament/Worker/Pages'), for: 'App\\Filament\\Worker\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Worker/Widgets'), for: 'App\\Filament\\Worker\\Widgets')
            ->widgets([
                AccountWidget::class,
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
