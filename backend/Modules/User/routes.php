<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Modules\User\Http\Controllers\UserController;
use Modules\User\Http\Controllers\AuthController;


Route::prefix('dashboard')->name('dashboard.')->group(function () {

    Route::get('register', [AuthController::class, 'registerForm'])->name('register.form');
    Route::post('register', [AuthController::class, 'register'])->name('register');

    Route::get('login', [AuthController::class, 'loginForm'])->name('login.form');
    Route::post('login', [AuthController::class, 'login'])->name('login');

    Route::get('forgot-password', [AuthController::class, 'forgotPasswordForm'])->name('forgotPassword.form');
    Route::post('forgot-password', [AuthController::class, 'sendResetLink'])->name('forgotPassword');

    Route::get('reset-password/{token}', [AuthController::class, 'resetPasswordForm'])->name('resetPassword.form');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('resetPassword');

    Route::middleware('auth')->group(function () {

        Route::get('me', [AuthController::class, 'me'])->name('me');

        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    });
});


Route::prefix('users')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('{id}', [UserController::class, 'show']);
    Route::post('/', [UserController::class, 'store']);
    Route::put('{id}', [UserController::class, 'update']);
    Route::delete('{id}', [UserController::class, 'destroy']);
});
