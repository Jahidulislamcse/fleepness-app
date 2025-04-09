<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\OTPAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SizeTemplateController;
use App\Http\Controllers\user\ProductController;
use App\Http\Controllers\user\UserProductController;
use App\Http\Controllers\user\UserVendorController;
use App\Http\Controllers\user\UserVendorFollowController;
use App\Http\Controllers\user\UserVendorReviewController;
use App\Http\Controllers\SMSController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Vendor\VendorProductController;

RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

Route::middleware(['api', 'throttle:api'])->group(function () {
    Route::get('/auth/{provider}', [SocialLoginController::class, 'redirectToProvider']);
    Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback']);

    Route::post('/register', [OTPAuthController::class, 'register']);
    Route::post('/send-sms', [SMSController::class, 'sendSMS']);

    Route::post('/verify-otp', [OTPAuthController::class, 'verifyOtp']);
    Route::post('/resend-otp', [OTPAuthController::class, 'resendOtp']);

    Route::post('/seller/register', [UserController::class, 'application']);

    Route::post('/login', [AuthenticatedSessionController::class, 'storeapi']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthenticatedSessionController::class, 'destroyapi']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/seller/status', [UserController::class, 'checkStatus']);
        Route::get('/user/profile', [UserController::class, 'checkProfile']);
        Route::get('/notifications', [NotificationController::class, 'getNotifications']);
        Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead']);

        // Only admins can access these routes
        Route::middleware(['role:admin'])->group(function () {
            Route::post('/admin/seller-approve', [UserController::class, 'approveSeller']);
            Route::post('/admin/seller-reject', [UserController::class, 'rejectSeller']);
            Route::get('/admin/seller-requests', [UserController::class, 'sellerRequest']);
        });
    });

    Route::get('/search', [UserProductController::class, 'search']);

    Route::get('/vendorlist ', [UserVendorController::class, 'vendorlist']);
    Route::get('/vendorlist/{vendor} ', [UserVendorController::class, 'vendorData']);

    Route::get('/vendorlist/{vendor}/product/in-price-range ', [UserProductController::class, 'getProductsByPriceRange']);
    Route::get('/vendorlist/{vendor}/product/in-price-category', [UserProductController::class, 'getProductsInPriceCategory']);
    Route::get('/vendorlist/{vendor}/allproduct ', [UserProductController::class, 'getAllProducts']);

    Route::get('/vendorlist/{vendor}/shortvideo ', [UserVendorController::class, 'getShortVideos']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/vendor/{vendor_id}/review', [UserVendorReviewController::class, 'store']);
        Route::delete('/vendor/review/{review_id}/delete', [UserVendorReviewController::class, 'delete']);
    });
    Route::get('/vendor/{vendor_id}/reviews', [UserVendorReviewController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/vendor/{vendor_id}/follow', [UserVendorFollowController::class, 'follow']);
        Route::get('/vendor/{vendor_id}/unfollow ', [UserVendorFollowController::class, 'unfollow']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        // Create a size template
        Route::post('/size-template/create', [SizeTemplateController::class, 'store']);

        // Add a size to a template
        Route::post('/size-templates/{templateId}/sizes', [SizeTemplateController::class, 'addSizeToTemplate']);

        // Get all size templates for the authenticated seller
        Route::get('/size-templates', [SizeTemplateController::class, 'getTemplates']);

        // Delete a size template
        Route::delete('/size-templates/{id}', [SizeTemplateController::class, 'destroy']);

        //store product
        Route::post('/product/create', [VendorProductController::class, 'store']);

        //show products
        Route::get('/product/index', [VendorProductController::class, 'show']);
    });


});
