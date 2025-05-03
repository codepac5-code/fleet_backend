<?php
use Illuminate\Support\Facades\Route;
use App\Http\Services\Driver\Earning\Controller\EarningController;
use App\Http\Services\Driver\Auth\Login\Controller\LoginController;
use App\Http\Services\User\Auth\Logiout\Controller\LogoutController;
use App\Http\Services\Driver\AcceptOrder\Controller\AcceptOrderController;
use App\Http\Services\Driver\RattingUser\Controller\RattingUserController;
use App\Http\Services\Driver\ReceiveCash\Controller\ReceiveCashController;
use App\Http\Services\Driver\OrderHistory\Controller\OrderHistoryController;
use App\Http\Services\Driver\Profile\GetProfile\Controller\GetProfileController;
use App\Http\Services\Driver\ChangeConnected\Controller\ChangeConnectedController;
use App\Http\Services\Driver\Profile\EditImageProfile\Controller\EditImageProfileController;
use App\Http\Services\PoilceAndPrivceManagement\ShowPoilceAndPrivceService\Controller\ShowPoilceAndPrivceServiceController;
use App\Http\Services\Driver\GetDriverNotification\Controller\GetDriverNotificationController;
use App\Http\Services\Driver\GetDriverWalletHistory\Controller\GetDriverWalletHistoryController;
use App\Http\Services\Driver\GetPublicDriverAppSettings\Controller\GetPublicDriverAppSettingsController;
use App\Http\Services\User\GetPaymentMethod\Controller\GetPaymentMethodController;
use App\Http\Services\User\GetPublicUserAppSettings\Controller\GetPublicUserAppSettingsController;
use App\Http\Services\User\GetWalletHistory\Controller\GetWalletHistoryController;
use App\Http\Services\User\SendReport\Controller\SendReportController;
use App\Http\Services\User\WalletManagement\AddBalanceByPaymentMethod\Controller\AddBalanceByPaymentMethodController;
use App\Http\Services\User\WalletManagement\ConfirmPhone_AddBalance\Controller\ConfirmPhone_AddBalanceController;

Route::post('login', LoginController::class);

Route::get('/policy',ShowPoilceAndPrivceServiceController::class);


Route::group(['middleware' => ['set-localization']], function () {
    Route::group(['prefix' => 'settings'], function () {
        Route::get('/app-public-settings', GetPublicDriverAppSettingsController::class);
    });
    
});

Route::group(['middleware' => ['auth:driver','set-localization']],function () {

    Route::post('rating',RattingUserController::class);
    Route::post('changeConnected',ChangeConnectedController::class);
    Route::post('accept-order',AcceptOrderController::class);
    Route::get('get/notifications',GetDriverNotificationController::class);
    Route::get('order/history',OrderHistoryController::class);
    Route::post('earning',EarningController::class);
    Route::post('receive-cash',ReceiveCashController::class);
    Route::post('start-ride',RattingUserController::class);
    Route::post('addComplaint', SendReportController::class);
    Route::get('wallet/history',GetDriverWalletHistoryController::class);

    Route::get('get/payment/method',GetPaymentMethodController::class);


    Route::group(['prefix' => 'wallet'], function () {
    Route::Post('/add-balance/by/paymentMethods' , AddBalanceByPaymentMethodController::class);
    Route::Post('/confirm-phoneNumber/add-balance-to-wallet' , ConfirmPhone_AddBalanceController::class);
});

    Route::group(['prefix' => 'profile'], function () {
        Route::get('/', GetProfileController::class);
        Route::post('/photo', EditImageProfileController::class);
        Route::get('/logout',LogoutController::class);
    });

    

});


