<?php

namespace Modules\Accounting;

use Illuminate\Support\ServiceProvider;
use Modules\Accounting\Models\InvoiceItem;
use Modules\Accounting\Observers\InvoiceItemObserver;

class AccountingServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        //
    }
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/Database/migrations');
        $this->loadFactoriesFrom(__DIR__ . '/Database/factories');
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'product');

        // Load commands
        // if ($this->app->runningInConsole()) {
        //     $this->commands([
        //         \Modules\Accounting\Console\Commands\UpdateProductStock::class,
        //     ]);
        // }

        // Register observers
        InvoiceItem::observe(InvoiceItemObserver::class);
    }
}
