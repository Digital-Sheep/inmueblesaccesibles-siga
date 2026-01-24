<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Actions\Action;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('/')
            ->login()
            ->colors([
                'primary' => Color::Indigo,
                'gray' => Color::Slate,
                'success' => Color::Emerald,
                'danger'  => Color::Red,
                'warning' => Color::Amber,
            ])
            ->font('Poppins')
            ->darkMode(false)
            ->defaultThemeMode(ThemeMode::Light)
            ->brandName('SIGA - Inmuebles Accesibles')
            ->brandLogo(asset('images/logo-dark.png'))
            ->brandLogoHeight('4rem')
            ->favicon(asset('images/favicon.ico'))
            ->sidebarCollapsibleOnDesktop(true)
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Módulo comercial'),
                NavigationGroup::make()
                    ->label('Módulo jurídico'),
                NavigationGroup::make()
                    ->label('Módulo financiero'),
                NavigationGroup::make()
                    ->label('Configuración')
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\Filament\Clusters')
            ->maxContentWidth(Width::Full)
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
            ->userMenuItems([
                'profile' => Action::make('profile')
                    ->label(
                        function () {
                            /** @var \App\Models\User $user */
                            $user = Auth::user();

                            return $user->name;
                        }
                    )
                    ->icon('heroicon-o-user-circle'),

                // Sucursal
                Action::make('sucursal')
                    ->label(
                        function () {
                            /** @var \App\Models\User $user */
                            $user = Auth::user();

                            return $user->sucursal->nombre ?? 'Sin sucursal';
                        }
                    )
                    ->icon('heroicon-o-building-office-2')
                    ->color('gray')
                    ->disabled(),

                Action::make('roles')
                    ->label(
                        function () {
                            /** @var \App\Models\User $user */
                            $user = Auth::user();

                            $roles = $user->roles
                                ->pluck('name')
                                ->map(fn($role) => str_replace('_', ' ', $role))
                                ->join(', ');

                            return $roles;
                        }
                    )
                    ->icon('heroicon-o-shield-check')
                    ->color('gray')
                    ->disabled(),
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
            ->plugins([
                FilamentShieldPlugin::make(),

                FilamentFullCalendarPlugin::make()
                    ->selectable()
                    ->editable()
                    ->timezone('America/Mexico_City')
                    ->locale('es'),
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn(): string => Blade::render('<style>
                    /* Forzar margen superior en la barra del calendario */
                    .fc .fc-toolbar.fc-header-toolbar {
                        margin-top: 2rem !important; /* Espacio extra arriba */
                        margin-bottom: 1.5rem !important; /* Espacio abajo */
                    }

                    /* Separar todo el contenedor del calendario del título del Widget */
                    .fi-wi-content {
                        padding-top: 1rem !important;
                    }
                </style>')
            )
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
