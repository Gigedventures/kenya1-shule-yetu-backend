<?php

namespace App\Modules\ShuleYetu\Providers;

use Illuminate\Support\ServiceProvider;

class ShuleYetuServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(app_path('Modules/ShuleYetu/routes/web.php'));
    }
}

