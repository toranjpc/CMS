<?php

namespace Modules\Sse;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\Sse\Models\SSE;



class SseServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/Database/migrations');


        /*
        Event::listen('eloquent.created: ' . Traffic::class, function ($item) {
            SSE::create([
                // 'message' => ['data' => $item->toArray()],
                'message' => ['data' => $item->id],
                'event' => 'ocr-match',
                'model' => Traffic::class,
                'receiver_id' => $item->gate_number,
            ]);
        });

        Event::listen('eloquent.updated: ' . Traffic::class, function ($item) {
            SSE::create([
                // 'message' => ['data' => $item->toArray()],
                'message' => ['data' => $item->id],
                'event' => 'ocr-match',
                'model' => Traffic::class,
                'receiver_id' => $item->gate_number,
            ]);
        });
        */
    }
}
