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
    ->middleware(['checkPermission'])
    ->group(function () { //auth:sanctum

        Route::prefix('jobs')->name('jobs.')->group(function () {
            Route::get('/', [UserController::class, 'job_index'])->name('index');
            Route::get('{id}', [UserController::class, 'job_show'])->name('show');
            Route::post('/', [UserController::class, 'job_store'])->name('store');
            Route::put('{id}', [UserController::class, 'job_update'])->name('update');
            Route::delete('{id}', [UserController::class, 'job_destroy'])->name('destroy');
        });

        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('{id}', [UserController::class, 'show'])->name('show');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('{id}', [UserController::class, 'update'])->name('update');
        Route::delete('{id}', [UserController::class, 'destroy'])->name('destroy');
    });
