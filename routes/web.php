<?php

use Illuminate\Support\Facades\Route;

/**
 * Service (서비스) 사용자 페이지 라우트
 *
 * @description
 * 사용자가 접근할 수 있는 서비스 기능을 제공합니다.
 */
Route::middleware('web')->prefix('services')->name('services.')->group(function () {
    // 서비스 목록 페이지
    Route::get('/', \Jiny\Service\Http\Controllers\Site\Services\IndexController::class)
        ->name('index');

    // 서비스 검색
    Route::get('/search', \Jiny\Service\Http\Controllers\Site\Services\SearchController::class)
        ->name('search');

    // 서비스 카테고리별 목록
    Route::get('/category/{slug}', \Jiny\Service\Http\Controllers\Site\Services\CategoryController::class)
        ->name('category');

    // 서비스 상세 페이지
    Route::get('/{slug}', \Jiny\Service\Http\Controllers\Site\Services\ShowController::class)
        ->name('show')
        ->where('slug', '[a-zA-Z0-9\-_]+');
});