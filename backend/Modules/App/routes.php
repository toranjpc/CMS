<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\ProductController;


Route::prefix('apps')->middleware('auth:sanctum')->name('apps.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('{id}', [ProductController::class, 'show'])->name('show');
    Route::post('/', [ProductController::class, 'store'])->name('store');
    Route::put('{id}', [ProductController::class, 'update'])->name('update');
    Route::delete('{id}', [ProductController::class, 'destroy'])->name('destroy');
});
