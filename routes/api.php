<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\OTPAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SizeTemplateController;
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
    //Facebook and google authentication
    Route::get('/auth/{provider}', [SocialLoginController::class, 'redirectToProvider']);

    //Facebook and google authentication callback
    Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback']);

    //Otp based registration
    Route::post('/register', [OTPAuthController::class, 'register']);

    //Sending sms to phone number
    Route::post('/send-sms', [SMSController::class, 'sendSMS']);

    //Verifying OTP
    Route::post('/verify-otp', [OTPAuthController::class, 'verifyOtp']);

    //Resending OTP
    Route::post('/resend-otp', [OTPAuthController::class, 'resendOtp']);

    //Seller registration
    Route::post('/seller/register', [UserController::class, 'application']);

    //Login to the system
    Route::post('/login', [AuthenticatedSessionController::class, 'storeapi']);

    //Logout from the system
    Route::middleware('auth:sanctum')->post('/logout', [AuthenticatedSessionController::class, 'destroyapi']);

    Route::middleware(['auth:sanctum'])->group(function () {
        //Checking seller approval status for determining UI
        Route::get('/seller/status', [UserController::class, 'checkStatus']);

        //Checking Seller profile info, specially role for determining UI
        Route::get('/user/profile', [UserController::class, 'checkProfile']);

        //fetching BNotifications
        Route::get('/notifications', [NotificationController::class, 'getNotifications']);

        //Marking a notification as read
        Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead']);

        // Only admins can access these routes
        Route::middleware(['role:admin'])->group(function () {
            //Approving a seller request
            Route::post('/admin/seller-approve', [UserController::class, 'approveSeller']);

            //Rejecting a seller request
            Route::post('/admin/seller-reject', [UserController::class, 'rejectSeller']);

            //Getting all seller requests
            Route::get('/admin/seller-requests', [UserController::class, 'sellerRequest']);
        });
    });

    //Searching a product by name, description, category
    Route::get('/search', [UserProductController::class, 'search']);

    //Show all sellers in list form
    Route::get('/vendorlist ', [UserVendorController::class, 'vendorlist']);

    //Show a particular seller data
    Route::get('/vendorlist/{vendor} ', [UserVendorController::class, 'vendorData']);

    //Show products based on price range
    Route::get('/vendorlist/{vendor}/product/in-price-range ', [UserProductController::class, 'getProductsByPriceRange']);

    //Show products on price category like low, medium, high
    Route::get('/vendorlist/{vendor}/product/in-price-category', [UserProductController::class, 'getProductsInPriceCategory']);

    //Show all product of a particular seller
    Route::get('/vendorlist/{vendor}/allproduct ', [UserProductController::class, 'getAllProducts']);

    //Show short videos of a particular seller
    Route::get('/vendorlist/{vendor}/shortvideo ', [UserVendorController::class, 'getShortVideos']);

    Route::middleware(['auth:sanctum'])->group(function () {
        //Giving a review to a seller
        Route::post('/vendor/{vendor_id}/review', [UserVendorReviewController::class, 'store']);

        //Removing review from a seller
        Route::delete('/vendor/review/{review_id}/delete', [UserVendorReviewController::class, 'delete']);
    });

    //See all reviews of a seller
    Route::get('/vendor/{vendor_id}/reviews', [UserVendorReviewController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function () {
        //Follow a Seller
        Route::get('/vendor/{vendor_id}/follow', [UserVendorFollowController::class, 'follow']);

        //Unfollow a seller
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

        //Update Product
        Route::post('/products/{id}', [VendorProductController::class, 'update']);
    });
});
