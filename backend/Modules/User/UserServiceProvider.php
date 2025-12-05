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
    public function boot(): void
    {
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
