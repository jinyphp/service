<?php

use Illuminate\Support\Facades\Route;

/**
 * Services 관리 라우트
 *
 * @description
 * 서비스 관리 시스템을 위한 라우트입니다.
 * 서비스 CRUD 기능을 제공합니다.
 */
Route::prefix('admin/site/services')->middleware(['web', 'admin'])->name('admin.site.services.')->group(function () {
    Route::get('/', \Jiny\Service\Http\Controllers\Admin\Services\IndexController::class)->name('index');
    Route::get('/create', \Jiny\Service\Http\Controllers\Admin\Services\CreateController::class)->name('create');
    Route::post('/', \Jiny\Service\Http\Controllers\Admin\Services\StoreController::class)->name('store');
    Route::get('/{id}', \Jiny\Service\Http\Controllers\Admin\Services\ShowController::class)->name('show')->where(['id' => '[0-9]+']);
    Route::get('/{id}/edit', \Jiny\Service\Http\Controllers\Admin\Services\EditController::class)->name('edit')->where(['id' => '[0-9]+']);
    Route::put('/{id}', \Jiny\Service\Http\Controllers\Admin\Services\UpdateController::class)->name('update')->where(['id' => '[0-9]+']);
    Route::delete('/{id}', \Jiny\Service\Http\Controllers\Admin\Services\DestroyController::class)->name('destroy')->where(['id' => '[0-9]+']);

    // Service Categories 관리
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', \Jiny\Service\Http\Controllers\Admin\Services\Categories\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Service\Http\Controllers\Admin\Services\Categories\CreateController::class)->name('create');
        Route::post('/', \Jiny\Service\Http\Controllers\Admin\Services\Categories\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Service\Http\Controllers\Admin\Services\Categories\ShowController::class)->name('show')->where(['id' => '[0-9]+']);
        Route::get('/{id}/edit', \Jiny\Service\Http\Controllers\Admin\Services\Categories\EditController::class)->name('edit')->where(['id' => '[0-9]+']);
        Route::put('/{id}', \Jiny\Service\Http\Controllers\Admin\Services\Categories\UpdateController::class)->name('update')->where(['id' => '[0-9]+']);
        Route::delete('/{id}', \Jiny\Service\Http\Controllers\Admin\Services\Categories\DestroyController::class)->name('destroy')->where(['id' => '[0-9]+']);
    });
});