<?php

namespace Modules\App;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        //
    }
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/Database/migrations');
        // $this->loadFactoriesFrom(__DIR__ . '/Database/factories');
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'app');
    }
}
