<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Modules\User\Http\Controllers\UserController;
use Modules\User\Http\Controllers\AuthController;


Route::prefix('dashboard')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('register', [AuthController::class, 'registerpost'])->name('registerpost');
    Route::get('login', [AuthController::class, 'login'])->name('login');
    Route::post('login', [AuthController::class, 'loginpost'])->name('loginpost');
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('me', [AuthController::class, 'me'])->name('me');
    });
});


Route::prefix('users')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('{id}', [UserController::class, 'show']);
    Route::post('/', [UserController::class, 'store']);
    Route::put('{id}', [UserController::class, 'update']);
    Route::delete('{id}', [UserController::class, 'destroy']);
});
