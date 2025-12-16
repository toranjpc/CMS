<?php

namespace Modules\User;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Modules\App\Jobs\LogActionJob;


class UserServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        //
    }

    /**
     * Configure authentication settings for API usage
     */
    protected function configureAuth(): void
    {
        // Override auth guards to ensure sanctum is available
        config([
            'auth.guards.sanctum' => [
                'driver'   => 'sanctum',
                'provider' => 'users',
            ],
        ]);

        // Override user provider to use our custom User model
        config([
            'auth.providers.users.model' => \Modules\User\Models\User::class,
        ]);

        // Set default guard to sanctum for API requests
        if (request()->is('api/*')) {
            config(['auth.defaults.guard' => 'sanctum']);
        }
    }
    public function boot(): void
    {
        // Override auth config for API authentication
        $this->configureAuth();

        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/Database/migrations');
        // $this->loadFactoriesFrom(__DIR__ . '/Database/factories');
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'user');


        Event::listen(Login::class, function ($event) {
            $userId = $event->user->id ?? null;
            LogActionJob::dispatch(
                $userId,
                'users',
                $userId,
                'login',
                [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]
            );
        });
        Event::listen(Logout::class, function ($event) {
            $userId = $event->user->id ?? null;
            LogActionJob::dispatch(
                $userId,
                'users',
                $userId,
                'logout',
                [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]
            );
        });
    }
}
