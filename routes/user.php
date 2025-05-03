<?php
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Services\User\Auth\Login\Controller\LoginController;
use App\Http\Services\User\GetCopon\Controller\GetCoponController;
use App\Http\Services\User\Auth\Logiout\Controller\LogoutController;
use App\Http\Services\User\GetSlides\Controller\GetSlidesController;
use App\Http\Services\User\MakeOrder\Controller\MakeOrderController;
use App\Http\Services\User\CancelOrder\Controller\CancelOrderController;
use App\Http\Services\User\GetServices\Controller\GetServicesController;
use App\Http\Services\User\OrderHistory\Controller\OrderHistoryController;
use App\Http\Services\Apis\MTNPaymentApi\Controller\MTNPaymentApiController;
use App\Http\Services\User\GetSubService\Controller\GetSubServiceController;
use App\Http\Services\User\RattingDriver\Controller\RattingDriverController;
use App\Http\Services\User\CompletedOrder\Controller\CompletedOrderController;
use App\Http\Services\User\PaymentService\Controller\PaymentServiceController;
use App\Http\Services\User\Auth\UserRegister\Controller\UserRegisterController;
use App\Http\Services\FleetWalletPayment\Controller\FleetWalletPaymentController;
use App\Http\Services\User\GetPaymentMethod\Controller\GetPaymentMethodController;
use App\Http\Services\User\GetWalletHistory\Controller\GetWalletHistoryController;
use App\Http\Services\User\ResearchOnDriver\Controller\ResearchOnDriverController;
use App\Http\Services\User\GetUserNotifications\Controller\GetUserNotificationsController;
use App\Http\Services\User\ProfileManagement\ShowProfile\Controller\ShowProfileController;
use App\Http\Services\User\ProfileManagement\EditeProfile\Controller\EditeProfileController;
use App\Http\Services\User\UserAddressManagement\AddAddress\Controller\AddAddressController;
use App\Http\Services\User\Auth\UserCheckOtpService\Controller\UserCheckOtpServiceController;
use App\Http\Services\User\UserAddressManagement\ShowAddress\Controller\ShowAddressController;
use App\Http\Services\Dashboard\ServiceManagement\ViewService\Controller\ViewServiceController;
use App\Http\Services\User\UserAddressManagement\DeleteAddress\Controller\DeleteAddressController;
use App\Http\Services\Apis\ConfirmPaymentPhoneNumber\Controller\ConfirmPaymentPhoneNumberController;
use App\Http\Services\Apis\MTNConfirmPaymentPhoneNumber\Controller\MTNConfirmPaymentPhoneNumberController;
use App\Http\Services\Apis\SyriatelConfirmPhoneNumber\Controller\SyriatelConfirmPhoneNumberController;
use App\Http\Services\Dashboard\BannersManagement\CreateOrUpdateBanner\Controller\CreateOrUpdateBannerController;
use App\Http\Services\User\ProfileManagement\EdateImageProfile\Controller\EdateImageProfileController;
use App\Http\Services\User\Auth\UserResetPasswordService\Controller\UserResetPasswordServiceController;
use App\Http\Services\User\Auth\UserForgetPasswordService\Controller\UserForgetPasswordServiceController;
use App\Http\Services\User\Auth\UserSendOtpServiceService\Controller\UserSendOtpServiceServiceController;
use App\Http\Services\PoilceAndPrivceManagement\ShowPoilceAndPrivceService\Controller\ShowPoilceAndPrivceServiceController;
use App\Http\Services\PoilceAndPrivceManagement\ViewPoilceAndPrivceService\Controller\ViewPoilceAndPrivceServiceController;
use App\Http\Services\User\GetPublicUserAppSettings\Controller\GetPublicUserAppSettingsController;
use App\Http\Services\User\SendReport\Controller\SendReportController;
use App\Http\Services\User\WalletManagement\AddBalanceByPaymentMethod\Controller\AddBalanceByPaymentMethodController;
use App\Http\Services\User\WalletManagement\ConfirmPhone_AddBalance\Controller\ConfirmPhone_AddBalanceController;

Route::post('/send/otp',UserSendOtpServiceServiceController::class);
Route::post('/check/otp',UserCheckOtpServiceController::class);
Route::post('/change/password',UserForgetPasswordServiceController::class);
Route::post('/register',UserRegisterController::class);
Route::post('/login',LoginController::class);
Route::get('/policy',ShowPoilceAndPrivceServiceController::class);


Route::group(['middleware' => ['set-localization']], function () {
    Route::group(['prefix' => 'settings'], function () {
        Route::get('/app-public-settings', GetPublicUserAppSettingsController::class);
    });
    
});

Route::group(['middleware' => ['auth:user','set-localization']], function () {

    Route::post('rating',RattingDriverController::class);
    Route::post('make-order',MakeOrderController::class);
    Route::post('order/cancel',CancelOrderController::class);
    Route::post('research-on-driver',ResearchOnDriverController::class);
    Route::get('order/history',OrderHistoryController::class);
    Route::get('get/notifications', GetUserNotificationsController::class);
    Route::get('wallet/history',GetWalletHistoryController::class);

    


Route::get('coupon/calculation',GetCoponController::class);


Route::group(['prefix' => 'address'], function () {

        Route::post('/add', AddAddressController::class);
        Route::get('/index', ShowAddressController::class);
        Route::delete('/delete', DeleteAddressController::class);
    });

    Route::group(['prefix' => 'profile'], function () {
        Route::get('/', ShowProfileController::class);
        Route::put('/update', EditeProfileController::class);
        Route::post('/photo', EdateImageProfileController::class);
        Route::post('/reset/password',UserResetPasswordServiceController::class);
        Route::get('/logout',LogoutController::class);
    });


Route::group(['prefix' => 'get'],function(){
    Route::get('slides',GetSlidesController::class);
    Route::get('services',GetServicesController::class);
    Route::get('payment/method',GetPaymentMethodController::class);
    Route::post('sub/services',GetSubServiceController::class);
});


Route::group(['prefix' => 'wallet'], function () {
    Route::Post('/add-balance/by/paymentMethods' , AddBalanceByPaymentMethodController::class);
    Route::Post('/confirm-phoneNumber/add-balance-to-wallet' , ConfirmPhone_AddBalanceController::class);
    
});


Route::post('ride-report', SendReportController::class);
Route::post('addComplaint', SendReportController::class);

Route::group(['prefix' => 'payment'], function () {
    Route::post('/fleet-wallet',FleetWalletPaymentController::class);
    Route::Post('/order' , PaymentServiceController::class);
    Route::Post('/confirm-phoneNumber/completed-order' , MTNConfirmPaymentPhoneNumberController::class);

    // Route::Post('/confirm-syriatel-phoneNumber/completed-order' , SyriatelConfirmPhoneNumberController::class);

});

Route::group(['prefix' => 'order'], function () {
    Route::post('/make',MakeOrderController::class);
    Route::post('/completed' ,CompletedOrderController::class);
});


// Broadcast::routes(['middleware' => ['auth:user']]);
Route::post('banner/add', CreateOrUpdateBannerController::class)->name('banner.store');
});



Route::get('services',GetServicesController::class);
