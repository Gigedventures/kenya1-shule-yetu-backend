<?php

namespace App\Providers\Filament;

use App\Http\Middleware\ShuleTenancyMiddleware;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class SuperAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('super-admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])

            /*
            |--------------------------------------------------------------------------
            | Core Resources
            |--------------------------------------------------------------------------
            */
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources'
            )

            /*
            |--------------------------------------------------------------------------
            | ShuleYetu Module Resources
            |--------------------------------------------------------------------------
            */
            ->discoverResources(
                in: app_path('Modules/ShuleYetu/Filament/Resources'),
                for: 'App\\Modules\\ShuleYetu\\Filament\\Resources'
            )
            ->discoverResources(
                in: app_path('Modules/ShuleYetu/Academics/Filament/Resources'),
                for: 'App\\Modules\\ShuleYetu\\Academics\\Filament\\Resources'
            )
            ->discoverResources(
                in: app_path('Modules/ShuleYetu/Students/Filament/Resources'),
                for: 'App\\Modules\\ShuleYetu\\Students\\Filament\\Resources'
            )
            ->discoverResources(
                in: app_path('Modules/ShuleYetu/HR/Filament/Resources'),
                for: 'App\\Modules\\ShuleYetu\\HR\\Filament\\Resources'
            )
            ->discoverResources(
                in: app_path('Modules/ShuleYetu/Exams/Filament/Resources'),
                for: 'App\\Modules\\ShuleYetu\\Exams\\Filament\\Resources'
            )
            ->discoverResources(
                in: app_path('Modules/ShuleYetu/Finance/Filament/Resources'),
                for: 'App\\Modules\\ShuleYetu\\Finance\\Filament\\Resources'
            )

            /*
            |--------------------------------------------------------------------------
            | Pages
            |--------------------------------------------------------------------------
            */
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\\Filament\\Pages'
            )
            ->discoverPages(
                in: app_path('Modules/ShuleYetu/Finance/Filament/Pages'),
                for: 'App\\Modules\\ShuleYetu\\Finance\\Filament\\Pages'
            )

            ->pages([
                Pages\Dashboard::class,
            ])

            /*
            |--------------------------------------------------------------------------
            | Widgets
            |--------------------------------------------------------------------------
            */
            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'App\\Filament\\Widgets'
            )

            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->userMenuItems($this->schoolSwitcherMenuItems())

            /*
            |--------------------------------------------------------------------------
            | Middleware
            |--------------------------------------------------------------------------
            */
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
                ShuleTenancyMiddleware::class,
            ]);
    }

    private function schoolSwitcherMenuItems(): array
    {
        $items = [
            MenuItem::make()
                ->label(function (): string {
                    $schoolId = session('active_school_id');

                    if (empty($schoolId)) {
                        return 'School: Not Selected';
                    }

                    $schoolName = DB::table('shule_schools')
                        ->where('id', $schoolId)
                        ->value('name');

                    return $schoolName ? "School: {$schoolName}" : 'School: Not Selected';
                })
                ->url(fn (): string => route('shule.schools.select')),
        ];

        $user = Auth::user();
        if (!$user) {
            return $items;
        }

        $schools = DB::table('shule_school_user as ssu')
            ->join('shule_schools as ss', 'ss.id', '=', 'ssu.school_id')
            ->where('ssu.user_id', $user->getAuthIdentifier())
            ->where('ssu.status', 'active')
            ->where('ss.status', 'active')
            ->orderBy('ss.name')
            ->select(['ss.id', 'ss.name', 'ss.code'])
            ->get();

        $activeSchoolId = (string) session('active_school_id', '');
        foreach ($schools as $school) {
            if ($activeSchoolId === (string) $school->id) {
                continue;
            }

            $items[] = MenuItem::make()
                ->label("Switch to {$school->name}")
                ->url(route('shule.schools.switch.get', ['code' => $school->code]));
        }

        return $items;
    }
}
