<?php

use App\Events\PodcastProcessed;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminSliderController;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Vendor\VendorProductController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\Vendor\ProductController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Vendor\VendorDashboardController;
use App\Http\Controllers\Vendor\VendorOrderController;
use App\Http\Controllers\Vendor\VendorProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\DeliveryModelController;
use App\Http\Controllers\Vendor\VendorShortVideoController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
use App\Notifications\NewMessageNotification;

Broadcast::routes(['middleware' => ['auth']]);

Route::get('/test-notify', function () {
    Log::info('Route /test-notify hit');
    broadcast(new PodcastProcessed());

    return 'Notification sent!';
});

Route::get('/auth/{provider}', [SocialLoginController::class, 'redirectToProvider']);
Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback']);


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('role:user')->group(function () {
    Route::get('/user/dashboard', function () {
        return 'User Dashboard';
    });
});

Route::middleware('role:vendor')->group(function () {});

Route::prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/dashboard', function () {
        return view('vendor.index');
    })->name('dashboard');
    Route::resource('products', VendorProductController::class);
    Route::get('product-image-delete/{id}', [VendorProductController::class, 'ImageDelete'])->name('product-image-delete');
    Route::get('product-stock-delete/{id}', [VendorProductController::class, 'StockDelete'])->name('product-stock-delete');
    Route::get('/get-tags', [TagController::class, 'getTags'])->name('get.tags');
    // Route::get('/short-videos', [VendorShortVideoController::class, 'Videos'])->name('media.videos');
    Route::get('/videos', [VendorShortVideoController::class, 'Videos'])->name('videos');
    Route::post('/video/store', [VendorShortVideoController::class, 'store'])->name('video.store');
    Route::put('/video/update/{id}', [VendorShortVideoController::class, 'update'])->name('video.update');
    Route::delete('/video/delete/{id}', [VendorShortVideoController::class, 'destroy'])->name('video.delete');
});

Route::prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/dashboard', [VendorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [VendorProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [VendorProfileController::class, 'update'])->name('profile.update');
    Route::controller(VendorOrderController::class)->group(function () {
        Route::prefix('order')->name('order.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/pending', 'PendingOrder')->name('pending');
            Route::get('/details/{id}', 'orderDetails')->name('details');
            Route::get('/confirmed', 'ConfirmedOrder')->name('confirmed');
            Route::get('/processing', 'ProcessingOrder')->name('processing');
            Route::get('/ready', 'ReadyOrder')->name('ready');
            Route::get('/shipped', 'shippedOrder')->name('shipped');
            Route::get('/delivered', 'DeliveredOrder')->name('delivered');
            Route::get('/completed', 'completedOrder')->name('completed');
            Route::get('/cancled', 'CancledOrder')->name('cancled');
            Route::get('/pending/confirm/{order_id}', 'PendingToConfirm')->name('pending-confirm');
            Route::get('/confirm/processing/{order_id}', 'ConfirmToProcess')->name('confirm-processing');
            Route::get('/processing/ready-ship/{order_id}', 'ProcessToReadyToShip')->name('processing-ready-ship');
            Route::get('/pending/cancel/{order_id}', 'PendingToCancel')->name('pending-cancel');
            Route::get('/processing/delivered/{order_id}', 'ProcessToDelivered')->name('processing-delivered');
            Route::get('/invoice/download/{order_id}', 'AdminInvoiceDownload')->name('invoice.download');
        });
    });
    Route::get('/payment/request', [PaymentController::class, 'request'])->name('payment.request');
    Route::post('/money/withdraw', [PaymentController::class, 'storeWithdrawRequest'])->name('money.withdraw');
    Route::get('/payment/history', [PaymentController::class, 'paymentHistory'])->name('payment.history');
});


Route::middleware(['auth', 'role:admin,vendor'])->group(function () {});
Route::get('/create-vendor-account', [ProfileController::class, 'createVendor'])->name('create.vendor.account');
Route::post('vendor/application', [UserController::class, 'application'])->name('vendor.application');
Route::get('create-rider-account', [ProfileController::class, 'createRider'])->name('create.rider.account');
Route::post('rider/application', [UserController::class, 'riderApplication'])->name('rider.application');

