<?php

use Illuminate\Support\Facades\Route;

use function PHPUnit\Framework\isNull;

Route::get('/', function () {
    return '';
});
Route::get('/test', function () {
    return \Modules\User\Models\User::first();
    return view('welcome');
});

Route::get('/routes', function () {

    $routes = collect(Route::getRoutes())->map(function ($route) {
        return [
            'method' => implode('|', $route->methods()),
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'action' => $route->getActionName(),
            // 'data' => $route,
        ];
    });

    return response()->json($routes->values());
});
//php artisan optimize:clear