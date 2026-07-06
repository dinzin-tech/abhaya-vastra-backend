<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\PagesController;
use App\Http\Controllers\Api\CustomizedProductController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RazorpayController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReturnController;
use App\Http\Controllers\Api\ExchangeController;




/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/contact-details', [PagesController::class, 'contactDetails']);
Route::get('/short-video', [PagesController::class, 'video']);
Route::get('/review', [PagesController::class, 'review']);
Route::get('/gallery', [PagesController::class, 'gallery']);



// Customized Products (public)
Route::get('/customized-products', [CustomizedProductController::class, 'index']);
Route::get('/customized-products/all', [CustomizedProductController::class, 'getAllCustomizedProducts']);
Route::get('/customized-products/{id}', [CustomizedProductController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/user/profile', [ProfileController::class, 'profile']);
    Route::post('/user/profile', [ProfileController::class, 'updateProfile']);
    
    // Submit review for a product after delivery
    Route::post('/products/{id}/reviews', [ProductController::class, 'storeReview']);
    Route::get('/products/{id}/review-eligibility', [ProductController::class, 'checkReviewEligibility']);
});




Route::get('/best-sellers', [ProductController::class, 'bestSellers']);
Route::get('/featured-products', [ProductController::class, 'featuredProducts']);
Route::get('/new-arrivals', [ProductController::class, 'newArrivals']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/search', [ProductController::class, 'search']);

// Wishlist routes (supports both authenticated and guest users)
Route::get('/wishlist', [WishlistController::class, 'index']);
Route::post('/wishlist', [WishlistController::class, 'store']);
Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy']);
Route::delete('/wishlist-clear', [WishlistController::class, 'clear']);
Route::post('/wishlist-merge', [WishlistController::class, 'mergeWishlist'])->middleware('auth:sanctum');

// Cart routes (supports both authenticated and guest users)
Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart', [CartController::class, 'store']);
Route::put('/cart/{id}', [CartController::class, 'update']);
Route::delete('/cart/{id}', [CartController::class, 'destroy']);
Route::delete('/cart-clear', [CartController::class, 'clear']);
Route::post('/cart-merge', [CartController::class, 'mergeCart'])->middleware('auth:sanctum');

// Order routes
Route::post('/orders', [OrderController::class, 'store']); // Place order (guest or authenticated)
Route::get('/orders', [OrderController::class, 'index'])->middleware('auth:sanctum'); // Get user orders
Route::get('/orders/{id}', [OrderController::class, 'show'])->middleware('auth:sanctum'); // Get specific order
Route::delete('/orders/{id}', [OrderController::class, 'destroy'])->middleware('auth:sanctum'); // Delete order (payment cancelled)

Route::get('/returns', [ReturnController::class, 'index'])->middleware('auth:sanctum'); // Get user returns
Route::post('/returns', [ReturnController::class, 'store'])->middleware('auth:sanctum'); // Create return request
Route::get('/returns/{id}', [ReturnController::class, 'show'])->middleware('auth:sanctum'); // Get specific return request

Route::get('/exchanges', [ExchangeController::class, 'index'])->middleware('auth:sanctum'); // Get user exchanges
Route::post('/exchanges', [ExchangeController::class, 'store'])->middleware('auth:sanctum'); // Create exchange request
Route::get('/exchanges/{id}', [ExchangeController::class, 'show'])->middleware('auth:sanctum'); // Get specific exchange request
Route::post('/exchanges/{id}/create-payment-order', [ExchangeController::class, 'createPaymentOrder'])->middleware('auth:sanctum'); // Create Razorpay order for exchange
Route::post('/exchanges/{id}/verify-payment', [ExchangeController::class, 'verifyPayment'])->middleware('auth:sanctum'); // Verify Razorpay payment


// Payment routes (Razorpay)
Route::post('/razorpay/create-order', [RazorpayController::class, 'createOrder'])->middleware('auth:sanctum');
Route::post('/razorpay/verify-payment', [RazorpayController::class, 'verifyPayment']);
Route::post('/razorpay/payment-failed', [RazorpayController::class, 'paymentFailed']);


Route::get('/about-us', [PagesController::class, 'aboutUs']);
Route::get('/categories', [PagesController::class, 'categories']);
Route::get('/banner', [PagesController::class, 'banner']);
Route::get('/faq', [PagesController::class, 'faq']);
Route::get('/privacy', [PagesController::class, 'privacy']);
Route::get('/terms', [PagesController::class, 'terms']);
Route::get('/social-links', [PagesController::class, 'socialLinks']);

Route::get('/products-by-category', [ProductController::class, 'productsByCategory']);

Route::post('/contact', [ContactController::class, 'sendMessage']);



Route::middleware('auth:sanctum')->post('/change-password', [PasswordController::class, 'changePassword']);



Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [PasswordResetController::class, 'reset']);



Route::group(['prefix' => 'coupon'], function () {
    Route::post('check', [CouponController::class, 'check']);
    Route::post('update-usage', [CouponController::class, 'updateUsage']);
});

// Wallet & Loyalty Points routes
Route::group(['prefix' => 'wallet', 'middleware' => 'auth:sanctum'], function () {
    Route::get('balance', [App\Http\Controllers\Api\WalletController::class, 'getBalance']);
    Route::get('transactions', [App\Http\Controllers\Api\WalletController::class, 'getTransactions']);
    Route::post('apply-points', [App\Http\Controllers\Api\WalletController::class, 'applyPoints']);
    Route::post('redeem-points', [App\Http\Controllers\Api\WalletController::class, 'redeemPoints']);
    
    // Loyalty Points Top-up (Buy Points with Razorpay)
    Route::get('topup-packages', [App\Http\Controllers\Api\WalletController::class, 'getTopUpPackages']);
    Route::post('create-topup-order', [App\Http\Controllers\Api\WalletController::class, 'createTopUpOrder']);
    Route::post('verify-topup-payment', [App\Http\Controllers\Api\WalletController::class, 'verifyTopUpPayment']);
    
    // Wallet Money Top-up (Add Money to Wallet)
    Route::post('create-money-topup-order', [App\Http\Controllers\Api\WalletController::class, 'createMoneyTopUpOrder']);
    Route::post('verify-money-topup-payment', [App\Http\Controllers\Api\WalletController::class, 'verifyMoneyTopUpPayment']);
});

// Reward settings (public - for frontend display)
Route::get('reward-settings', [App\Http\Controllers\Api\WalletController::class, 'getSettings']);