Route::get('/admin/categories/children/{parentId}', [AdminCategoryController::class, 'getChildren']);


Route::middleware('role:admin')->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('categories', AdminCategoryController::class);
        Route::resource('products', AdminProductController::class);
        Route::controller(AdminOrderController::class)->group(function () {
            Route::prefix('order')->name('order.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/pending', 'PendingOrder')->name('pending');
                Route::get('/details/{id}', 'orderDetails')->name('details');
                Route::get('/confirmed', 'ConfirmedOrder')->name('confirmed');
                Route::get('/processing', 'ProcessingOrder')->name('processing');
                Route::get('/ready', 'ReadyOrder')->name('ready');
                Route::get('/shipped', 'shippedOrder')->name('shipped');
                Route::get('/delivered', 'DeliveredOrder')->name('delivered');
                Route::get('/completed', 'completedOrder')->name('completed');
                Route::get('/cancled', 'CancledOrder')->name('cancled');
                Route::get('/ready/shipped/{order_id}', 'ReadyToShipped')->name('ready-shipped');
                Route::get('/shipped/delivered/{order_id}', 'shippedToDelivered')->name('shipped-delivered');
                Route::get('/delivered/completed/{order_id}', 'DeliveredToCompleted')->name('delivered-completed');
                Route::get('/invoice/download/{order_id}', 'AdminInvoiceDownload')->name('invoice.download');
            });
        });

        Route::resource('sliders', AdminSliderController::class);
        Route::get('get-tags', [TagController::class, 'getTags'])->name('getTags');


        Route::get('user/list', [UserController::class, 'userList'])->name('user.list');
        Route::post('user/store', [UserController::class, 'store'])->name('user.store');
        Route::get('user/{id}/edit', [UserController::class, 'edit'])->name('user.edit');
        Route::put('user/{id}', [UserController::class, 'update'])->name('user.update');
        Route::delete('user/{id}', [UserController::class, 'destroy'])->name('user.destroy');

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [AdminSettingController::class, 'Index'])->name('index');
            Route::post('/', [AdminSettingController::class, 'Update'])->name('update');
        });
        Route::get('/payment/requests', [PaymentController::class, 'PaymentRequests'])->name('payment.requests');
        Route::get('/payment/history', [PaymentController::class, 'AdminPaymentHistory'])->name('payment.history');
        Route::put('/payment/update/{bill}', [PaymentController::class, 'update'])->name('payment.update');

        Route::get('payment-methods', [PaymentMethodController::class, 'index'])->name('payment-methods.index');
        Route::get('payment-methods/create', [PaymentMethodController::class, 'create'])->name('payment-methods.create');
        Route::post('payment-methods', [PaymentMethodController::class, 'store'])->name('payment-methods.store');
        Route::get('payment-methods/{id}/edit', [PaymentMethodController::class, 'edit'])->name('payment-methods.edit');
        Route::put('payment-methods/{id}', [PaymentMethodController::class, 'update'])->name('payment-methods.update');
        Route::delete('payment-methods/{id}', [PaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');
    });
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/delivery/models', [DeliveryModelController::class, 'index'])->name('admin.delivery.models.index');
    Route::get('/delivery/models/create', [DeliveryModelController::class, 'create'])->name('admin.delivery.models.create');
    Route::post('/delivery/models', [DeliveryModelController::class, 'store'])->name('admin.delivery.models.store');
    Route::get('/delivery/models/{model}/edit', [DeliveryModelController::class, 'edit'])->name('admin.delivery.models.edit');
    Route::put('/delivery/models/{model}', [DeliveryModelController::class, 'update'])->name('admin.delivery.models.update');
    Route::delete('/delivery/models/{model}', [DeliveryModelController::class, 'destroy'])->name('admin.delivery.models.destroy');
});



Route::get('/test', function () {
    dd(Storage::disk('r2')->files());
})->name('payment.requests');

require __DIR__ . '/auth.php';
