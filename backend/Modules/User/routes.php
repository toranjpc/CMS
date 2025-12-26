<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Modules\User\Http\Controllers\UserController;
use Modules\User\Http\Controllers\AuthController;


Route::prefix('auth')->middleware('api')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::get('login', function () {
        return response()->json(['message' => 'login'], 200);
    })->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('resetPassword');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    });
});


Route::prefix('users')
    ->name('users.')
    ->middleware(['api', 'checkPermission'])
    ->group(function () { //auth:sanctum

        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [UserController::class, 'category_index'])->name('index');
            // Route::get('{category}', [UserController::class, 'category_show'])->name('show');
            Route::post('/', [UserController::class, 'category_store'])->name('store');
            Route::put('{category}', [UserController::class, 'category_update'])->name('update');
            Route::delete('{category}', [UserController::class, 'category_destroy'])->name('destroy');
            Route::delete('{id}/force', [UserController::class, 'category_force_destroy'])->name('force_destroy');
            Route::patch('{id}/restore', [UserController::class, 'category_restore'])->name('restore');
        });

        Route::prefix('jobs')->name('jobs.')->group(function () {
            Route::get('/', [UserController::class, 'job_index'])->name('index');
            // Route::get('{job}', [UserController::class, 'job_show'])->name('show');
            Route::post('/', [UserController::class, 'job_store'])->name('store');
            Route::put('{job}', [UserController::class, 'job_update'])->name('update');
            Route::delete('{job}', [UserController::class, 'job_destroy'])->name('destroy');
            Route::delete('{id}/force', [UserController::class, 'job_force_destroy'])->name('force_destroy');
            Route::patch('{id}/restore', [UserController::class, 'job_restore'])->name('restore');
        });

        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('{user}', [UserController::class, 'show'])->name('show');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('{user}', [UserController::class, 'update'])->name('update');
        Route::delete('{user}', [UserController::class, 'destroy'])->name('destroy');
    });
