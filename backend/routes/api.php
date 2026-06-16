<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OverdueController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SellerProductController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);
Route::get('stores/{store}', [StoreController::class, 'publicShow']);
Route::get('reviews', [ReviewController::class, 'index']);
Route::post('reviews', [ReviewController::class, 'store']);

// Auth
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('switch-role', [AuthController::class, 'switchRole']);
    });
});

// Protected
Route::middleware('auth:api')->group(function () {
    Route::put('reviews/{review}', [ReviewController::class, 'update']);

    // Discount validation (buyer use)
    Route::post('vouchers/validate', [VoucherController::class, 'validate']);
    Route::post('promos/validate', [PromoController::class, 'validate']);

    // Admin - monitoring
    Route::get('admin/dashboard', [AdminController::class, 'dashboard']);
    Route::get('admin/users', [AdminController::class, 'users']);
    Route::get('admin/stores', [AdminController::class, 'stores']);
    Route::get('admin/products', [AdminController::class, 'products']);
    Route::get('admin/orders', [AdminController::class, 'orders']);
    Route::get('admin/deliveries', [AdminController::class, 'deliveries']);
    Route::get('admin/overdue', [AdminController::class, 'overdueOrders']);

    // Admin - overdue handling
    Route::post('admin/overdue/process', [OverdueController::class, 'processOverdue']);
    Route::post('admin/simulate-next-day', [OverdueController::class, 'simulateNextDay']);

    // Admin - vouchers
    Route::get('admin/vouchers', [VoucherController::class, 'index']);
    Route::get('admin/vouchers/{voucher}', [VoucherController::class, 'show']);
    Route::post('admin/vouchers', [VoucherController::class, 'store']);
    Route::delete('admin/vouchers/{voucher}', [VoucherController::class, 'destroy']);

    // Admin - promos
    Route::get('admin/promos', [PromoController::class, 'index']);
    Route::get('admin/promos/{promo}', [PromoController::class, 'show']);
    Route::post('admin/promos', [PromoController::class, 'store']);
    Route::delete('admin/promos/{promo}', [PromoController::class, 'destroy']);

    // Seller - store
    Route::get('seller/store', [StoreController::class, 'show']);
    Route::post('seller/store', [StoreController::class, 'store']);
    Route::put('seller/store', [StoreController::class, 'update']);

    // Seller - products
    Route::get('seller/products', [SellerProductController::class, 'index']);
    Route::post('seller/products', [SellerProductController::class, 'store']);
    Route::put('seller/products/{product}', [SellerProductController::class, 'update']);
    Route::delete('seller/products/{product}', [SellerProductController::class, 'destroy']);

    // Seller - orders
    Route::get('seller/orders', [OrderController::class, 'sellerOrders']);
    Route::patch('seller/orders/{order}/process', [OrderController::class, 'processOrder']);
    Route::get('seller/report', [OrderController::class, 'sellerReport']);

    // Buyer - wallet
    Route::get('buyer/wallet', [WalletController::class, 'show']);
    Route::post('buyer/wallet/topup', [WalletController::class, 'topUp']);
    Route::get('buyer/wallet/transactions', [WalletController::class, 'transactions']);

    // Buyer - addresses
    Route::get('buyer/addresses', [AddressController::class, 'index']);
    Route::post('buyer/addresses', [AddressController::class, 'store']);
    Route::put('buyer/addresses/{address}', [AddressController::class, 'update']);
    Route::delete('buyer/addresses/{address}', [AddressController::class, 'destroy']);
    Route::patch('buyer/addresses/{address}/default', [AddressController::class, 'setDefault']);

    // Buyer - cart
    Route::get('buyer/cart', [CartController::class, 'show']);
    Route::post('buyer/cart/items', [CartController::class, 'addItem']);
    Route::put('buyer/cart/items/{item}', [CartController::class, 'updateItem']);
    Route::delete('buyer/cart/items/{item}', [CartController::class, 'removeItem']);
    Route::delete('buyer/cart', [CartController::class, 'clear']);

    // Buyer - orders
    Route::post('buyer/checkout/preview', [OrderController::class, 'previewCheckout']);
    Route::post('buyer/checkout', [OrderController::class, 'checkout']);
    Route::get('buyer/orders', [OrderController::class, 'index']);
    Route::get('buyer/orders/{order}', [OrderController::class, 'show']);
    Route::get('buyer/orders/{order}/tracking', [DeliveryController::class, 'trackOrder']);
    Route::get('buyer/report', [OrderController::class, 'buyerReport']);

    // Driver - jobs
    Route::get('driver/jobs', [DeliveryController::class, 'availableJobs']);
    Route::get('driver/jobs/{delivery}', [DeliveryController::class, 'showJob']);
    Route::patch('driver/jobs/{delivery}/take', [DeliveryController::class, 'takeJob']);
    Route::patch('driver/jobs/{delivery}/complete', [DeliveryController::class, 'completeJob']);
    Route::get('driver/my-jobs', [DeliveryController::class, 'myJobs']);
    Route::get('driver/active-job', [DeliveryController::class, 'myActiveJob']);

    // Seller - tracking
    Route::get('seller/orders/{order}/tracking', [DeliveryController::class, 'trackOrder']);
});
