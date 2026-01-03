<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\ProductController;




Route::prefix('products')
    ->name('products.')
    ->middleware(['api', 'checkPermission'])
    ->group(function () { //'auth:sanctum', 

        Route::prefix('features')->name('features.')->group(function () {
            Route::get('/', [ProductController::class, 'feature_index'])->name('index');
            Route::get('{id}', [ProductController::class, 'feature_show'])->name('show');
            Route::post('/', [ProductController::class, 'feature_store'])->name('store');
            Route::put('{feature}', [ProductController::class, 'feature_update'])->name('update');
            Route::delete('{feature}', [ProductController::class, 'feature_destroy'])->name('destroy');
            Route::delete('{id}/force', [ProductController::class, 'feature_force_destroy'])->name('forceDestroy');
            Route::patch('{id}/restore', [ProductController::class, 'feature_restore'])->name('restore');
        });


        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [ProductController::class, 'category_index'])->name('index');
            Route::get('{id}', [ProductController::class, 'category_show'])->name('show');
            Route::post('/', [ProductController::class, 'category_store'])->name('store');
            Route::put('{category}', [ProductController::class, 'category_update'])->name('update');
            Route::delete('{category}', [ProductController::class, 'category_destroy'])->name('destroy');
            Route::delete('{id}/force', [ProductController::class, 'category_force_destroy'])->name('forceDestroy');
            Route::patch('{id}/restore', [ProductController::class, 'category_restore'])->name('restore');
        });


        Route::prefix('units')->name('units.')->group(function () {
            Route::get('/', [ProductController::class, 'unit_index'])->name('index');
            Route::get('{id}', [ProductController::class, 'unit_show'])->name('show');
            Route::post('/', [ProductController::class, 'unit_store'])->name('store');
            Route::put('{unit}', [ProductController::class, 'unit_update'])->name('update');
            Route::delete('{unit}', [ProductController::class, 'unit_destroy'])->name('destroy');
            Route::delete('{id}/force', [ProductController::class, 'unit_force_destroy'])->name('forceDestroy');
            Route::patch('{id}/restore', [ProductController::class, 'unit_restore'])->name('restore');
        });

        Route::prefix('brands')->name('brands.')->group(function () {
            Route::get('/', [ProductController::class, 'brand_index'])->name('index');
            Route::get('{id}', [ProductController::class, 'brand_show'])->name('show');
            Route::post('/', [ProductController::class, 'brand_store'])->name('store');
            Route::put('{brand}', [ProductController::class, 'brand_update'])->name('update');
            Route::delete('{brand}', [ProductController::class, 'brand_destroy'])->name('destroy');
            Route::delete('{id}/force', [ProductController::class, 'brand_force_destroy'])->name('forceDestroy');
            Route::patch('{id}/restore', [ProductController::class, 'brand_restore'])->name('restore');
        });


        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('{id}', [ProductController::class, 'show'])->name('show');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::put('{id}', [ProductController::class, 'update'])->name('update');
        Route::delete('{product}', [ProductController::class, 'destroy'])->name('destroy');
        Route::delete('{id}/force', [ProductController::class, 'force_destroy'])->name('forceDestroy');
        Route::patch('{id}/restore', [ProductController::class, 'restore'])->name('restore');

    });
