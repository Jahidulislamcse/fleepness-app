<?php

use App\Models\Livestream;
use App\Http\Controllers\Admin\ShopCategoryController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\OTPAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LiveStreaming\GetLivestreamPublisherTokenController;
use App\Http\Controllers\LiveStreaming\GetLivestreamSubscriberTokenController;
use App\Http\Controllers\LiveStreaming\LivestreamController;
use App\Http\Controllers\LiveStreaming\LivestreamProductController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SizeTemplateController;
use App\Http\Controllers\user\UserProductController;
use App\Http\Controllers\user\UserVendorController;
use App\Http\Controllers\user\UserVendorFollowController;
use App\Http\Controllers\user\UserVendorReviewController;
use App\Http\Controllers\SMSController;
use App\Http\Controllers\user\UserSearchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\Vendor\VendorProductController;

// Route::get('/livestreams/{livestream:id}', function (Livestream $livestream) {
//     dd($livestream);
//     return $livestream;
// });

RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

Route::middleware(['api', 'throttle:api'])->group(function () {

    Route::get('/auth/{provider}', [SocialLoginController::class, 'redirectToProvider']); //Facebook and google authentication

    //Facebook and google authentication callback
    Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback']);
    Route::post('/register', [OTPAuthController::class, 'register']);   //Otp based registration
    Route::post('/send-sms', [SMSController::class, 'sendSMS']);     //Sending sms to phone number
    Route::post('/send-login-otp', [AuthenticatedSessionController::class, 'apiSendOtp']);
    Route::post('/verify-cache-otp', [AuthenticatedSessionController::class, 'verifyCacheOtp']);    //Verifying OTP
    Route::post('/verify-otp', [OTPAuthController::class, 'verifyOtp']);    //Verifying OTP
    Route::post('/resend-otp', [OTPAuthController::class, 'resendOtp']);    //Resending OTP
    Route::post('/seller/register', [UserController::class, 'application']);    //Seller registration
    Route::middleware('auth:sanctum')->post('/seller/application', [UserController::class, 'applyForSeller']); //Regular user to seller application
    Route::post('/     ', [AuthenticatedSessionController::class, 'storeapi']); //Login to the system

    //Logout from the system
    Route::middleware('auth:sanctum')->post('/logout', [AuthenticatedSessionController::class, 'destroyapi']);

    Route::middleware(['auth:sanctum'])->group(function () {
        //Checking seller approval status for determining UI
        Route::get('/seller/status', [UserController::class, 'checkStatus']);

        //Checking Seller profile info, specially role for determining UI
        Route::get('/user/profile', [UserController::class, 'checkProfile']);
        Route::get('/notifications', [NotificationController::class, 'getNotifications']);  //fetching Notifications
        Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead']);  //Marking a notification as read

        // Only admins can access these routes
        Route::middleware(['role:admin'])->group(function () {
            Route::post('/admin/seller-approve', [UserController::class, 'approveSeller']);  //Approving a seller request
            Route::post('/admin/seller-reject', [UserController::class, 'rejectSeller']);    //Rejecting a seller request
            Route::get('/admin/seller-requests', [UserController::class, 'sellerRequest']);  //Getting all seller requests
        });
    });

    Route::post('/send-email', [EmailController::class, 'sendTestEmail']);
    Route::post('/get-email', [EmailController::class, 'receiveCustomerEmail']);

    Route::get('/search/product', [UserProductController::class, 'search']); //Searching a product
    Route::get('/search', [UserSearchController::class, 'search']); //Searching by seller/tag

    Route::get('/vendorlist ', [UserVendorController::class, 'vendorlist']);            //Show all sellers in list form
    Route::get('/vendorlist/{vendor} ', [UserVendorController::class, 'vendorData']);   //Show a particular seller data
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
        Route::get('/vendor/{vendor_id}/follow', [UserVendorFollowController::class, 'follow']);        //Follow a Seller
        Route::get('/vendor/{vendor_id}/unfollow ', [UserVendorFollowController::class, 'unfollow']);   //Unfollow a seller
    });

    Route::get('/get-categories', [CategoryController::class, 'getCategories'])->name('get.categories');
    Route::get('/get-random-tags', [TagController::class, 'getTagsRandom'])->name('get.random.tags');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/size-template/create', [SizeTemplateController::class, 'store']);              // Create a size template
        Route::post('/size-templates/{templateId}/sizes', [SizeTemplateController::class, 'addSizeToTemplate']); // Add a size to a template
        Route::get('/size-templates', [SizeTemplateController::class, 'getTemplates']);         // Get all size templates for the authenticated seller
        Route::delete('/size-templates/{id}', [SizeTemplateController::class, 'destroy']);      // Delete a size template

        Route::get('/get-tags', [TagController::class, 'getTags'])->name('get.tags');
        Route::post('/product/create', [VendorProductController::class, 'store']);              //store product
        Route::get('/product/index', [VendorProductController::class, 'show']);                 //show products
        Route::post('/products/{id}', [VendorProductController::class, 'update']);              //Update Product
        Route::post('/products/soft-delete/{id}', [VendorProductController::class, 'destroy']); //Soft Deleting a product
        Route::post('/products/inactive/{id}', [VendorProductController::class, 'inactive']);   //Inactivating a product
        Route::post('/products/active/{id}', [VendorProductController::class, 'active']);       //Activating a product

        Route::get('/shop-categories', [ShopCategoryController::class, 'index']);          // List all categories
        Route::post('/shop-categories', [ShopCategoryController::class, 'store']);         // Create new category
        Route::get('/shop-categories/{id}', [ShopCategoryController::class, 'show']);      // View single category
        Route::put('/shop-categories/{id}', [ShopCategoryController::class, 'update']);    // Update category
        Route::delete('/shop-categories/{id}', [ShopCategoryController::class, 'destroy']); // Delete category
    });

    // GET /api/livestreams
    Route::get('livestreams', [LivestreamController::class, 'index'])->name('livestreams.index');

    // GET /api/livestreams/{livestream}
    Route::get('livestreams/{livestream}', [LivestreamController::class, 'show'])->name('livestreams.show');

    // POST /api/livestreams
    Route::post('livestreams', [LivestreamController::class, 'store'])->name('livestreams.store');

    // PUT/PATCH /api/livestreams/{livestream}
    Route::match(['put', 'patch'], 'livestreams/{livestream}', [LivestreamController::class, 'update'])->name('livestreams.update');

    Route::get('livestreams/{livestream}/publisher-token', GetLivestreamPublisherTokenController::class)
        ->middleware('auth:sanctum')
        ->name('livestreams.get-publisher-token');

    Route::get('livestreams/{livestream}/subscriber-token', GetLivestreamSubscriberTokenController::class)
        ->name('livestreams.get-subscriber-token');

    Route::post('livestreams/{ls}/products', [LivestreamProductController::class, 'store'])
        ->middleware('auth:sanctum')
        ->name('livestream-products.store');

    Route::delete('livestreams/{livestream}/products', [LivestreamProductController::class, 'destroy'])
        ->middleware('auth:sanctum')
        ->name('livestream-products.destroy');
});
