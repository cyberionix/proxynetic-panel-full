<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AlertController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BulkEmailController;
use App\Http\Controllers\Admin\BulkNotificationController;
use App\Http\Controllers\Admin\BulkSmsController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\CheckoutController;
use App\Http\Controllers\Admin\CouponCodeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DistrictController;
use App\Http\Controllers\Admin\EmailLogController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SmsLogController;
use App\Http\Controllers\Admin\SupportController;
use App\Http\Controllers\Admin\SupportAutoReplyController;
use App\Http\Controllers\Admin\SupportTemplateController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\ThreeProxyServerController;
use App\Http\Controllers\Admin\ThreeProxyPoolController;
use App\Http\Controllers\Admin\ThreeProxyLogController;
use App\Http\Controllers\Admin\LocaltonetV4SettingsController;
use App\Http\Controllers\Admin\PProxyController;
use App\Http\Controllers\Admin\PProxyUPoolController;
use App\Http\Controllers\Admin\LocaltonetRotatingPoolController;
use App\Http\Controllers\Admin\TokenPoolController;
use App\Http\Controllers\Admin\IpPoolController;
use App\Http\Controllers\Admin\UserAddressController;
use App\Http\Controllers\Admin\UserBalanceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserGroupController;
use App\Http\Controllers\Admin\UserKycController;
use App\Http\Controllers\Admin\UserSessionController;
use App\Http\Controllers\Portal\OrderLocaltonetController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'netAdmin', 'as' => 'admin.'], function () {
    Route::group(["middleware" => "guest:admin", "prefix" => "/auth", "as" => "auth."], function () {
        Route::get('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/login', [AuthController::class, 'loginPost'])->name('loginPost');
    });
    Route::middleware('auth:admin')->group(function () {

        Route::group(['prefix' => '/reports', 'as' => 'reports.'], function () {
            Route::any('/financial', [ReportController::class, 'financialReports'])->name('financial');
//            Route::post('/financial/ajax',[ReportController::class, 'financialReportsAjax'])->name('financialAjax');
        });
        Route::get('/system-settings', [SystemController::class, 'settings'])->name('settings');
        Route::post('/system-settings', [SystemController::class, 'updateSettings'])->name('updateSettings');
        Route::get('/system-status-ajax', [SystemController::class, 'systemStatusAjax'])->name('systemStatusAjax');
        Route::get('/pending-jobs-ajax', [SystemController::class, 'pendingJobsAjax'])->name('pendingJobsAjax');
        Route::post('/system-process-start', [SystemController::class, 'startProcess'])->name('systemProcessStart');
        Route::post('/system-process-stop', [SystemController::class, 'stopProcess'])->name('systemProcessStop');
        Route::post('/test-sms-connection', [SystemController::class, 'testSmsConnection'])->name('testSmsConnection');
        Route::post('/test-sms-send', [SystemController::class, 'testSmsSend'])->name('testSmsSend');
        Route::post('/test-mail-connection', [SystemController::class, 'testMailConnection'])->name('testMailConnection');
        Route::post('/test-mail-send', [SystemController::class, 'testMailSend'])->name('testMailSend');
        Route::get('/notification-template/{id}', [SystemController::class, 'getNotificationTemplate'])->name('getNotificationTemplate');
        Route::post('/notification-template/{id}', [SystemController::class, 'updateNotificationTemplate'])->name('updateNotificationTemplate');
        Route::post('/notification-template/{id}/toggle', [SystemController::class, 'toggleNotificationTemplate'])->name('toggleNotificationTemplate');
        Route::post('/site-settings/save', [SystemController::class, 'saveSiteSettings'])->name('siteSave');
        Route::post('/telegram/save', [SystemController::class, 'saveTelegramSettings'])->name('telegramSave');
        Route::post('/telegram/test', [SystemController::class, 'testTelegram'])->name('telegramTest');
        Route::post('/telegram/find-chat-id', [SystemController::class, 'findTelegramChatId'])->name('telegramFindChatId');
        Route::get('/test', function (\App\Library\EInvoiceManager $EInvoiceManager, \Illuminate\Http\Request $request) {

            $ipAddress = $request->ip();

            if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Geçersiz IP adresi.'
                ], 422);
            }

            $data = file_get_contents('https://api.ipapi.is/?q=' . $ipAddress);

            $data = json_decode($data);
dd($data->is_proxy || $data->is_vpn);
            return $data->is_proxy || $data->is_vpn;

            $inovice = \App\Models\Invoice::latest()->first();

            return $EInvoiceManager->createInvoice($inovice);
            return $inovice;
        });

        Route::post('/log-out', [AuthController::class, 'logOutPost'])->name('auth.logOutPost');
        Route::post('/user-account-login/{user}', [AuthController::class, 'userAccountLogin'])->name('auth.userAccountLogin');

        Route::group(['prefix' => 'profile', 'as' => 'profile.'], function () {
            Route::get('/', [ProfileController::class, 'index'])->name('index');
            Route::post('/update-profile', [ProfileController::class, 'updateProfile'])->name('updateProfile');
            Route::post('/update-password', [ProfileController::class, 'updatePassword'])->name('updatePassword');
            Route::post('/update-signature', [ProfileController::class, 'updateSignature'])->name('updateSignature');
        });

        Route::get('/', [DashboardController::class, "index"])->name("dashboard_");
        Route::get('/dashboard', [DashboardController::class, "index"])->name("dashboard");

        Route::group(['prefix' => 'dashboard', 'as' => 'dashboard.'], function () {
            Route::get('/get-sale-reports', [DashboardController::class, "getSaleReports"])->name("getSaleReports");
            Route::get('/get-customer-reports', [DashboardController::class, "getCustomerReports"])->name("getCustomerReports");
            Route::get('/get-support-reports', [DashboardController::class, "getSupportReports"])->name("getSupportReports");
            Route::get('/get-last-orders', [DashboardController::class, "getLastOrders"])->name("getLastOrders");
            Route::get('/get-pending-supports', [DashboardController::class, "getPendingSupports"])->name("getPendingSupports");
            Route::get('/get-upcoming-invoices', [DashboardController::class, "getUpcomingInvoices"])->name("getUpcomingInvoices");
        });

        Route::get('/district/search', [DistrictController::class, "search"])->name("district.search");
        Route::get('/blank-page', function () {
            return view("admin.pages.blank-page");
        })->name("blankPage");
        Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
            Route::get('/', [UserController::class, "index"])->name("index");
            Route::get('/search', [UserController::class, "search"])->name("search");
            Route::post('/ajax', [UserController::class, "ajax"])->name("ajax");
            Route::get('/{user}', [UserController::class, "show"])->name("show");
            Route::post('/store', [UserController::class, "store"])->name("store");
            Route::get('/find/{user}', [UserController::class, "find"])->name("find");
            Route::post('/update/{user}', [UserController::class, "update"])->name("update");
            Route::post('/delete/{user}', [UserController::class, "delete"])->name("delete");

            Route::post('/force-kyc-active/{user}', [UserKycController::class, "forceKycActive"])->name("forceKycActive");
            Route::post('/force-kyc-passive/{user}', [UserKycController::class, "forceKycPassive"])->name("forceKycPassive");
            Route::group(['prefix' => 'kyc/images/{user}', 'as' => 'kyc.images.'], function () {
                Route::get('/card-front-side', [UserKycController::class, "cardFrontSideImage"])->name("cardFrontSide");
                Route::get('/card-back-side', [UserKycController::class, "cardBackSideImage"])->name("cardBackSide");
                Route::get('/selfie', [UserKycController::class, "selfieImage"])->name("selfie");
            });
            Route::post('/confirmed-kyc/{user}', [UserKycController::class, "confirmedKyc"])->name("confirmedKyc");
            Route::post('/not-confirmed-kyc/{user}', [UserKycController::class, "notConfirmedKyc"])->name("notConfirmedKyc");


            Route::group(['prefix' => '/addresses', 'as' => 'addresses.'], function () {
                Route::post('/store/{user}', [UserAddressController::class, "store"])->name("store");
                Route::post('/find/{address}', [UserAddressController::class, "find"])->name("find");
                Route::post('/update/{address}', [UserAddressController::class, "update"])->name("update");
                Route::post('/delete/{address}', [UserAddressController::class, "delete"])->name("delete");
                Route::get('/search/{user}', [UserAddressController::class, "search"])->name("search");
            });
            Route::post('/reset-pass', [UserController::class, "resetPassword"])->name("resetPassword");

            Route::post('/ban-account/{user}', [UserController::class, "banAccount"])->name("banAccount");
            Route::post('/unban-account/{user}', [UserController::class, "unbanAccount"])->name("unbanAccount");

            Route::group(['prefix' => '/balance', 'as' => 'balance.'], function () {
                Route::post('/ajax', [UserBalanceController::class, "ajax"])->name("ajax");
                Route::post('/input/{user}', [UserBalanceController::class, "input"])->name("input");
                Route::post('/output/{user}', [UserBalanceController::class, "output"])->name("output");
            });

            Route::post('/account-login/{user}', [UserController::class, "accountLogin"])->name("accountLogin");
            Route::get('/get-last-login-ip/{user}', [UserController::class, "getLastLoginIp"])->name("getLastLoginIp");

            Route::post('/security/{user}', [UserController::class, "updateSecurity"])->name("updateSecurity");
        });
        Route::group(['prefix' => 'user-groups', 'as' => 'userGroups.'], function () {
            Route::get('/', [UserGroupController::class, "index"])->name("index");
            Route::post('/ajax', [UserGroupController::class, "ajax"])->name("ajax");
            Route::post('/store', [UserGroupController::class, "store"])->name("store");
            Route::post('/update', [UserGroupController::class, "update"])->name("update");
            Route::post('/delete', [UserGroupController::class, "delete"])->name("delete");
        });
        Route::group(['prefix' => 'orders', 'as' => 'orders.'], function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::post('/ajax', [OrderController::class, "ajax"])->name("ajax");
            Route::get('/create', [OrderController::class, "create"])->name("create");
            Route::post('/delete/{order}', [OrderController::class, "delete"])->name("delete");
            Route::post('/store', [OrderController::class, "store"])->name("store");
            Route::group(['prefix' => '{order}/localtonet', 'as' => 'localtonet.'], function () {
                Route::group(['middleware' => ['localtonet_forgot_cache_if_action']], function () {
                    Route::post('/v4-connectivity-test', [OrderLocaltonetController::class, 'v4ConnectivityTest'])->name('v4ConnectivityTest');
                    Route::post('/v4-toggle-protocol', [OrderLocaltonetController::class, 'v4ToggleProtocol'])->name('v4ToggleProtocol');
                    Route::post('/authentication', [OrderLocaltonetController::class, 'authentication'])->name('authentication');
                    Route::post('/start', [OrderLocaltonetController::class, 'start'])->name('start');
                    Route::post('/stop', [OrderLocaltonetController::class, 'stop'])->name('stop');
                    Route::post('/set-server-port', [OrderLocaltonetController::class, 'setServerPort'])->name('setServerPort');
                    Route::post('/set-auto-airplane-mode-setting', [OrderLocaltonetController::class, 'setAutoAirplaneModeSetting'])->name('setAutoAirplaneModeSetting');
                    Route::post('/ip-change-post', [OrderLocaltonetController::class, 'ipChangePost'])->name('ipChangePost');
                    Route::post('/device-restart-post', [OrderLocaltonetController::class, 'deviceRestartPost'])->name('deviceRestartPost');
                    Route::post('/get-ip-history', [OrderLocaltonetController::class, 'getIpHistory'])->name('getIpHistory');
                    Route::post('/admin-adjust-quota-duration', [OrderController::class, 'applyAdminLocaltonetQuotaAndDuration'])->name('adminAdjustQuotaDuration');
                    Route::post('/proxy-check', [OrderLocaltonetController::class, 'proxyCheck'])->name('proxyCheck');
                });
            });
            Route::get('/{order}', [OrderController::class, "show"])->name("show");
            Route::post('/update/{order}', [OrderController::class, "update"])->name("update");
            Route::post('/process-localtonet-delivery-now/{order}', [OrderController::class, 'processLocaltonetDeliveryNow'])->name('processLocaltonetDeliveryNow');
            Route::post('/complete-delivery/{order}', [OrderController::class, "completeDelivery"])->name("completeDelivery");
            Route::post('/remove-delivery/{order}', [OrderController::class, "removeDelivery"])->name("removeDelivery");
            Route::post('/stop-tunnels/{order}', [OrderController::class, 'stopTunnels'])->name('stopTunnels');
            Route::post('/start-tunnels/{order}', [OrderController::class, 'startTunnels'])->name('startTunnels');
            Route::post('/change-localtonet-proxy-id/{order}', [OrderController::class, "changeLocaltonetProxyId"])->name("changeLocaltonetProxyId");
            Route::post('/change-localtonet-device/{order}', [OrderController::class, "changeLocaltonetDevice"])->name("changeLocaltonetDevice");
            Route::post('/change-stack-proxies/{order}', [OrderController::class, "changeStackProxies"])->name("changeStackProxies");
            Route::post('/change-localtonet-proxy-type/{order}', [OrderController::class, "changeLocaltonetProxyType"])->name("changeLocaltonetProxyType");
            Route::post('/three-proxy-reinstall/{order}', [OrderController::class, 'threeProxyReinstall'])->name('threeProxyReinstall');
            Route::post('/three-proxy-change-credentials/{order}', [OrderController::class, 'threeProxyChangeCredentials'])->name('threeProxyChangeCredentials');
            Route::post('/three-proxy-change-port/{order}', [OrderController::class, 'threeProxyChangePort'])->name('threeProxyChangePort');
            Route::post('/pproxyu-update-info/{order}', [OrderController::class, 'pproxyuUpdateInfo'])->name('pproxyuUpdateInfo');
            Route::post('/bulk-action', [OrderController::class, 'bulkAction'])->name('bulkAction');
        });
        Route::group(['prefix' => 'invoices', 'as' => 'invoices.'], function () {
            Route::get('/', [InvoiceController::class, "index"])->name("index");
            Route::get('/product-search', [InvoiceController::class, "productSearch"])->name("productSearch");
            Route::get('/product-find', [InvoiceController::class, "productFind"])->name("productFind");
            Route::get('/create', [InvoiceController::class, "create"])->name("create");
            Route::post('/store', [InvoiceController::class, "store"])->name("store");
            Route::post('/ajax', [InvoiceController::class, "ajax"])->name("ajax");
            Route::get('/status-counts', [InvoiceController::class, "statusCounts"])->name("statusCounts");
            Route::post('/bulk-action', [InvoiceController::class, "bulkAction"])->name("bulkAction");
            Route::get('/view-as-customer/{invoice}', [InvoiceController::class, "viewAsCustomer"])->name("viewAsCustomer");
            Route::get('/{invoice}', [InvoiceController::class, "show"])->name("show");
            Route::post('/update/{invoice}', [InvoiceController::class, "update"])->name("update");
            Route::post('/delete/{invoice}', [InvoiceController::class, "delete"])->name("delete");
            Route::post('/formalize/{invoice}', [InvoiceController::class, "formalize"])->name("formalize");
            Route::post('/toggle-payment-status/{invoice}', [InvoiceController::class, "togglePaymentStatus"])->name("togglePaymentStatus");
            Route::get('/pdf/{invoice}', [InvoiceController::class, 'showPdf'])->name('showPdf');
        });
        Route::group(['prefix' => 'coupons', 'as' => 'couponCodes.'], function () {
            Route::get('/', [CouponCodeController::class, "index"])->name("index");
            Route::post('/find', [CouponCodeController::class, "find"])->name("find");
            Route::post('/ajax', [CouponCodeController::class, "ajax"])->name("ajax");
            Route::post('/store', [CouponCodeController::class, "store"])->name("store");
            Route::post('/update', [CouponCodeController::class, "update"])->name("update");
            Route::post('/delete', [CouponCodeController::class, "delete"])->name("delete");

        });
        Route::group(['prefix' => 'products', 'as' => 'products.'], function () {
            Route::get('/', [ProductController::class, "index"])->name("index");
            Route::get('/search', [ProductController::class, "search"])->name("search");
            Route::post('/ajax', [ProductController::class, "ajax"])->name("ajax");
            Route::get('/create', [ProductController::class, "create"])->name("create");
            Route::post('/store', [ProductController::class, "store"])->name("store");
            Route::get('/find/{product}', [ProductController::class, "find"])->name("find");
            Route::get('/{product}', [ProductController::class, "edit"])->whereNumber('product')->name("edit");
            Route::post('/update/{product}', [ProductController::class, "update"])->name("update");
            Route::post('/destroy/{product}', [ProductController::class, "destroy"])->name("destroy");

            Route::group(['prefix' => 'categories', 'as' => 'categories.'], function () {
                Route::get('/', [ProductCategoryController::class, "index"])->name("index");
                Route::post('/store', [ProductCategoryController::class, "store"])->name("store");
                Route::get('/find/{productCategory}', [ProductCategoryController::class, "find"])->name("find");
                Route::post('/update/{productCategory}', [ProductCategoryController::class, "update"])->name("update");
                Route::post('/delete/{productCategory}', [ProductCategoryController::class, "delete"])->name("delete");
            });
            Route::group(['prefix' => '/3proxy', 'as' => '3proxy.'], function () {
                Route::group(['prefix' => '/servers', 'as' => 'servers.'], function () {
                    Route::get('/', [ThreeProxyServerController::class, 'index'])->name("index");
                    Route::get('/proxies/{ThreeProxyServer}', [ThreeProxyServerController::class, 'proxyList'])->name("proxyList");
                    Route::post('/bulk-create/{ThreeProxyServer}', [ThreeProxyServerController::class, 'bulkCreate'])->name("bulkCreate");
                    Route::post('/ajax', [ThreeProxyServerController::class, "ajax"])->name("ajax");
                    Route::post('/store', [ThreeProxyServerController::class, "store"])->name("store");
                    Route::post('/update', [ThreeProxyServerController::class, "update"])->name("update");
                    Route::post('/delete', [ThreeProxyServerController::class, "delete"])->name("delete");

                });
                Route::get('/create-proxy', [ThreeProxyServerController::class, 'createProxy'])->name("createProxy");
            });

            Route::group(['prefix' => '3proxy-pools', 'as' => '3proxyPools.'], function () {
                Route::post('/ajax', [ThreeProxyPoolController::class, 'ajax'])->name('ajax');
                Route::get('/{threeProxyPool}', [ThreeProxyPoolController::class, 'show'])->name('show');
                Route::post('/', [ThreeProxyPoolController::class, 'store'])->name('store');
                Route::post('/update/{threeProxyPool}', [ThreeProxyPoolController::class, 'update'])->name('update');
                Route::post('/delete/{threeProxyPool}', [ThreeProxyPoolController::class, 'delete'])->name('delete');
            });
        });
        Route::group(['prefix' => 'checkouts', 'as' => 'checkouts.'], function () {
            Route::get('/', [CheckoutController::class, "index"])->name("index");
            Route::post('/ajax', [CheckoutController::class, "ajax"])->name("ajax");
            Route::post('/store/{user}', [CheckoutController::class, "store"])->name("store");
            Route::post('/find/{checkout}', [CheckoutController::class, "find"])->name("find");
            Route::post('/payment-status-update/{checkout}', [CheckoutController::class, "paymentStatusUpdate"])->name("paymentStatusUpdate");
        });
        Route::group(['prefix' => 'calendar', 'as' => 'calendar.'], function () {
            Route::get('/', [CalendarController::class, 'index'])->name("index");
            Route::get('/get-events', [CalendarController::class, 'getEvents']);
            Route::post('/store', [CalendarController::class, 'store'])->name("store");
            Route::post('/update', [CalendarController::class, 'update'])->name("update");
            Route::post('/event-drop-update', [CalendarController::class, 'eventDropUpdate'])->name("eventDropUpdate");
            Route::post('/delete', [CalendarController::class, 'delete'])->name("delete");
        });
        Route::group(['prefix' => 'bulk-sms', 'as' => 'bulkSms.'], function () {
            Route::get('/', [BulkSmsController::class, "index"])->name("index");
            Route::post('/send', [BulkSmsController::class, "send"])->name("send");
        });
        Route::group(['prefix' => 'bulk-email', 'as' => 'bulkEmail.'], function () {
            Route::get('/', [BulkEmailController::class, "index"])->name("index");
            Route::post('/send', [BulkEmailController::class, "send"])->name("send");
            Route::post('/show-users', [BulkEmailController::class, "showUsers"])->name("showUsers");
        });
        Route::group(['prefix' => '/sms-logs', 'as' => 'smsLogs.'], function () {
            Route::get('/', [SmsLogController::class, 'index'])->name('index');
            Route::post('/ajax', [SmsLogController::class, 'ajax'])->name('ajax');

        });
        Route::group(['prefix' => '/email-logs', 'as' => 'emailLogs.'], function () {
            Route::get('/', [EmailLogController::class, 'index'])->name('index');
            Route::post('/ajax', [EmailLogController::class, 'ajax'])->name('ajax');
            Route::post('/find/{id?}', [EmailLogController::class, 'find'])->name('find');
        });
        Route::group(['prefix' => 'bulk-notifications', 'as' => 'bulkNotifications.'], function () {
            Route::get('/', [BulkNotificationController::class, 'index'])->name("index");
            Route::post('/ajax', [BulkNotificationController::class, 'ajax'])->name("ajax");
            Route::get('/create', [BulkNotificationController::class, 'create'])->name("create");
            Route::post('/store', [BulkNotificationController::class, 'store'])->name("store");
            Route::get('/edit/{bulkNotification}', [BulkNotificationController::class, 'edit'])->name("edit");
            Route::post('/update/{bulkNotification}', [BulkNotificationController::class, 'update'])->name("update");
            Route::post('/delete/{bulkNotification}', [BulkNotificationController::class, "delete"])->name("delete");
        });
        Route::group(['prefix' => 'activity-logs', 'as' => 'activityLogs.'], function () {
            Route::get('/', [ActivityLogController::class, 'index'])->name("index");
            Route::post('/ajax', [ActivityLogController::class, 'ajax'])->name("ajax");
        });
        Route::group(['prefix' => 'supports', 'as' => 'supports.'], function () {
            Route::get('/', [SupportController::class, 'index'])->name('index');
            Route::post('/ajax', [SupportController::class, 'ajax'])->name('ajax');
            Route::post('/bulk-action', [SupportController::class, 'bulkAction'])->name('bulkAction');
            Route::get('/new-tickets-poll', [SupportController::class, 'newTicketsPoll'])->name('newTicketsPoll');

            Route::group(['prefix' => 'templates', 'as' => 'templates.'], function () {
                Route::get('/', [SupportTemplateController::class, 'index'])->name('index');
                Route::post('/store', [SupportTemplateController::class, 'store'])->name('store');
                Route::post('/update/{template}', [SupportTemplateController::class, 'update'])->name('update');
                Route::post('/delete/{template}', [SupportTemplateController::class, 'delete'])->name('delete');
                Route::get('/get-active', [SupportTemplateController::class, 'getActive'])->name('getActive');
            });

            Route::group(['prefix' => 'auto-replies', 'as' => 'autoReplies.'], function () {
                Route::get('/', [SupportAutoReplyController::class, 'index'])->name('index');
                Route::post('/store', [SupportAutoReplyController::class, 'store'])->name('store');
                Route::post('/update/{autoReply}', [SupportAutoReplyController::class, 'update'])->name('update');
                Route::post('/delete/{autoReply}', [SupportAutoReplyController::class, 'delete'])->name('delete');
                Route::post('/toggle-status/{autoReply}', [SupportAutoReplyController::class, 'toggleStatus'])->name('toggleStatus');
            });

            Route::get('/{support}', [SupportController::class, 'show'])->name('show');
            Route::get('/find/{support}', [SupportController::class, 'find'])->name('find');
            Route::post('/save-message/{support}', [SupportController::class, 'saveMessage'])->name("saveMessage");
            Route::post('/typing/{support}', [SupportController::class, 'typing'])->name('typing');
            Route::get('/poll/{support}', [SupportController::class, 'pollMessages'])->name('poll');
            Route::post('/update/status/{support}', [SupportController::class, 'updateStatus'])->name("updateStatus");
            Route::post('/update/department/{support}', [SupportController::class, 'updateDepartment'])->name("updateDepartment");
            Route::post('/lock/{support}', [SupportController::class, 'lock'])->name("lock");
            Route::post('/unlock/{support}', [SupportController::class, 'unlock'])->name("unlock");
            Route::post('/delete/{support}', [SupportController::class, 'delete'])->name("delete");
        });

        Route::group(['prefix' => 'alerts', 'as' => 'alerts.'], function () {
            Route::get('/', [AlertController::class, 'index'])->name('index');
            Route::post('/ajax', [AlertController::class, 'ajax'])->name('ajax');
            Route::post('/store', [AlertController::class, 'store'])->name('store');
            Route::get('/find/{alert}', [AlertController::class, 'find'])->name('find');
            Route::post('/update/{alert}', [AlertController::class, 'update'])->name('update');
            Route::post('/delete/{alert}', [AlertController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'prices', 'as' => 'prices.'], function () {
            Route::get('/search-by-roduct', [\App\Http\Controllers\Admin\PriceController::class, "searchByProduct"])->name("searchByProduct");
        });

        Route::group(['prefix' => 'user-sessions', 'as' => 'userSessions.'], function () {
            Route::get('/', [UserSessionController::class, "index"])->name("index");
            Route::post('/ajax', [UserSessionController::class, "ajax"])->name("ajax");
        });

        Route::group(['prefix' => 'token-pools', 'as' => 'tokenPools.'], function () {
            Route::get('/', [TokenPoolController::class, "index"])->name("index");
            Route::post('/ajax', [TokenPoolController::class, "ajax"])->name("ajax");
            Route::post('/', [TokenPoolController::class, "store"])->name("store");
            Route::get('/{tokenPool}', [TokenPoolController::class, "show"])->name("show");
            Route::post('/update/{tokenPool}', [TokenPoolController::class, "update"])->name("update");
            Route::post('/delete/{tokenPool}', [TokenPoolController::class, "delete"])->name("delete");
        });

        Route::group(['prefix' => 'ip-pools', 'as' => 'ipPools.'], function () {
            Route::post('/ajax', [IpPoolController::class, 'ajax'])->name('ajax');
            Route::get('/{ipPool}', [IpPoolController::class, 'show'])->name('show');
            Route::post('/', [IpPoolController::class, 'store'])->name('store');
            Route::post('/update/{ipPool}', [IpPoolController::class, 'update'])->name('update');
            Route::post('/delete/{ipPool}', [IpPoolController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'localtonet-rotating-pools', 'as' => 'localtonetRotatingPools.'], function () {
            Route::post('/ajax', [LocaltonetRotatingPoolController::class, 'ajax'])->name('ajax');
            Route::get('/{localtonetRotatingPool}', [LocaltonetRotatingPoolController::class, 'show'])->name('show');
            Route::post('/', [LocaltonetRotatingPoolController::class, 'store'])->name('store');
            Route::post('/update/{localtonetRotatingPool}', [LocaltonetRotatingPoolController::class, 'update'])->name('update');
            Route::post('/delete/{localtonetRotatingPool}', [LocaltonetRotatingPoolController::class, 'delete'])->name('delete');
        });

        Route::get('/localtonet-v4/settings', [LocaltonetV4SettingsController::class, 'edit'])->name('localtonetV4.settings');
        Route::post('/localtonet-v4/settings', [LocaltonetV4SettingsController::class, 'update'])->name('localtonetV4.updateSettings');

        Route::group(['prefix' => 'pproxy', 'as' => 'pproxy.'], function () {
            Route::get('/settings', [PProxyController::class, 'settings'])->name('settings');
            Route::post('/settings', [PProxyController::class, 'saveSettings'])->name('saveSettings');
            Route::post('/test-connection', [PProxyController::class, 'testConnection'])->name('testConnection');
        });

        Route::group(['prefix' => 'pproxyu-pool', 'as' => 'pproxyuPool.'], function () {
            Route::post('/ajax', [PProxyUPoolController::class, 'ajax'])->name('ajax');
            Route::post('/', [PProxyUPoolController::class, 'store'])->name('store');
            Route::post('/update/{id}', [PProxyUPoolController::class, 'update'])->name('update');
            Route::post('/delete/{id}', [PProxyUPoolController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-import', [PProxyUPoolController::class, 'bulkImport'])->name('bulkImport');
        });

        Route::get('/three-proxy-logs', [ThreeProxyLogController::class, 'index'])->name('threeProxyLogs.index');
        Route::get('/three-proxy-logs/export', [ThreeProxyLogController::class, 'export'])->name('threeProxyLogs.export');

        Route::group(['prefix' => 'campaigns', 'as' => 'campaigns.'], function () {
            Route::post('/store', [SystemController::class, 'storeCampaign'])->name('store');
            Route::get('/{id}', [SystemController::class, 'getCampaign'])->name('get');
            Route::post('/{id}/update', [SystemController::class, 'updateCampaign'])->name('update');
            Route::post('/{id}/delete', [SystemController::class, 'deleteCampaign'])->name('delete');
            Route::post('/{id}/send', [SystemController::class, 'sendCampaign'])->name('send');
            Route::post('/{id}/duplicate', [SystemController::class, 'duplicateCampaign'])->name('duplicate');
            Route::post('/preview-recipients', [SystemController::class, 'previewCampaignRecipients'])->name('previewRecipients');
        });

    });
});
