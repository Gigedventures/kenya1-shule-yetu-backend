<?php

namespace App\Providers;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SchoolContext::class);
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        $this->loadMigrationsFrom(app_path('Modules/ShuleYetu/Database/Migrations'));
    }
}
