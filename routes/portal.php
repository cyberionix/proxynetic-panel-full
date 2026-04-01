<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Portal\AuthController;
use App\Http\Controllers\Portal\DashboardController;
use App\Http\Controllers\Portal\UserController;
use App\Http\Controllers\Portal\InvoiceController;
use App\Http\Controllers\Portal\UserAddressController;
use App\Http\Controllers\Portal\SupportController;
use App\Http\Controllers\Portal\OrderController;
use App\Http\Controllers\Portal\BasketController;
use App\Http\Controllers\Portal\ProductController;
use App\Http\Controllers\Portal\CheckoutController;
use App\Http\Controllers\Portal\OrderLocaltonetController;
use App\Http\Controllers\Portal\BalanceController;
use App\Http\Controllers\Portal\UserNotificationController;

Route::get('/test', function () {

});

Route::middleware(["logRequest", "updateLastSeen"])->group(function () {
    Route::prefix('/auth')->group(function () {
        Route::middleware('guest')->group(function () {
            Route::get('/login', [AuthController::class, 'login'])->name('portal.auth.login');
            Route::post('/login', [AuthController::class, 'loginPost'])->name('portal.auth.loginPost');
            Route::get('/register', [AuthController::class, 'register'])->name('portal.auth.register');
            Route::post('/register', [AuthController::class, 'registerPost'])->name('portal.auth.registerPost');
            Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('portal.auth.forgotPassword');
            Route::post('/verify-forgot-password-otp', [AuthController::class, 'verifyForgotPasswordOtp'])->name('portal.auth.verifyForgotPasswordOtp');
            Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('portal.auth.resetPassword');


            Route::get('/login/google', [AuthController::class, 'redirectToGoogle'])->name('portal.auth.login.google.redirect');
            Route::any('/login/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('portal.auth.login.google.callback');
            Route::get('/logout', [AuthController::class, 'logout'])->name('portal.auth.logout')->withoutMiddleware('guest')->middleware('auth');
        });

        Route::post('/send-email-otp', [AuthController::class, 'sendEmailVerificationOTP'])->name('portal.auth.send_email_otp');

        Route::post('/send-sms-otp', [AuthController::class, 'sendPhoneVerificationOTP'])->name('portal.auth.send_phone_otp');
        Route::post('/verify-sms-otp', [AuthController::class, 'verifyPhoneOTP'])->name('portal.auth.verify_phone_otp');

        Route::post('/update-email', [AuthController::class, 'updateEmail'])->name('portal.auth.update_email');
        Route::post('/update-phone', [AuthController::class, 'updatePhone'])->name('portal.auth.update_phone');
    });
    Route::middleware(['auth', 'check.verified_email', 'check.identity', 'check.kyc', 'check.ban', 'check.verified_phone'])->name("portal.")->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard_');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/get-data', [DashboardController::class, 'getData'])->name("dashboard.getData");

        Route::get('/district/search', [\App\Http\Controllers\Portal\DistrictController::class, "search"])->name("district.search");

        Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('checkout');
        Route::post('/save-bank-transfer-notification', [CheckoutController::class, 'saveBankTransferNotification'])->name('save_bank_transfer_notification');
        Route::post('/payment-with-balance', [CheckoutController::class, 'paymentWithBalance'])->name('paymentWithBalance');
        Route::any("/paytr-payment-result", [CheckoutController::class, 'paymentResult'])->name("paytr.paymentResult");

        Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
            Route::post('/change-password', [AuthController::class, 'changePassword'])->name('changePassword');
        });
        Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
            Route::get('/profile', [UserController::class, 'profile'])->name('profile');
            Route::post('verify-email', [UserController::class, "verifyEmail"])->name("verifyEmail");
            Route::post('store-identity-number', [UserController::class, "storeIdentityNumber"])->name("storeIdentityNumber");
            Route::post('check-kyc', [UserController::class, "checkKyc"])->name("checkKyc");
            Route::post('create-appointment/{user}/{availableAppointment}', [UserController::class, "createAppointment"])->name("createAppointment");
            Route::post('update-permission', [UserController::class, "updatePermission"])->name("updatePermission");
            Route::post('save-phone-and-send-verification-otp', [UserController::class, "savePhoneAndSendVerificationOTP"])->name("savePhoneAndSendVerificationOTP");
            Route::group(['prefix' => 'addresses', 'as' => 'addresses.'], function () {
                Route::post('/store', [UserAddressController::class, "store"])->name("store");
                Route::post('/find/{address}', [UserAddressController::class, "find"])->name("find");
                Route::post('/update/{address}', [UserAddressController::class, "update"])->name("update");
                Route::post('/delete/{address}', [UserAddressController::class, "delete"])->name("delete");
            });

            Route::group(['prefix' => '/notifications', 'as' => 'notifications.'], function () {
                Route::get('/list', [UserNotificationController::class, "list"])->name("list");
                Route::get('/redirect/{notificationId}/{routeName}', [UserNotificationController::class, "redirect"])->name("redirect");
            });
        });

        Route::group(['prefix' => 'basket', 'as' => 'basket.'], function () {
            Route::get('/', [BasketController::class, 'index'])->name("index");
            Route::post('/apply-coupon', [BasketController::class, 'applyCoupon'])->name("applyCoupon");
            Route::post('/remove-coupon', [BasketController::class, 'removeCoupon'])->name("removeCoupon");
            Route::post('/add-to-basket/{price}', [BasketController::class, 'addToBasket'])->name("addToBasket");
            Route::post('/remove-basket-item/{basketItem}', [BasketController::class, 'removeBasketItem'])->name("removeBasketItem");
            Route::post('/copy-item-add-to-basket/{basketItem}', [BasketController::class, 'copyItemAddToBasket'])->name("copyItemAddToBasket");
            Route::post('/change-period-to-basket/{basketItem}', [BasketController::class, 'changePeriodToBasket'])->name("changePeriodToBasket");

            Route::get('/payment', [BasketController::class, 'payment'])->name("payment.index");
            Route::post('/payment', [BasketController::class, 'paymentPost'])->name("payment.post");

        });

        Route::group(['prefix' => 'my-products', 'as' => 'orders.'], function () {
            Route::post('/update-order-note/{order}',[OrderController::class,'updateNote'])->name('updateNote');
            Route::get('/', [OrderController::class, "index"])->name("index");
            Route::post('/ajax/{user}', [OrderController::class, "ajax"])->name("ajax");
            Route::get('/{order}', [OrderController::class, "show"])->whereNumber('order')->name("show");
            Route::post('upgrade/{order}', [OrderController::class, "upgrade"])->name("upgrade")->middleware("check.order_status");
            Route::post('add-quota/{order}', [OrderController::class, "addQuotaPost"])->name("addQuotaPost")->middleware("check.order_status");
            Route::post('add-quota-duration/{order}', [OrderController::class, "addQuotaDurationPost"])->name("addQuotaDurationPost")->middleware("check.order_status");
            Route::post('tp-extra-duration/{order}', [OrderController::class, "tpExtraDurationPost"])->name("tpExtraDurationPost")->middleware("check.order_status");
            Route::post('tp-service-action/{order}', [OrderController::class, "tpServiceActionPost"])->name("tpServiceActionPost")->middleware("check.order_status");

            Route::group(['prefix' => 'localtonet', 'as' => 'localtonet.'], function () {
                Route::get('/get-proxy-list-table', [OrderLocaltonetController::class, 'getProxyListTable'])->name('getProxyListTable');
                Route::post('/v4-connectivity-test/{order}', [OrderLocaltonetController::class, 'v4ConnectivityTest'])
                    ->name('v4ConnectivityTest')
                    ->middleware(['localtonet_forgot_cache_if_action']);
                Route::post('/v4-toggle-protocol/{order}', [OrderLocaltonetController::class, 'v4ToggleProtocol'])
                    ->name('v4ToggleProtocol')
                    ->middleware(['localtonet_forgot_cache_if_action', 'check.order_status']);
                Route::group(['middleware' => ['localtonet_forgot_cache_if_action', 'check.order_status']], function () {
                    Route::post('/authentication/{order}', [OrderLocaltonetController::class, 'authentication'])->name('authentication');
                    Route::post('/start/{order}', [OrderLocaltonetController::class, 'start'])->name('start');
                    Route::post('/stop/{order}', [OrderLocaltonetController::class, 'stop'])->name('stop');
                    Route::post('/set-server-port/{order}', [OrderLocaltonetController::class, 'setServerPort'])->name('setServerPort');
                    Route::post('/set-auto-airplane-mode-setting/{order}', [OrderLocaltonetController::class, 'setAutoAirplaneModeSetting'])->name('setAutoAirplaneModeSetting');
                    Route::post('/ip-change-post/{order}', [OrderLocaltonetController::class, 'ipChangePost'])->name('ipChangePost');
                    Route::post('/device-restart-post/{order}', [OrderLocaltonetController::class, 'deviceRestartPost'])->name('deviceRestartPost');
                    Route::post('/get-ip-history/{order}', [OrderLocaltonetController::class, 'getIpHistory'])->name('getIpHistory');
                });
                Route::post('/lr-change-password/{order}', [OrderLocaltonetController::class, 'lrChangePassword'])->name('lrChangePassword');
                Route::post('/lr-get-clients/{order}', [OrderLocaltonetController::class, 'lrGetClients'])->name('lrGetClients');
                Route::post('/three-proxy-change-credentials/{order}', [OrderLocaltonetController::class, 'threeProxyChangeCredentials'])->name('threeProxyChangeCredentials');
            });
        });
        Route::group(['prefix' => 'invoices', 'as' => 'invoices.'], function () {
            Route::get('/', [InvoiceController::class, "index"])->name("index");
            Route::post('/ajax/{user}', [InvoiceController::class, "ajax"])->name("ajax");
            Route::get('/{invoice}', [InvoiceController::class, "show"])->whereNumber('invoice')->name("show");
        });
        Route::group(['prefix' => 'supports', 'as' => 'supports.'], function () {
            Route::get('/', [SupportController::class, 'index'])->name("index");
            Route::post('/store', [SupportController::class, 'store'])->name("store");
            Route::post('/ajax', [SupportController::class, 'ajax'])->name('ajax');
            Route::get('/show/{support}', [SupportController::class, 'show'])->name("show");
            Route::get('/find/{support}', [SupportController::class, 'find'])->name("find");
            Route::post('/save-message/{support}', [SupportController::class, 'saveMessage'])->name("saveMessage")->middleware("check.locked_support");

        });
        Route::group(['prefix' => 'products', 'as' => 'products.'], function () {
            Route::get('/category/{productCategory}', [ProductController::class, 'index'])->name("index");
            Route::get('/test-product', [ProductController::class, 'testProduct'])->name("testProduct");
        });

        Route::group(['prefix' => 'balance', 'as' => 'balance.'], function () {
            Route::get('/', [BalanceController::class, 'index'])->name("index");
            Route::post('/ajax', [BalanceController::class, 'ajax'])->name("ajax");
            Route::post('/add-balance', [BalanceController::class, 'addBalancePost'])->name("addBalancePost");
        });
    });

    Route::get('/ip-change/{order}', [OrderLocaltonetController::class, 'ipChange'])->name('portal.ipChange');
    Route::get('/device-restart/{order}', [OrderLocaltonetController::class, 'deviceRestart'])->name('portal.deviceRestart');
});
