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



Route::prefix('users')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('{id}', [UserController::class, 'show']);
    Route::post('/', [UserController::class, 'store']);
    Route::put('{id}', [UserController::class, 'update']);
    Route::delete('{id}', [UserController::class, 'destroy']);
});
