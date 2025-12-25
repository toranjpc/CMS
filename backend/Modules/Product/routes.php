<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\ProductController;


Route::prefix('products')->middleware(['auth:sanctum', 'checkPermission'])->name('products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('{id}', [ProductController::class, 'show'])->name('show');
    Route::post('/', [ProductController::class, 'store'])->name('store');
    Route::put('{id}', [ProductController::class, 'update'])->name('update');
    Route::delete('{id}', [ProductController::class, 'destroy'])->name('destroy');



    Route::prefix('options')->name('options.')->group(function () {
        Route::get('/', [ProductController::class, 'options_index'])->name('index');
        Route::get('{id}', [ProductController::class, 'options_show'])->name('show');
        Route::post('/', [ProductController::class, 'options_store'])->name('store');
        Route::put('{id}', [ProductController::class, 'options_update'])->name('update');
        Route::delete('{id}', [ProductController::class, 'options_destroy'])->name('destroy');
    });

    Route::prefix('categores')->name('categores.')->group(function () {
        Route::get('/', [ProductController::class, 'categores_index'])->name('index');
        Route::get('{id}', [ProductController::class, 'categores_show'])->name('show');
        Route::post('/', [ProductController::class, 'categores_store'])->name('store');
        Route::put('{id}', [ProductController::class, 'categores_update'])->name('update');
        Route::delete('{id}', [ProductController::class, 'categores_destroy'])->name('destroy');
    });
});
