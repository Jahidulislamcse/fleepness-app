<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\user\ProductController;
use App\Http\Controllers\user\UserProductController;
use App\Http\Controllers\user\UserVendorController;

RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

Route::middleware(['api', 'throttle:api'])->group(function () {
    Route::get('/auth/{provider}', [SocialLoginController::class, 'redirectToProvider']);
    Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback']);
    Route::get('/search', [UserProductController::class, 'search']);
    Route::get('/vendorlist ', [UserVendorController::class, 'vendorlist']);
    Route::get('/vendorlist/{vendor} ', [UserVendorController::class, 'vendorData']);
    Route::get('/vendorlist/{vendor}/mediumprice ', [UserProductController::class, 'getProductsByPriceRange']);
    Route::get('/vendorlist/{vendor}/mediumprice ', [UserProductController::class, 'getProductsByPriceRange']);
    Route::get('/vendorlist/{vendor}/allproduct ', [UserProductController::class, 'getAllProducts']);
    Route::get('/vendorlist/{vendor}/shortvideo ', [UserVendorController::class, 'getShortVideos']);
});
