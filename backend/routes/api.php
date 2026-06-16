<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SellerProductController;
use App\Http\Controllers\StoreController;
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
});
