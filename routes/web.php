<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\PasswordController;


use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\PointsConfigController;
use App\Http\Controllers\Admin\RewardController;

// Admin login routes

Auth::routes();

Route::get('/', [LoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('admin/login', [LoginController::class, 'login'])->name('admin.login.submit');
Route::post('admin/logout', [LoginController::class, 'logout'])->name('admin.logout');

// Protected admin routes
Route::prefix('admin')->middleware('auth:admin')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('admin.profile');
    Route::get('/update-profile', [ProfileController::class, 'updateForm'])->name('admin.profile.update.form');
    Route::post('/profile-update', [ProfileController::class, 'update'])->name('admin.profile.update');
    Route::post('/profile-upload', [ProfileController::class, 'uploadSingleImage'])->name('admin.profile.upload');

    // Change password
    Route::get('/change-password', [PasswordController::class, 'changePassword'])->name('admin.password.change');
    Route::post('/change-password', [PasswordController::class, 'changePasswordSubmit'])->name('admin.password.change.submit');


    Route::resource('about-us', 'App\Http\Controllers\Admin\AboutUsController');
    Route::get('about-us-list', 'App\Http\Controllers\Admin\AboutUsController@listAboutUs')->name('aboutus.list');
    Route::delete('/delete-about-us', 'App\Http\Controllers\Admin\AboutUsController@delete')->name('aboutus.delete');

    Route::resource('terms', 'App\Http\Controllers\Admin\TermsController');
    Route::get('terms-list', 'App\Http\Controllers\Admin\TermsController@listTerms')->name('terms.list');
    Route::delete('/delete-terms', 'App\Http\Controllers\Admin\TermsController@delete')->name('terms.delete');

    Route::resource('privacy', 'App\Http\Controllers\Admin\PrivacyController');
    Route::get('privacy-list', 'App\Http\Controllers\Admin\PrivacyController@listPrivacy')->name('privacy.list');
    Route::delete('/delete-privacy', 'App\Http\Controllers\Admin\PrivacyController@delete')->name('privacy.delete');

    Route::resource('faq', 'App\Http\Controllers\Admin\FaqController');
    Route::get('faq-list', 'App\Http\Controllers\Admin\FaqController@listFaqs')->name('faq.list');
    Route::delete('/delete-faq', 'App\Http\Controllers\Admin\FaqController@delete')->name('faq.delete');

    Route::resource('categories', 'App\Http\Controllers\Admin\CategoryController');
    Route::get('categories-list', 'App\Http\Controllers\Admin\CategoryController@listCategories')->name('categories.list');
    Route::delete('/delete-category', 'App\Http\Controllers\Admin\CategoryController@delete')->name('categories.delete');

    // Bulk Product Import Routes (MUST be before resource to avoid {product} parameter capture)
    Route::get('products/bulk-import', 'App\Http\Controllers\Admin\BulkProductImportController@index')->name('products.bulk-import');
    Route::post('products/bulk-import', 'App\Http\Controllers\Admin\BulkProductImportController@import')->name('products.bulk-import.store');
    Route::get('products/bulk-import/template', 'App\Http\Controllers\Admin\BulkProductImportController@downloadTemplate')->name('products.bulk-import.template');

    Route::resource('products', 'App\Http\Controllers\Admin\ProductController');
    Route::get('products-list', 'App\Http\Controllers\Admin\ProductController@listProducts')->name('products.list');
    Route::delete('/delete-product', 'App\Http\Controllers\Admin\ProductController@delete')->name('products.delete');

    // Product Variants Routes
    Route::resource('product-variants', 'App\Http\Controllers\Admin\ProductVariantController');
    Route::get('product-variants-list', 'App\Http\Controllers\Admin\ProductVariantController@listVariants')->name('product-variants.list');
    Route::delete('/delete-product-variant', 'App\Http\Controllers\Admin\ProductVariantController@delete')->name('product-variants.delete');
    Route::post('product-variants/get-colors', 'App\Http\Controllers\Admin\ProductVariantController@getProductColorsByProduct')->name('product-variants.get-colors');

    Route::resource('contact-us', 'App\Http\Controllers\Admin\ContactUsController');
    Route::get('contact-us-list', 'App\Http\Controllers\Admin\ContactUsController@listContact')->name('contact-us.list');
    Route::delete('/delete-contact-us', 'App\Http\Controllers\Admin\ContactUsController@delete')->name('contact-us.delete');


    Route::resource('customized', 'App\Http\Controllers\Admin\CustomizedController');
    Route::get('customized-list', 'App\Http\Controllers\Admin\CustomizedController@listCustomized')->name('customized.list');

    // GCS Storage Settings
    Route::get('/storage-settings', 'App\Http\Controllers\Admin\StorageController@index')->name('admin.storage.index');
    Route::post('/storage-settings', 'App\Http\Controllers\Admin\StorageController@update')->name('admin.storage.update');

    // Qikink Integration Settings
    Route::get('/qikink-settings', 'App\Http\Controllers\Admin\QikinkSettingsController@index')->name('admin.qikink.settings');
    Route::post('/qikink-settings', 'App\Http\Controllers\Admin\QikinkSettingsController@update')->name('admin.qikink.settings.update');

    // Design Library (Catalog & Categories)
    Route::get('/print-designs', 'App\Http\Controllers\Admin\PrintDesignController@index')->name('admin.print-designs.index');
    Route::post('/print-designs/category', 'App\Http\Controllers\Admin\PrintDesignController@storeCategory')->name('admin.print-designs.store-category');
    Route::delete('/print-designs/category/{id}', 'App\Http\Controllers\Admin\PrintDesignController@destroyCategory')->name('admin.print-designs.destroy-category');
    Route::post('/print-designs/upload', 'App\Http\Controllers\Admin\PrintDesignController@storeDesign')->name('admin.print-designs.store-design');
    Route::delete('/print-designs/{id}', 'App\Http\Controllers\Admin\PrintDesignController@destroyDesign')->name('admin.print-designs.destroy-design');
    Route::delete('/delete-customized', 'App\Http\Controllers\Admin\CustomizedController@delete')->name('customized.delete');

    // Banner Routes
    Route::resource('banner', 'App\Http\Controllers\Admin\BannerController');
    Route::get('banner-list', 'App\Http\Controllers\Admin\BannerController@listBanners')->name('banner.list');
    Route::delete('/delete-banner', 'App\Http\Controllers\Admin\BannerController@delete')->name('banner.delete');

    Route::resource('gallery', 'App\Http\Controllers\Admin\GalleryController');
    Route::get('gallery-list', 'App\Http\Controllers\Admin\GalleryController@listGallery')->name('gallery.list');
    Route::delete('/delete-gallery', 'App\Http\Controllers\Admin\GalleryController@delete')->name('gallery.delete');

    Route::resource('video', 'App\Http\Controllers\Admin\VideoController');
    Route::get('video-list', 'App\Http\Controllers\Admin\VideoController@listVideo')->name('video.list');
    Route::delete('/delete-video', 'App\Http\Controllers\Admin\VideoController@delete')->name('video.delete');


    Route::resource('reviews', 'App\Http\Controllers\Admin\ReviewController');
    Route::get('reviews-list', 'App\Http\Controllers\Admin\ReviewController@listReview')->name('reviews.list');
    Route::delete('/delete-reviews', 'App\Http\Controllers\Admin\ReviewController@delete')->name('reviews.delete');


    // SMTP Settings Routes
    Route::resource('smtp', 'App\Http\Controllers\Admin\SmtpSettingController');
    Route::get('smtp-list', 'App\Http\Controllers\Admin\SmtpSettingController@listSmtp')->name('smtp.list');
    Route::delete('/delete-smtp', 'App\Http\Controllers\Admin\SmtpSettingController@delete')->name('smtp.delete');
    Route::get('/smtp-settings-test/{id}', 'App\Http\Controllers\Admin\SmtpSettingController@test')->name('smtp-settings.test');

    // Social Links Routes
    Route::resource('social', 'App\Http\Controllers\Admin\SocialLinkController');
    Route::get('social-list', 'App\Http\Controllers\Admin\SocialLinkController@listSocial')->name('social.list');
    Route::delete('/delete-social', 'App\Http\Controllers\Admin\SocialLinkController@delete')->name('social.delete');



    Route::resource('payment-gateway', 'App\Http\Controllers\Admin\PaymentGatewayController');
    Route::get('payment-gateway-list', 'App\Http\Controllers\Admin\PaymentGatewayController@listGateways')->name('payment-gateway.list');
    Route::delete('/delete-payment-gateway', 'App\Http\Controllers\Admin\PaymentGatewayController@delete')->name('payment-gateway.delete');

    // Orders Routes
    Route::resource('orders', 'App\Http\Controllers\Admin\OrderController');
    Route::get('orders-list', 'App\Http\Controllers\Admin\OrderController@listOrders')->name('orders.list');
    Route::get('orders-details/{id}', 'App\Http\Controllers\Admin\OrderController@getOrderDetails')->name('orders.details');
    Route::post('/orders-update-status', 'App\Http\Controllers\Admin\OrderController@updateStatus')->name('orders.update-status');
    Route::delete('/delete-order', 'App\Http\Controllers\Admin\OrderController@delete')->name('orders.delete');

    // Shiprocket Management inside Admin Panel
    Route::post('/shiprocket/create-shipment', 'App\Http\Controllers\Admin\OrderController@shiprocketCreateShipment')->name('admin.shiprocket.create-shipment');
    Route::get('/shiprocket/get-couriers/{order_id}', 'App\Http\Controllers\Admin\OrderController@shiprocketGetCouriers')->name('admin.shiprocket.get-couriers');
    Route::post('/shiprocket/assign-awb', 'App\Http\Controllers\Admin\OrderController@shiprocketAssignAwb')->name('admin.shiprocket.assign-awb');
    Route::get('/shiprocket/label/{order_id}', 'App\Http\Controllers\Admin\OrderController@shiprocketGenerateLabel')->name('admin.shiprocket.generate-label');
    Route::get('/shiprocket/manifest/{order_id}', 'App\Http\Controllers\Admin\OrderController@shiprocketGenerateManifest')->name('admin.shiprocket.generate-manifest');
    Route::post('/shiprocket/cancel', 'App\Http\Controllers\Admin\OrderController@shiprocketCancelShipment')->name('admin.shiprocket.cancel');

    // Qikink Print on Demand Management inside Admin Panel
    Route::post('/qikink/push-order', 'App\Http\Controllers\Admin\OrderController@qikinkCreateOrder')->name('admin.qikink.push-order');
    Route::post('/qikink/sync-order/{id}', 'App\Http\Controllers\Admin\OrderController@qikinkSyncOrder')->name('admin.qikink.sync-order');

    Route::resource('payments', 'App\Http\Controllers\Admin\PaymentController');
    Route::get('payments-list', 'App\Http\Controllers\Admin\PaymentController@listPayments')->name('payments.list');
    Route::post('/payments-update-status', 'App\Http\Controllers\Admin\PaymentController@updateStatus')->name('payments.update-status');
    Route::delete('/delete-payment', 'App\Http\Controllers\Admin\PaymentController@delete')->name('payments.delete');

    // Product Colors Routes
    Route::resource('product-colors', 'App\Http\Controllers\Admin\ProductColorController');
    Route::get('product-colors-list', 'App\Http\Controllers\Admin\ProductColorController@listColors')->name('product-colors.list');
    Route::delete('/delete-product-color', 'App\Http\Controllers\Admin\ProductColorController@delete')->name('product-colors.delete');



    Route::get('users-list', 'App\Http\Controllers\Admin\UserController@listUsers')->name('users.list');
    Route::get('users', 'App\Http\Controllers\Admin\UserController@index')->name('users.index');



    // Coupons Routes
    Route::resource('coupons', 'App\Http\Controllers\Admin\CouponController');
    Route::get('coupons-list', 'App\Http\Controllers\Admin\CouponController@listCoupons')->name('coupons.list');
    Route::delete('/delete-coupon', 'App\Http\Controllers\Admin\CouponController@delete')->name('coupons.delete');
    Route::post('/coupons/assign-to-users', 'App\Http\Controllers\Admin\CouponController@assignToUsers')->name('coupons.assign-to-users');
    Route::post('/coupons-assign', 'App\Http\Controllers\Admin\CouponController@assignToUsers')->name('coupons.assign');


    // points

    Route::prefix('rewards')->name('rewards.')->group(function () {
        // Admin listing page
        Route::get('/', [RewardController::class, 'index'])->name('index');

        // AJAX fetch of reward transactions
        Route::get('/list', [RewardController::class, 'listTransactions'])->name('list');

        // View single transaction
        Route::get('/view/{id}', [RewardController::class, 'show'])->name('view');

        // Approve pending transaction (POST)
        Route::post('/approve', [RewardController::class, 'approveTransaction'])->name('approve');

        // Reverse pending transaction (POST)
        Route::post('/reverse', [RewardController::class, 'reverseTransaction'])->name('reverse');
    });

    // Reward Settings Routes
    Route::prefix('reward-settings')->name('reward-settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\RewardPointsSettingsController::class, 'index'])->name('index');
        Route::post('/store', [App\Http\Controllers\Admin\RewardPointsSettingsController::class, 'store'])->name('store');
    });

    Route::resource('points', PointsConfigController::class);
    Route::get('points-list', 'App\Http\Controllers\Admin\PointsConfigController@listPoints')->name('points.list');
    Route::delete('/delete-points', 'App\Http\Controllers\Admin\PointsConfigController@delete')->name('points.delete');


    Route::group(['prefix' => 'settings'], function () {
        Route::get('/', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::post('/store', [App\Http\Controllers\Admin\SettingController::class, 'store'])->name('settings.store');
    });

    Route::resource('returns', 'App\Http\Controllers\Admin\ReturnController');
    Route::get('returns-list', 'App\Http\Controllers\Admin\ReturnController@listReturns')->name('returns.list');
    Route::get('returns-details/{id}', 'App\Http\Controllers\Admin\ReturnController@getReturnDetails')->name('returns.details');
    Route::post('/returns-update-status/{id}', 'App\Http\Controllers\Admin\ReturnController@update')->name('returns.update-status');
    Route::delete('/delete-return', 'App\Http\Controllers\Admin\ReturnController@destroy')->name('returns.delete');

    // Exchanges Routes
    Route::resource('exchanges', 'App\Http\Controllers\Admin\ExchangeController');
    Route::get('exchanges-list', 'App\Http\Controllers\Admin\ExchangeController@listExchanges')->name('exchanges.list');
    Route::get('exchanges-details/{id}', 'App\Http\Controllers\Admin\ExchangeController@getExchangeDetails')->name('exchanges.details');
    Route::post('/exchanges-update-status/{id}', 'App\Http\Controllers\Admin\ExchangeController@updateStatus')->name('exchanges.update-status');
    Route::post('/exchanges-schedule-pickup/{id}', 'App\Http\Controllers\Admin\ExchangeController@schedulePickup')->name('exchanges.schedule-pickup');
    Route::post('/exchanges-schedule-delivery/{id}', 'App\Http\Controllers\Admin\ExchangeController@scheduleDelivery')->name('exchanges.schedule-delivery');
    Route::post('/exchanges-mark-completed/{id}', 'App\Http\Controllers\Admin\ExchangeController@markCompleted')->name('exchanges.mark-completed');
    Route::delete('/delete-exchange', 'App\Http\Controllers\Admin\ExchangeController@destroy')->name('exchanges.delete');
});
