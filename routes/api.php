<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\user\ProductController;
use App\Http\Controllers\user\UserProductController;
use App\Http\Controllers\user\UserVendorController;
use App\Http\Controllers\user\UserVendorReviewController;

RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

Route::middleware(['api', 'throttle:api'])->group(function () {
    Route::post('/login', [AuthenticatedSessionController::class, 'storeapi']);
    Route::get('/auth/{provider}', [SocialLoginController::class, 'redirectToProvider']);
    Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback']);
    Route::get('/search', [UserProductController::class, 'search']);
    Route::get('/vendorlist ', [UserVendorController::class, 'vendorlist']);
    Route::get('/vendorlist/{vendor} ', [UserVendorController::class, 'vendorData']);
    Route::get('/vendorlist/{vendor}/mediumprice ', [UserProductController::class, 'getProductsByPriceRange']);
    Route::get('/vendorlist/{vendor}/mediumprice ', [UserProductController::class, 'getProductsByPriceRange']);
    Route::get('/vendorlist/{vendor}/allproduct ', [UserProductController::class, 'getAllProducts']);
    Route::get('/vendorlist/{vendor}/shortvideo ', [UserVendorController::class, 'getShortVideos']);
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/vendor/{vendor_id}/review', [UserVendorReviewController::class, 'store']);
    });
    Route::get('/vendor/{vendor_id}/reviews', [UserVendorReviewController::class, 'index']);
});
