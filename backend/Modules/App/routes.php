<?php

use Illuminate\Support\Facades\Route;
use Modules\App\Http\Controllers\AppController;

Route::prefix('apps')->middleware(['auth:sanctum', 'checkPermission'])->name('apps.')->group(function () {
    Route::get('/', [AppController::class, 'index'])->name('index');
    Route::post('/list', [AppController::class, 'index'])->name('indexSearch');
    Route::post('{id}', [AppController::class, 'show'])->name('show');
    Route::post('/', [AppController::class, 'store'])->name('store');
    Route::put('{id}', [AppController::class, 'update'])->name('update');
    Route::delete('{id}', [AppController::class, 'destroy'])->name('destroy');
    Route::patch('{id}/restore', [AppController::class, 'restore'])->name('restore');
    Route::delete('{id}/force', [AppController::class, 'forceDestroy'])->name('force_destroy');
});

Route::prefix('branches')->middleware(['auth:sanctum', 'checkPermission'])->name('branches.')->group(function () {
    Route::get('/', [AppController::class, 'branches'])->name('index');
    Route::post('/list', [AppController::class, 'branches'])->name('indexSearch');
});
