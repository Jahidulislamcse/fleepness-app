<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SMSController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\user\CartController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Auth\OTPAuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SizeTemplateController;
use App\Http\Controllers\user\AddressController;
use App\Http\Controllers\DeliveryModelController;
use App\Http\Controllers\user\UserSearchController;
use App\Http\Controllers\user\UserVendorController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\user\UserProductController;
use App\Http\Controllers\user\UserProfileController;
use App\Http\Controllers\Admin\AdminSliderController;
use App\Http\Controllers\ShortsInteractionController;
use App\Http\Controllers\Admin\ShopCategoryController;
use App\Http\Controllers\Vendor\VendorProductController;
use App\Http\Controllers\user\UserVendorFollowController;
use App\Http\Controllers\user\UserVendorReviewController;
use App\Http\Controllers\Vendor\VendorShortVideoController;
use App\Http\Controllers\LiveStreaming\LivestreamController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\LiveStreaming\LivestreamCommentController;
use App\Http\Controllers\LiveStreaming\LivestreamProductController;
use App\Http\Controllers\LiveStreaming\GetLivestreamPublisherTokenController;
use App\Http\Controllers\LiveStreaming\GetLivestreamSubscriberTokenController;

Route::middleware(['api', 'throttle:api'])->group(function (): void {

    Route::get('/auth/{provider}', [SocialLoginController::class, 'redirectToProvider']); // Facebook and google authentication

    // Facebook and google authentication callback
    Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback']);
    Route::post('/register', [OTPAuthController::class, 'register']);   // Otp based registration 1
    Route::post('/send-sms', [SMSController::class, 'sendSMS']);     // Sending sms to phone number 1
    Route::post('/send-login-otp', [AuthenticatedSessionController::class, 'apiSendOtp']); //
    Route::post('/verify-otp', [OTPAuthController::class, 'verifyOtp']);    // Verifying OTP 1
    Route::post('/resend-otp', [OTPAuthController::class, 'resendOtp']);    // Resending OTP 1
    Route::post('/seller/register', [UserController::class, 'application']);    // Seller registration 1
    // Route::post('/', [AuthenticatedSessionController::class, 'storeapi']); //Login to the system

    Route::get('/payment_methods', [UserProfileController::class, 'getPaymenMethods']);

    Route::middleware(['auth:sanctum'])->group(function (): void {
        Route::post('/seller/application', [UserController::class, 'applyForSeller']); // Regular user to seller application

        // Logout from the system
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroyapi']);
        Route::post('/store-device-token', [AuthenticatedSessionController::class, 'storeDeviceToken'])->name('auth.store-device-token');

        Route::get('/user/profile', [UserProfileController::class, 'show']); // Show user profile
        Route::post('/seller/profile', [UserProfileController::class, 'updateSeller']); // Update Seller profile
        Route::post('/user/profile', [UserProfileController::class, 'updateUser']); // Update user profile
        Route::post('/my/payments', [UserProfileController::class, 'updatePaymentAccounts']);
        Route::get('/my/payments', [UserProfileController::class, 'getPaymentAccounts']);

        // Checking seller approval status for determining UI
        Route::get('/seller/status', [UserController::class, 'checkStatus']);

        // Checking Seller profile info, specially role for determining UI
        Route::get('/me/role', [UserController::class, 'checkProfile']);
        Route::get('/notifications', [NotificationController::class, 'index']);  // fetching Notifications
        Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAllAsRead']);  // Marking all notifications as read
        Route::post('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead']);  // Marking a notification as read

        // Only admins can access these routes
        Route::middleware(['role:admin'])->group(function (): void {
            Route::post('/admin/seller-approve', [UserController::class, 'approveSeller']);  // Approving a seller request
            Route::post('/admin/seller-reject', [UserController::class, 'rejectSeller']);    // Rejecting a seller request
            Route::get('/admin/seller-requests', [UserController::class, 'sellerRequest']);  // Getting all seller requests
        });
    });

    Route::get('/search/product', [UserProductController::class, 'search']); // Searching a product
    Route::get('/search', [UserSearchController::class, 'search']); // Searching by seller/tag
    Route::get('user/{userId}/tags/most-used', [TagController::class, 'getMostUsedTags']);
    Route::get('user/{userId}/tags/used', [TagController::class, 'getAllUsedTags']);
    Route::get('sliders', [AdminSliderController::class, 'getAllSliders']);

    Route::get('/vendorlist ', [UserVendorController::class, 'vendorlist']);            // Show all sellers in list form
    Route::get('/vendorlist/{vendor} ', [UserVendorController::class, 'vendorData']);   // Show a particular seller data
    Route::get('/similarvendors/{vendor}', [UserVendorController::class, 'similarSellers']);
    // Show products based on price range
    Route::get('/vendorlist/{vendor}/product/in-price-range ', [UserProductController::class, 'getProductsByPriceRange']);
    // Show products on price category like low, medium, high
    Route::get('/vendorlist/{vendor}/product/in-price-category', [UserProductController::class, 'getProductsInPriceCategory']);
    // Show all product of a particular seller
    Route::get('/vendorlist/{vendor}/allproduct ', [UserProductController::class, 'getAllProducts']);
    // Show short videos of a particular seller
    Route::get('/vendorlist/{vendor}/shortvideo ', [UserVendorController::class, 'getShortVideos']);

    Route::middleware(['auth:sanctum'])->group(function (): void {
        // Giving a review to a seller
        Route::post('/vendor/{vendor_id}/review', [UserVendorReviewController::class, 'store']);
        // Removing review from a seller
        Route::delete('/vendor/review/{review_id}/delete', [UserVendorReviewController::class, 'delete']);
    });
    // See all reviews of a seller
    Route::get('/vendor/{vendor_id}/reviews', [UserVendorReviewController::class, 'index']);
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/vendor/{vendor_id}/follow', [UserVendorFollowController::class, 'follow']);        // Follow a Seller
        Route::get('/vendor/{vendor_id}/unfollow ', [UserVendorFollowController::class, 'unfollow']);   // Unfollow a seller
        Route::get('/following ', [UserVendorFollowController::class, 'following']);   // Get all following sellers
        Route::get('/followers ', [UserVendorFollowController::class, 'followers']);
    });

    Route::get('/get-categories', [CategoryController::class, 'getCategories'])->name('get.categories');
    Route::get('/get-categories-by-order', [CategoryController::class, 'getOrderBasisCategories'])->name('get.categories.by.order');
    Route::get('/categories', [CategoryController::class, 'getCategoriesOnly']);

    Route::get('/get-random-tags', [TagController::class, 'getTagsRandom'])->name('get.random.tags');
    Route::get('/get-tag-info/{id}', [TagController::class, 'getTagInfo'])->name('get.tag.info');
    Route::get('/get-product-by-tag/{id}', [TagController::class, 'getProductByTag'])->name('get.product.by.tag');
    Route::get('/get-own-product-by-tag/{id}', [TagController::class, 'getOwnProductByTag'])->name('get.own.product.by.tag');
    Route::get('/product/{product}', [UserProductController::class, 'show']);  // show a single product by id

    Route::get('/product/{id}/similar', [UserProductController::class, 'getSimilarProducts']);
    Route::get('/seller/{id}/products', [UserProductController::class, 'getProductsByType']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/size-template/create', [SizeTemplateController::class, 'store']);              // Create a size template
        Route::post('/size-templates/{templateId}/sizes', [SizeTemplateController::class, 'addSizeToTemplate']); // Add a size to a template
        Route::get('/size-templates', [SizeTemplateController::class, 'getTemplates']);         // Get all size templates for the authenticated seller
        Route::delete('/size-templates/{id}', [SizeTemplateController::class, 'destroy']);      // Delete a size template
        Route::put('/size-template/{templateId}/size-item/{sizeItemId}', [SizeTemplateController::class, 'updateSize'])->name('size-template.update-size');
        Route::delete('/size-template/{templateId}/size-item/{sizeItemId}', [SizeTemplateController::class, 'destroySizeItem']);

        Route::middleware(['role:vendor'])->group(function (): void {
            Route::post('/product/create', [VendorProductController::class, 'store']);              // store product
            Route::get('/my-products', [VendorProductController::class, 'getAllMyProducts']);
            Route::get('/my-product/{id}', [VendorProductController::class, 'getSingleProduct']);  // show a single product by id
            Route::post('/products/{id}', [VendorProductController::class, 'update']);              // Update Product
            Route::post('/products/soft-delete/{id}', [VendorProductController::class, 'destroy']); // Soft Deleting a product
            Route::delete('/products/{id}/images/{img}', [VendorProductController::class, 'deleteImage']); // Deleting a product's image
            Route::post('/products/inactive/{id}', [VendorProductController::class, 'inactive']);   // Inactivating a product
            Route::post('/products/active/{id}', [VendorProductController::class, 'active']);
            Route::get('/search/my-product', [VendorProductController::class, 'search']); // Searching a product

            Route::prefix('short-videos')->group(function (): void {
                Route::get('/', [VendorShortVideoController::class, 'index_api']);
                Route::post('/', [VendorShortVideoController::class, 'store_api']);
                Route::post('/{id}', [VendorShortVideoController::class, 'update_api']);
                Route::delete('/{id}', [VendorShortVideoController::class, 'destroy_api']);
            });

            Route::post('/withdraw', [TransactionController::class, 'withdraw']);

        });

        Route::post('/shop-categories', [ShopCategoryController::class, 'store']);         // Create new category
        Route::put('/shop-categories/{id}', [ShopCategoryController::class, 'update']);    // Update category
        Route::delete('/shop-categories/{id}', [ShopCategoryController::class, 'destroy']); // Delete category
    });

    Route::middleware('auth:sanctum')->get('/user/balance-stats', [UserController::class, 'getBalanceStats']);

    Route::get('short-videos/{id}', [VendorShortVideoController::class, 'show_api']);

    Route::get('/get-tags', [TagController::class, 'getTags'])->name('get.tags');

    Route::delete('broadcasting/auth', fn (Illuminate\Http\Request $request) => \Illuminate\Support\Facades\Broadcast::driver('fcm')->unauth($request));

    Route::get('/shop-categories', [ShopCategoryController::class, 'index']);          // List all categories
    Route::get('/shop-categories/{id}', [ShopCategoryController::class, 'show']);      // View single category

    // Cart Routes (Auth Required)
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/cart', [CartController::class, 'addOrUpdate']);
        Route::get('/cart', [CartController::class, 'index']);
        Route::delete('/cart/{item}', [CartController::class, 'destroy']);
        Route::get('/cart/summary', [CartController::class, 'summary']);

        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);

        Route::get('/my-orders', [OrderController::class, 'MyOrders']);
        Route::get('/my-orders/search', [OrderController::class, 'searchOrderById']);
        Route::get('/my-order/{id}', [OrderController::class, 'myOrderDetail']);
        Route::get('/my-store-orders', [OrderController::class, 'MyStoreOrders']);

        Route::get('/seller/orders', [OrderController::class, 'sellerOrders']);
        Route::get('/seller/orders/{order}', [OrderController::class, 'sellerOrderDetail']);
        Route::patch('/seller/orders/{order}/accept', [OrderController::class, 'acceptSellerOrder']);
        Route::patch('/seller/orders/{order}/reject', [OrderController::class, 'rejectSellerOrder']);

        // Addresses
        Route::post('/addresses', [AddressController::class, 'store']);
        Route::get('/addresses', [AddressController::class, 'index']);
        Route::put('/addresses/{address}', [AddressController::class, 'update']);
        Route::post('/addresses/{id}/set-default', [AddressController::class, 'setDefault']);
        Route::get('/addresses/default', [AddressController::class, 'getDefault']);

        // Delivery options for users
    });

    Route::get('/delivery/models', [DeliveryModelController::class, 'userIndex']);
    Route::get('/sections', [SectionController::class, 'sections']);
    Route::get('/search-section', [SectionController::class, 'searchSection']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('livestreams', [LivestreamController::class, 'store'])->name('livestreams.store');
        Route::match(['put', 'patch'], 'livestreams/{livestream}', [LivestreamController::class, 'update'])->name('livestreams.update');
    });

    Route::get('livestreams', [LivestreamController::class, 'index'])->name('livestreams.index');
    Route::get('livestreams/{ls}', [LivestreamController::class, 'show'])->name('livestreams.show');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('livestreams/{livestream}/publisher-token', GetLivestreamPublisherTokenController::class)
            ->name('livestreams.get-publisher-token');
        Route::post('livestreams/{ls}/products', [LivestreamProductController::class, 'store'])
            ->name('livestream-products.store');
        Route::delete('livestreams/{ls}/products', [LivestreamProductController::class, 'destroy'])
            ->name('livestream-products.destroy');

        Route::prefix('livestreams/{id}')->group(function (): void {
            Route::post('like', [LivestreamController::class, 'like']);
            Route::post('save', [LivestreamController::class, 'save']);
        });

        Route::prefix('lives')->group(function (): void {
            Route::get('liked', [LivestreamController::class, 'getLikedLivestreams']);
            Route::get('saved', [LivestreamController::class, 'getSavedLivestreams']);
            Route::get('{livestream}/likes-count', [LivestreamController::class, 'getLikesCount'])->name('livestreams.likes-count');
        });

        Route::prefix('livestreams/{livestreamId}/comments')->group(function (): void {
            Route::get('/', [LivestreamCommentController::class, 'index']);
            Route::post('/', [LivestreamCommentController::class, 'store']);
            Route::put('{commentId}', [LivestreamCommentController::class, 'update']);
            Route::delete('{commentId}', [LivestreamCommentController::class, 'destroy']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/shorts/{id}/comment', [ShortsInteractionController::class, 'comment']);
        Route::delete('/shorts/comment/{id}', [ShortsInteractionController::class, 'deleteComment']);
        Route::post('/shorts/{id}/like', [ShortsInteractionController::class, 'toggleLike']);
        Route::post('/shorts/{id}/save', [ShortsInteractionController::class, 'toggleSave']);
        Route::get('/shorts/saved', [ShortsInteractionController::class, 'getSavedShorts']);
    });

    Route::get('/shorts/{id}/products', [ShortsInteractionController::class, 'getShortProducts']);
    Route::get('/shorts', [ShortsInteractionController::class, 'allshorts']); 
    Route::get('/shorts/{id}/comments', [ShortsInteractionController::class, 'getComments']);

    Route::get('livestream/{livestream}/products', [LivestreamController::class, 'addedProducts']);

    Route::get('livestreams/{livestream}/subscriber-token', GetLivestreamSubscriberTokenController::class)->name('livestreams.get-subscriber-token');
});
