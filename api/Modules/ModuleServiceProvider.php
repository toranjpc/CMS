<?php

namespace Modules;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulesPath = ["User", "Sse"];

        foreach ($modulesPath as $moduleName) {
            $providerClass = "Modules\\{$moduleName}\\{$moduleName}ServiceProvider";
            if (class_exists($providerClass)) {
                $this->app->register($providerClass);
            }
        }

        $helpers = __DIR__ . '/helpers.php';
        if (file_exists($helpers)) {
            require_once $helpers;
        }
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
    }
}
