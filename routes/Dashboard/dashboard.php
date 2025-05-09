<?php
use Carbon\Carbon;
use App\Models\User;
use App\Models\Driver;
use Sk\Geohash\Geohash;
use App\Events\NewOrder;
use App\Events\TestEvent;
use App\Events\DeleteOrder;
use App\Events\MyRedisEvent;
use App\Events\SearchOnDriver;
use App\Models\FrontendSetting;
use App\Events\DriverPositionChanged;
use App\Events\HoldOrder;
use App\Events\NewMessage;
use App\Http\Core\Const\APIs\MTN_API;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use App\Notifications\UserNotification;
use App\Notifications\PrivateNotification;
use App\Http\Core\Models\NotificationModel;
use App\Http\Controllers\HandymanController;
use App\Http\Core\Classes\CommissionManagement;
use App\Http\Core\Classes\DashboardEventsName;
use App\Http\Core\Classes\Operations\FleetSystemOperationGo;
use App\Http\Core\Classes\RedisManagerData;
use App\Http\Core\Classes\StatisticsEvent;
use App\Http\Core\Classes\WalletManagement;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\Const\Options\Permissions;
use App\Http\Core\Const\Options\Roles;
use App\Http\Core\Response\SendResponse;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\Order\OngoingRedisModel;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\Order\OrderRedisModel;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LargeScaleNotification;
use App\Http\Middleware\AuthSessionMiddleware;
use App\Http\Middleware\LanguageMiddleware;
use App\Http\Repositories\DriverRepositories\DriverReadRepository;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Repositories\SliderRepositories\SliderReadRepository;
use App\Http\Repositories\UserRepositories\UserReadRepository;
use App\Http\Services\Dashboard\Auth\Logout\Controller\LogoutController;
use App\Http\Services\Dashboard\BannersManagement\CreateOrUpdateBanner\Controller\CreateOrUpdateBannerController;
use App\Http\Services\Dashboard\BannersManagement\DestroyBanner\Controller\DestroyBannerController;
use App\Http\Services\Dashboard\BannersManagement\ViewBannersList\Controller\ViewBannersListController;
use App\Http\Services\Dashboard\BannersManagement\Views\CU_BannerPageController;
use App\Http\Services\Dashboard\BannersManagement\Views\IndexBannerController;
use App\Http\Services\Dashboard\BookingManagement\BookingStatusUpdate\Controller\BookingStatusUpdateController;
use App\Http\Services\Dashboard\BookingManagement\Follow\FollowOrderLayoutData\Controller\FollowOrderLayoutDataController;
use App\Http\Services\Dashboard\BookingManagement\Follow\FollowOrderOnMapToView\Controller\FollowOrderOnMapToViewController;
use App\Http\Services\Dashboard\BookingManagement\Follow\FollowOrdersToView\FollowOrdersToViewController;
use App\Notifications\BroadcastUserNotification;
use Illuminate\Notifications\DatabaseNotification;
use App\Http\Services\SharedServices\SwitchLanguageController;
use App\Http\Services\Dashboard\Home\Controller\HomeController;
use App\Http\Services\Dashboard\Front\Controller\FrontController;
use App\Http\Services\Dashboard\BookingManagement\IndexBookingController;
use App\Http\Services\Dashboard\VehicleManagement\IndexVehicleController;
use App\Http\Services\Dashboard\DriverManagement\Views\IndexDriverController;
use App\Http\Services\Dashboard\DriverManagement\Views\CU_DriverPageController;
use App\Http\Services\Dashboard\PublicServices\AjaxLists\Controller\AjaxListsController;
use App\Http\Services\Dashboard\OfficeManagement\ShowOffice\Controller\ShowOfficeController;
use App\Http\Services\Dashboard\BookingManagement\ShowBooking\Controller\ShowBookingController;
use App\Http\Services\Dashboard\BookingManagement\ViewBooking\Controller\ViewBookingController;
use App\Http\Services\Dashboard\BookingManagement\Views\BookingDetailsController;
use App\Http\Services\Dashboard\BookingManagement\Views\Create_PDF_Controller;
use App\Http\Services\Dashboard\BookingManagement\Views\ShowBookingLayoutPage;
use App\Http\Services\Dashboard\CommissionManagement\ToView\fleet\ViewFleetCommissions;
use App\Http\Services\Dashboard\CommissionManagement\ToView\fleet\ViewFleetFreeDriverCommissions;
use App\Http\Services\Dashboard\CommissionManagement\ToView\fleet\ViewFleetOfficeCommissions;
use App\Http\Services\Dashboard\CommissionManagement\ToView\office\ViewOfficeCommission;
use App\Http\Services\Dashboard\CommissionManagement\ToView\ViewUpdateDriverCommission;
use App\Http\Services\Dashboard\CommissionManagement\ToView\ViewUpdateOfficeCommission;
use App\Http\Services\Dashboard\CommissionManagement\UpdateCommissions\Controller\UpdateCommissionsController;
use App\Http\Services\Dashboard\CommissionManagement\UpdateOfficeCommissions\Controller\UpdateOfficeCommissionsController;
use App\Http\Services\Dashboard\CouponManagement\CreateOrUpdateCoupon\Controller\CreateOrUpdateCouponController;
use App\Http\Services\Dashboard\CouponManagement\DestroyCoupon\Controller\DestroyCouponController;
use App\Http\Services\Dashboard\CouponManagement\ToView\CU_CouponPage;
use App\Http\Services\Dashboard\CouponManagement\ToView\IndexCoupon;
use App\Http\Services\Dashboard\CouponManagement\ViewCouponsList\Controller\ViewCouponsListController;
use App\Http\Services\Dashboard\PublicServices\ChangeStatus\Controller\ChangeStatusController;
use App\Http\Services\Dashboard\ServiceManagement\ViewService\Controller\ViewServiceController;
use App\Http\Services\Dashboard\DriverManagement\DestroyDriver\Controller\DestroyDriverController;
use App\Http\Services\Dashboard\ServiceManagement\CheckInTrash\Controller\CheckInTrashController;
use App\Http\Services\Dashboard\OfficeManagement\DestroyOffice\Controller\DestroyOfficeController;
use App\Http\Services\Dashboard\ServiceManagement\ActionService\Controller\ActionServiceController;
use App\Http\Services\Dashboard\ServiceManagement\DeleteService\Controller\DeleteServiceController;
use App\Http\Services\Dashboard\OfficeManagement\ViewOfficeList\Controller\ViewOfficeListController;
use App\Http\Services\Dashboard\DriverManagement\ViewDriversList\Controller\ViewDriversListController;
use App\Http\Services\Dashboard\VehicleManagement\ViewVehicleList\Controller\ViewVehicleListController;
use App\Http\Services\Dashboard\ServiceManagement\BulkActionService\Controller\BulkActionServiceController;
use App\Http\Services\Dashboard\SubServiceManagement\DestroySubService\Controller\DestroySubServiceController;
use App\Http\Services\Dashboard\DriverManagement\CreateOrUpdateDriver\Controller\CreateOrUpdateDriverController;
use App\Http\Services\Dashboard\DriverManagement\ViewOfficeDrivers\Controller\ViewOfficeDriversController;
use App\Http\Services\Dashboard\GetHomeStatistic\Controller\GetHomeStatisticController;
use App\Http\Services\Dashboard\GetOfficeReviews\Controller\GetOfficeReviewsController;
use App\Http\Services\Dashboard\NotificationsManagement\PushNotification\Controller\PushNotificationController;
use App\Http\Services\Dashboard\NotificationsManagement\Views\PushNotificationViewController;
use App\Http\Services\Dashboard\UsersManagement\CreateOrUpdateUser\Controller\CreateOrUpdateUserController;
use App\Http\Services\Dashboard\UsersManagement\DestroyUser\Controller\DestroyUserController;
use App\Http\Services\Dashboard\VehicleManagement\Pages\CreateVehiclePage\Controller\CreateVehiclePageController;
use App\Http\Services\Dashboard\OfficeManagement\CreateOrUpdateOffice\Controller\CreateOrUpdateOfficeController;
use App\Http\Services\Dashboard\OfficeManagement\ToView\CU_OfficePageController;
use App\Http\Services\Dashboard\OfficeManagement\ToView\IndexOfficeController;
use App\Http\Services\Dashboard\OfficeManagement\UpdateOffice\Controller\UpdateOfficeController;
use App\Http\Services\Dashboard\RatingManagement\DriverRattingIndexData\Controller\DriverRattingIndexDataController;
use App\Http\Services\Dashboard\RatingManagement\ToView\IndexDriverRatingController;
use App\Http\Services\Dashboard\RatingManagement\ToView\IndexUserRatingController;
use App\Http\Services\Dashboard\RatingManagement\UserRattingIndexData\Controller\UserRattingIndexDataController;
use App\Http\Services\Dashboard\RedisApi\GetOnlyNewOrdersByStatus\Controller\GetOnlyNewOrdersByStatusController;
use App\Http\Services\Dashboard\RedisApi\GetOrdersByStatus\Controller\GetOrdersByStatusController;
use App\Http\Services\Dashboard\SubServiceManagement\ViewSubServiceList\Controller\ViewSubServiceListController;
use App\Http\Services\Dashboard\VehicleManagement\CreateOrUpdateVehicle\Controller\CreateOrUpdateVehicleController;
use App\Http\Services\Dashboard\ServiceManagement\CreateOrUpdateService\Controller\CreateOrUpdateServiceController;
use App\Http\Services\Dashboard\ServiceManagement\DestroyService\Controller\DestroyServiceController;
use App\Http\Services\Dashboard\ServiceManagement\Views\CU_ServicePageController;
use App\Http\Services\Dashboard\ServiceManagement\Views\IndexServiceController;
use App\Http\Services\Dashboard\Settings\GetOfficeComission\Controller\GetOfficeComissionController;
use App\Http\Services\Dashboard\Settings\SaveTermAndCondition\Controller\SaveTermAndConditionController;
use App\Http\Services\Dashboard\Settings\ToView\ToViewSettingsController;
use App\Http\Services\Dashboard\SubServiceManagement\BulkActionSubService\Controller\BulkActionSubServiceController;
use App\Http\Services\Dashboard\SubServiceManagement\CreateOrUpdateSubService\Controller\CreateOrUpdateSubServiceController;
use App\Http\Services\Dashboard\SubServiceManagement\ToView\CU_SubServicePageController;
use App\Http\Services\Dashboard\SubServiceManagement\ToView\IndexSubServiceController;
use App\Http\Services\Dashboard\Transactions\ToView\IndexPaymentController;
use App\Http\Services\Dashboard\Transactions\ViewPayments\Controller\ViewPaymentsController;
use App\Http\Services\Dashboard\UsersManagement\ToView\CU_UserPageController;
use App\Http\Services\Dashboard\UsersManagement\ToView\IndexUserController;
use App\Http\Services\Dashboard\UsersManagement\ViewUsersList\Controller\ViewUsersListController;
use App\Http\Services\Driver\Earning\Logic\EarningOutput;
use App\Http\Services\Driver\GetDriverNotification\Controller\GetDriverNotificationController;
use App\Http\Services\User\GetPaymentMethod\Controller\GetPaymentMethodController;
use App\Jobs\SearchOnDriverJob;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\Commissions;
use App\Models\FleetOffice;
use App\Models\Office;
use App\Models\Permission;
use App\Models\Rating;
use App\Models\Role;
use App\Models\Service;
use App\Models\Setting;
use App\Models\UserNotification_model;
use App\Models\Vehicle;
use App\Notifications\BroadcastSuperAdminNotification;
use Illuminate\Http\Request;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\DataTables;

use function PHPUnit\Framework\isEmpty;

require __DIR__.'/auth.php';

// $footerSection = FrontendSetting::where('key', 'login-register-setting')->first();
// $sectionData = $footerSection ? json_decode($footerSection->value, true) : null;
// return view('landing-page.login',compact('sectionData'));

// Route::post('/login', [L::class, 'store'])
//                 ->middleware('guest');

// Route::get('/login', function () {
//  //  $user = User::where('id', 1)->first();
//  return view('auth.login');
//  //return view('service.index');
// })->name('login');



Route::get('/register', function () {
    //  $user = User::where('id', 1)->first();
    return view('auth.register');
    //return view('service.index');
   })->name('auth.register');

Route::get('/frontendj', function () {
    //  $user = User::where('id', 1)->first();
    return view('frontend.index');
    //return view('service.index');
   })->name('user.forgot_password');

Route::get('/frontendj', function () { return 'user.forgot_password';
   })->name('user.forgot_password');


   Route::get('/frontendkj', function () { return 'user.register';
   })->name('user.register');

   Route::get('/recover-password', function () {
    //  $user = User::where('id', 1)->first();
    return view('auth.register');
    //return view('service.index');
   })->name('auth.recover-password');

   Route::get('/frontend.index', function () {
    //  $user = User::where('id', 1)->first();
    return view('frontend.index');
    //return view('service.index');
   })->name('frontend.index');

   Route::get('/setting.index', function () {
    //  $user = User::where('id', 1)->first();
    return view('frontend.index');
    //return view('service.index');
   })->name('setting.index');
   
   

   Route::get('/logout', function () {
    //  $user = User::where('id', 1)->first();
    return view('frontend.index');
    //return view('service.index');
   })->name('logout');

   Route::get('lang/{locale}', SwitchLanguageController::class)->name('switch-language');


// Route::group(['prefix' => 'service' ,'middleware' => ['auth:user']], function () {
//     Route::get('/view',ViewServiceController::class)->name('service.view');
//     Route::post('/add', AddServiceController::class)->name('service.create');
//     Route::post('/edite', EditeServiceController::class)->name('service.update');
//     Route::delete('/delete', DeleteServiceController::class)->name('service.delete');
//     Route::get('/show',ShowServiceController::class)->name('service.show');
//     Route::post('/show',ShowServiceController::class)->name('service.bulk-action');
// });

//ChangeStatusController::class
//Route::post('changeStatus/{entity_type}', function(){return "dddddd";})->name('changeStatus');
Route::get('/notification.list', function () {return 'notification.list';})->name('notification.list');
Route::get('/notification.counts', function () {return 'changeStatus';})->name('notification.counts');

//HomeController::class





// Route::group(['middleware' => ['auth:admin']], function()
// {
//     Route::get('/home', HomeController::class)->name('home');
// });

Route::get('changeStatus/{entity_type}',ChangeStatusController::class)->name('changeStatus');

// Route::get('/ajax-list', function () {
//     $services = Service::all();
//     return response()->json( $services);
// })->name('ajax-list');


Route::group([ 'middleware' => ['auth:office,admin' ,'set-language'] ], function() {
Route::get('/home', HomeController::class)->name('home');

  Route::group(['prefix' => 'service'] ,function () {
    Route::get('/', IndexServiceController::class)->name('service.index');
    Route::post('/store', CreateOrUpdateServiceController::class )->name('service.add');
    Route::get('/index_data', ViewServiceController::class)->name('service.index-data');
    Route::get('/create', CU_ServicePageController::class )->name('service.create');
    Route::post('/Service-bulk-action', BulkActionServiceController::class)->name('service.bulk-action');
    Route::get('/action',ActionServiceController::class)->name('service.action');
    Route::delete('destroy', DestroyServiceController::class)->name('service.destroy');
//  Route::delete('/Service/{id}', DeleteServiceController::class)->name('service.destroy');
    Route::post('/check-in-trash', CheckInTrashController::class)->name('check-in-trash');
    Route::post('remove-file', [ ActionServiceController::class , 'removeFile' ] )->name('remove.file');
});

//->middleware(AuthSessionMiddleware::class);

Route::group(['prefix' => 'sub-service'] , function () {
    Route::get('/', IndexSubServiceController::class)->name('sub-service.index');
    Route::get('sub-index-data',ViewSubServiceListController::class )->name('sub-service.index-data');
    Route::post('sub-bulk-action',BulkActionSubServiceController::class)->name('sub-service.bulk-action');
    Route::get('/create', CU_SubServicePageController::class)->name('sub-service.create');
    Route::post('/add', CreateOrUpdateSubServiceController::class)->name('sub-service.add');
    Route::post('sub-service-action',[BulkActionSubServiceController::class ,'action'])->name('sub-service.action');
    Route::delete('destroy', DestroySubServiceController::class)->name('sub-service.destroy');
});


Route::get('test3',function(){
  return view('test4');
});


Route::post('office-save-slot', CreateOrUpdateOfficeController::class )->name('office.store');
//['middleware' => ['permission:provider list']
Route::group(['prefix' => 'office'], function () {
    Route::get('/', IndexOfficeController::class)->name('office.index');
    Route::get('create', CU_OfficePageController::class)->name('office.create-page');
    Route::get('provider/list/{status?}', function(){return 'office';})->name('office.pending');
    Route::post('/update',UpdateOfficeController::class)->name('office.update');
    Route::get('office-index-data', ViewOfficeListController::class)->name('office.index_data');
    Route::get('provider/approve/{id}', function(){return 'office';})->name('office.approve');
    Route::post('dstroy/{id}', DestroyOfficeController::class)->name('office.destroy');
    Route::post('provider-action', function(){return 'office';})->name('office.action');
    Route::post('provider-bulk-action', function(){return 'office';})->name('office.bulk-action');
    Route::get('office', ShowOfficeController::class)->name('office.show');
    Route::get('view-drivers',ViewOfficeDriversController::class )->name('driver.byOffice');
    Route::get('review', GetOfficeReviewsController::class)->name('office.review');
});

Route::group(['prefix' => 'vehicle'] , function () {
    Route::get('/', IndexVehicleController::class)->name('vehicle.index');
    Route::get('vehicle-index-data', ViewVehicleListController::class )->name('vehicle.index-data');
    Route::post('vehicle-bulk-action',BulkActionSubServiceController::class)->name('vehicle.bulk-action');
    Route::get('/create_page', CreateVehiclePageController::class )->name('vehicle.create');
    Route::post('/add', CreateOrUpdateVehicleController::class)->name('vehicle.store');
    Route::post('vehicle-action',[BulkActionSubServiceController::class ,'action'])->name('vehicle.action');
    Route::delete('destroy/{id}', DestroySubServiceController::class)->name('vehicle.destroy');
});

// Route::get('/drivers/by-office/{officeId}', [VehicleController::class, 'getDriversByOffice'])->name('drivers.byOffice');

Route::get('/drivers/by-office/{officeId}', function ($officeId) {
        $drivers = Driver::where('officeId', $officeId)->get();
        return response()->json($drivers);
})->name('drivers.byOffice');


Route::group(['prefix' => 'booking'] , function () {
    Route::get('/', IndexBookingController::class)->name('booking.index');
    Route::get('booking-index-data',ViewBookingController::class )->name('booking.index_data');
//  Route::get('details/{id}',ShowBookingController::class)->name('booking.show');
    Route::post('sub-bulk-action',BulkActionSubServiceController::class)->name('booking.bulk-action');
    Route::get('/create',[ CreateOrUpdateSubServiceController::class ,'to_create'])->name('booking.create');
    Route::post('sub-service-action',[BulkActionSubServiceController::class ,'action'])->name('booking.action');
    Route::delete('destroy/{id}', DestroySubServiceController::class)->name('booking.destroy');
    Route::post('/booking-layout-page/{id}',ShowBookingLayoutPage::class)->name('booking_layout_page');
    Route::get('/booking-details/{id}', BookingDetailsController::class)->name('booking.show');

    
    Route::get('/ongoing-index',[FollowOrdersToViewController::class ,'ongoing_index'] )->name('follow.ongoing');
    Route::get('/follow-layout', FollowOrdersToViewController::class)->name('follow.layout');

    Route::get('/order-layout-data', FollowOrderLayoutDataController::class)->name('order-layout-data');

    Route::get('/order-on-map', FollowOrderOnMapToViewController::class)->name('order.follow.map');

    
});

Route::get('/invoice_pdf/{id}', Create_PDF_Controller::class )->name('invoice_pdf');
Route::post('booking-status-update', BookingStatusUpdateController::class,'updateStatus')->name('bookingStatus.update');


Route::group(['prefix' => 'driver'] , function() {
  Route::get('/',IndexDriverController::class)->name('driver.index');
  Route::get('driver-index-data', ViewDriversListController::class )->name('driver.index-data');
  Route::get('/create', CU_DriverPageController::class )->name('driver.create');
  Route::post('/add', CreateOrUpdateDriverController::class)->name('driver.store');
  Route::delete('destroy/{id}', DestroyDriverController::class)->name('driver.destroy');
  Route::post('driver-bulk-action',BulkActionSubServiceController::class)->name('driver.bulk-action');
  Route::post('vehicle-bulk-action',BulkActionSubServiceController::class)->name('driver.getchangepassword');
  Route::post('vehicle-action',[BulkActionSubServiceController::class ,'action'])->name('driver.action');

});

Route::group(['prefix' => 'user'] , function () {
  Route::get('/',IndexUserController::class)->name('user.index');
  Route::get('driver-index-data', ViewUsersListController::class )->name('user.index-data');
  Route::get('/create', CU_UserPageController::class )->name('user.create');
  Route::post('/add', CreateOrUpdateUserController::class)->name('user.store');
  Route::delete('destroy', DestroyUserController::class)->name('user.destroy');
  Route::post('user-bulk-action',BulkActionSubServiceController::class)->name('user.bulk-action');
  Route::post('vehicle-bulk-action',BulkActionSubServiceController::class)->name('user.getchangepassword');
  Route::post('vehicle-bulk-h',BulkActionSubServiceController::class)->name('user.userResetPassword');
  Route::post('user.changepasswordbulk-h',BulkActionSubServiceController::class)->name('user.changepassword');

  Route::post('vehicle-action',[BulkActionSubServiceController::class ,'action'])->name('user.action');
});


Route::group(['prefix' => 'banner'] , function () {

  Route::get('/', IndexBannerController::class)->name('banner.index');
  Route::get('driver-index-data', ViewBannersListController::class )->name('banner.index-data');
  Route::get('/create', CU_BannerPageController::class )->name('banner.create');
  Route::post('/add', CreateOrUpdateBannerController::class)->name('banner.store');
  Route::delete('destroy', DestroyBannerController::class)->name('banner.destroy');
  Route::post('banner-bulk-action',BulkActionSubServiceController::class)->name('banner.bulk-action');
  Route::post('banner-action',[BulkActionSubServiceController::class ,'action'])->name('banner.action');
  });


  
Route::group(['prefix' => 'ratings'] , function () {

  Route::group(['prefix' => 'driver'] , function () { 
    Route::get('/', IndexDriverRatingController::class)->name('ratings.driver.index');
    Route::get('index-data', DriverRattingIndexDataController::class )->name('ratings.driver.index-data');

   });
  
  Route::group(['prefix' => 'user'] , function () { 
    Route::get('/', IndexUserRatingController::class)->name('ratings.user.index');
    Route::get('index-data', UserRattingIndexDataController::class )->name('ratings.user.index-data');

  });

  });


  Route::group(['prefix' => 'coupon'] , function(){

    Route::get('/', IndexCoupon::class)->name('coupon.index');
    Route::get('coupon-index-data', ViewCouponsListController::class )->name('coupon.index-data');
    Route::get('/create', CU_CouponPage::class )->name('coupon.create');
    Route::post('/add', CreateOrUpdateCouponController::class)->name('coupon.store');
    Route::delete('destroy', DestroyCouponController::class)->name('coupon.destroy');
    Route::post('Coupon-bulk-action',BulkActionSubServiceController::class)->name('coupon.bulk-action');
    Route::post('Coupon-action',[BulkActionSubServiceController::class ,'action'])->name('coupon.action');
    });

    // Route::group(['middleware' => ['permission:terms condition']], function () {
  Route::get('pages/term-condition',[ SaveTermAndConditionController::class, 'getView'])->name('term-condition');
  Route::post('term-condition-save', SaveTermAndConditionController::class )->name('term-condition-save');
  // });
  Route::get('/push-notification/view', PushNotificationViewController::class)->name('pushNotification.index');
  Route::post('/push-notification', PushNotificationController::class)->name('sendPushNotification');


  Route::post('dashboard-logout' ,LogoutController::class)->name('logout');

  Route::get('notifications/get',function(){

    $driver = Driver::find(2);
    return $driver->Notifications();
  } )->name('notifications-get');

  // Route::group(['prefix' => 'notification'] , function () {

  // Route::get('/push-notification/view', PushNotificationController::class)->name('sendPushNotification');
  // });

  //   Route::group(['namespace' => '', 'middleware' => ['permission:permission list']], function () {
  //   Route::resource('permission',PermissionController::class);
  //   Route::get('permission/add/{type}',[PermissionController::class,'addPermission'])->name('permission.add');
  //   Route::post('permission/save',[PermissionController::class,'savePermission'])->name('permission.save');
  // });

  Route::group(['prefix' => 'payments'] , function () {
    Route::get('/', IndexPaymentController::class)->name('payment.index');
    Route::get('/payments-index-data', ViewPaymentsController::class)->name('payment.index-data');
    });
  
  Route::group(['prefix' => 'commission'] , function () {
    // index
    Route::get('/fleet', ViewFleetCommissions::class)->name('commissions.fleet');
    Route::get('/office', ViewOfficeCommission::class)->name('commissions.office');

    // update views
    Route::get('/free-driver', ViewFleetFreeDriverCommissions::class)->name('commissions.free-driver');
    Route::get('/fleet-office', ViewFleetOfficeCommissions::class)->name('commissions.fleet.office');

    Route::get('/driver-car', ViewFleetFreeDriverCommissions::class)->name('commissions.driver.car');
    Route::get('/office-car', ViewFleetOfficeCommissions::class)->name('commissions.office.car');

    // update logic
    Route::post('/update-fleet-commission', UpdateCommissionsController::class)->name('commissions.fleet.update');
    Route::post('/update-office-commission', UpdateOfficeCommissionsController::class)->name('commissions.office.update');

    // Route::get('/office-owner', ViewUpdateDriverCommission::class)->name('commissions.driver');

    });


Route::get('setting/{page?}',ToViewSettingsController::class)->name('setting.index');
Route::post('/layout-page',[ ToViewSettingsController::class, 'layoutPage'])->name('layout_page');
Route::post('general-setting-save',[ ToViewSettingsController::class, 'generalSetting'])->name('generalsetting');
Route::post('theme-setup-save',[ ToViewSettingsController::class, 'themeSetup'])->name('themesetup');


Route::get('/ajax-list/{list_type}',  AjaxListsController::class)->name('ajax-list');
Route::get('comission',GetOfficeComissionController::class)->name('setting.comission');
Route::get('/ajax-l/{list_type}',  AjaxListsController::class);

Route::get('view', function(){

  $pageTitle = 'user view';
  $customerdata = User::find(1);
  $orders = $customerdata->Booking;

  return view('customer.changepassword' ,compact('customerdata','orders' ,'pageTitle'));
});

Route::get('wallet/add',  function(){

  return view('wallet.add-balance');

})->name('add.wallet');


Route::post('add-balance',  function(){

  return response()->json(['success'=>true ,'message'=>'bbbbbbbb','walletBalance'=>8000]);

})->name('add-balance');



Route::get('uuu',function(){


  $orders = Booking::all();
  $pageTitle = 'Wallet Hestory';
  $customerdata = User::first();
  return view('wallet.user',compact('orders','pageTitle','customerdata'));
})->name('uuu');


Route::get('get/notifications2',GetDriverNotificationController::class)->name('get-notifications');


Route::get('/tra',function(Request $request){
  $user = User::where('phone', $request->phoneNumber)
  ->where('type', $request->userType)
  ->first();

if (!$user) {
return response()->json([
"draw" => intval($request->draw),
"recordsTotal" => 0,
"recordsFiltered" => 0,
"data" => []
]);
}


$query = Booking::all();

return DataTables::of($query)
->addColumn('action', function ($row) {
return '<button class="btn btn-sm btn-danger delete-transaction" data-id="'.$row->id.'">
          <i class="fas fa-trash"></i>
      </button>';
})
->make(true);
})->name('wallet.getTransactions');


Route::get('get-user-info',  function(){

  $data['success'] = true;
  $user= User::find(1);
  return response()->json([
    'success' => true,
    'user' => [
        'name' => $user->firstName . ' ' . $user->lastName,
        'phone' => $user->phoneNumber,
        'address' => $user->address,
        'wallet_balance' => number_format($user->wallet_balance, 2) 
    ]
]);
})->name('wallet.getUserInfo');

});


Route::get('login-office',function(){
  // $footerSection = FrontendSetting::where('key', 'login-register-setting')->first();
  // $sectionData = $footerSection ? json_decode($footerSection->value, true) : null;
  $sectionData['description'] = 'Welcome To ';
  $sectionData['title'] = 'Welcome To Our Fleet';
  $sectionData['login_register'] = 1;


  return view('landing-page.login',compact('sectionData'));
});




Route::get('live-drivers-locations',function(){
  $keys = Redis::keys('driver_location:*'); 
    $locations = [];

    foreach ($keys as $key) {
        $driverId = str_replace('driver_location:', '', $key);
        $location = Redis::hgetall($key);

        $locations[] = [
            'driver_id' => $driverId,
            'longitude' => $location['longitude'],
            'latitude' => $location['latitude'],
            'name' => 'Bassam',
            'phoneNumber'=>'0933817393',
            'carNumber'=>'7885200',
            'carBrand'=>'kia'
        ];
    }

    return $locations;
})->name('live-drivers-locations');

// ------------ GET -----------//
Route::get('home/statistics',GetHomeStatisticController::class)->name('home.statistics');
Route::get('get/orders-by-status',GetOrdersByStatusController::class)->name('orders-by-status');
Route::get('get/only-new-orders-by-status',GetOnlyNewOrdersByStatusController::class)->name('new-orders-by-status');
Route::get('payment/method',GetPaymentMethodController::class);


Route::get('/bassam', function(){
  Http::get('https://services.mtnsyr.com:7443/General/MTNSERVICES/ConcatenatedSender.aspx?User=uom424&Pass=mar141214&From=FleetApp&Gsm=0940606534&Msg=5555IsyourFleetAppverificationcode&Lang=1');


  return 'otp sending..';
  event(new HoldOrder(1));


  return 'event fire';

  $order = Booking::where('id', 9)->first();
  // for($i = 1 ;$i<=10;$i++){ 
  $order->id = 45;
  $order->status = OrderStatus::$Pending;
  // $order->user = User::first();
  // OrderRedisModel::storeWithPagenationService($order);
  //}
  // OrderRedisModel::delete(38, OrderStatus::$Pending);

  // OrderRedisModel::updateStatus($order, OrderStatus::$Pending , OrderStatus::$OnGoing);


  // OrderRedisModel::storeCancelOrderId(28);
  return   OrderRedisModel::getCancelOrderIds();
  return response()->json( OrderRedisModel::getByStatusPaginated(OrderStatus::$OnGoing ,0));

  Booking::create(        $data = [
    'startAddress'          =>"برامكة - سانا",
    'endAddress'            =>"المزة - فيلات غربية",
    'time'                  =>"10:05",
    'startLatitude'         =>34.2999,
    'startLongitude'        =>34.5555,
    'endLatitude'           =>45.555,
    'endLongitude'          =>45.222,
    'distance'              =>5,
    'couponCode'=>565,
    'subServiceId'=>1,
    'userId'=>3,
    'totalAmount'=>255,
    'amount'=>5633,
]);


  // $new_count = FleetSystemOperationGo::add_orders_to_pinding_rides(1);

  // return RedisManagerData::get_system_daily_pending_rides();

  RedisManagerData::set_system_daily_completed_rides(77);
  Redis::hmset("driver_location:7", [
    'longitude' => 40.15522,
    'latitude' =>  38.5522
]);

Redis::hmset("driver_location:8", [
  'longitude' => 39.15522,
  'latitude' =>  33.5522,
]);

Redis::hmset("driver_location:9", [
  'longitude' => 36.15522,
  'latitude' =>  34.5522
]);

  Redis::hmset("driver_location:4", [
    'longitude' => 37.15522,
    'latitude' =>  33.5522
]);

  $keys = Redis::keys('driver_location:*');
    $locations = [];

    foreach ($keys as $key) {
        $driverId = str_replace('driver_location:', '', $key);
        $location = Redis::hgetall($key);

        $locations[] = [
            'driver_id' => $driverId,
            'longitude' => $location['longitude'],
            'latitude' => $location['latitude']
        ];
    }

    return $locations;

  $driver = Driver::find(2);

//     $repository = new RepositoryCaller();

//     $report = $repository->BookingRepository()->readRepository()->getEarning(
//     [],
//     [
//         'id', 
//         'amount', 
//         'distance', 
//         'driverCommissionValue      as rideCommission', 
//         'driverCommissionPercentage as CommissionPercentage',
//         'fleetCommissionValue       as fleetCommission',
//         'officeCommissionValue      as officeCommoission',
//         // '(fleetCommissionValue + officeCommissionValue) AS officeCommission'
//       ],
//     conditions: [
//         'driverId' => $driver->id,
//         'status' => OrderStatus::$Completed
//     ]
// );


// $response =[
//   'orders'            => $report['records'],  
//   'totalOrders'       => $report['summary']->total_orders ?? 0,
//   'totalKm'           => $report['summary']->total_km ?? 0,
//   'totalEarning'      => $report['summary']->total_earning ?? 0,
//   'totalOfficeDues'         => $report['summary']->total_office_commission ?? 0,
// ];


// return response()->json(['statusCode'=>200 , 'data' =>$response]);
// return SendResponse::sendSuccessResponse($result); // send response..


  if($driver->has_sub_service(
    "2"
  )){
    return 'yeess';
 }
 return 'nooo';

  $data = [
    'startLatitude'         =>33,
    'startLongitude'        =>22,
    'orderId'=>2,
    'radius'=> 1
];
  SearchOnDriverJob::dispatch($data)->onQueue('jobs');


  // Notification::send(Admin::first() , new BroadcastSuperAdminNotification(
  //   new NotificationModel(
  //           'فردو السجاد الأحمر',
  //           'حي الله ذكوور الملك نورت فليت يا خال',
  //           'تسديد جديد',
  //           'تم تسديد مبلغ 17000 مستحقات عمولة فلييت من قبل السائق مصعب السيوفي',
  //           '\storage\images\system\user_no_photo4a305405-ad0b-473a-a40c-456397a18b96.png',
  //       )));

        Notification::send(Admin::first() , new BroadcastSuperAdminNotification(
          new NotificationModel(
                  'فردو السجاد الأحمر',
                  'حي الله ذكوور الملك نورت فليت يا خال',
                  'تسديد جديد',
                  'تم تسديد مبلغ 17000 مستحقات عمولة فلييت من قبل السائق مصعب السيوفي',
                  '\storage\images\system\user_no_photo4a305405-ad0b-473a-a40c-456397a18b96.png',
              )));

return 'done';
  return Driver::find(2)->notifications;
  $subServiceId = 3;
  $driverIds = [2,1];
  
  
  // $services = Driver::find(2)->subServices->where('subServiceId', $subServiceId);
  // return response()->json($services );
// $drivers = Driver::select('drivers.id', 'drivers.isConected')
//     ->whereIn('drivers.id', $driverIds)
//     ->where('drivers.isConected', true)
//     ->whereHas('subServices', function ($query) use ($subServiceId) {
//         $query->where('subServiceId', $subServiceId); 
//     })
//     ->get();


  $driver = Driver::find(2);
  if($driver->has_sub_service(45)){
    return 'emm';
  }

  return 'not';
  return empty($services);
  return response()->json($services );


  app()->setLocale('ar');
  $select = select_by_language([
    'name',
    'image',
    'status',
    'description',
    'openPrice',
    'kmPrice',
    'minutePrice',
    'serviceId',
] , [
    'image',
    'status',
    'openPrice',
    'kmPrice',
    'minutePrice',
    'serviceId',
    'name_en as name',
    'description_en as description'
]);
$repo = new RepositoryCaller();

$serviceReadRepository = $repo->SubServiceRepository()->readRepository();
$services = $serviceReadRepository->getByConditions(
    ['status'=>true] ,
    $select
);

  // $repo = new RepositoryCaller();
  // $repo = $repo->ServiceRepository()->readRepository()->getByConditions(
  //   ['status'=>true] ,
  //   $select);
  return response()->json($services);


  $notificationModel = new NotificationModel(
    'aaaaaaaaaaaa',
   'aaaaaaaaaaaaaa',
   'eeeeeeee',
   'eeeeeeeeeee',
   'ddd',
 );


//  return $notificationModel->get_body_by_locale_language();
 $repo = new UserReadRepository();
 $repo->notifyUser( 1,  $notificationModel  );
  return User::find(1)->notifications;
  //UserNotification_model::select($selected)->find($id)->notifications()
//->notifications()->paginate($paginate)
 return response()->json(User::select(['*'])->find(1)->notifications()->paginate(10));

  return response()->json(Service::SelectWithTranslate()->get());

  return $msg = __('messages.msg_fail_to_delete',['item' => __('messages.coupon')] );



  return response()->json(Vehicle::first()->subservices);
  return 'done';
    return response()->json(Driver::find(11)->Notifications());

  return $user->notification;
  //  return response()->json($user->Notifications);
  $notificationModel = new NotificationModel(
     'testd4s5d45s',
    'test',
    'e',
    'eee',
    'ddd',
  );
  $repo = new UserReadRepository();
  $repo->notifyUser( 1,  $notificationModel  );
   return 'done';

  $driver = Driver::find(1);
  WalletManagement::transfer($user , $driver , 200 , 'تم تحويل مبلغ من حساب المستخدم');

  return 'done';
  DashboardEventsName::New_Order_Ongoing->sen_event_to_office_follow_orders(9);
  return 'done';
  return 'done';
  $office = Office::find(1);
  $office->assignRole('office');
  app()->setLocale('en');

  return $msg = __('messages.msg_fail_to_delete',['item' => __('messages.coupon')] );


  foreach ( Permissions::cases() as $permission ) {
    Permission::updateOrCreate(['name'=>$permission->value ,'guard_name'=>'admin']);
}

  $role = Role::findByName(Roles::Super_Admin->value , 'admin');

  $role->givePermissionTo(Permission::all()->pluck('name')->toArray());

  $admin = Admin::find(1);

  $admin->assignRole(Roles::Super_Admin->value);
  // $permission = Permission::create([
  // 'name'=>'update user'
  // ]);

  // $role = Role::create(['name'=>'super-admin']);
  // $role = Role::findByName('super-admin');

  // $role->givePermissionTo(['update user']);
  // event(new NewMessage('public-notification', ' رسالة جديدة من Laravel!'));

  // RedisManagerData::set_system_daily_pending_rides(1);
  // FleetSystemOperationGo::
  // 0990173958

  

  return 'done';
  $repository = new RepositoryCaller();
  $statistic = $repository->FleetStatisticRepository()->readRepository()->getFirstByConditions([]);
  $new_pending_amount = $statistic->pending_amount + (1000 * 0.1);
  $updated = $repository->FleetStatisticRepository()->updateRepository()
  ->update(['id'=>$statistic->id],['pending_amount'=>$new_pending_amount]);
  
  if($updated > 0){
    StatisticsEvent::Pending_Card
    ->send_event_to_admin($new_pending_amount);
  }

  // $bookingdata = Booking::find(1);
  // $driver = $bookingdata->office;
  
  return 'done';
  
  // StatisticsEvent::New_Order_Ongoing->send_event_to_admin(1);
  return 'done';
  $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
  $datetime = $sitesetup ? json_decode($sitesetup->value) : null;

  $bookingdata = Booking::find(1);
  return view('booking.view',compact('bookingdata','datetime'));

  $driver = Driver::find(1); 
  // $office = $driver->office;
  return response()->json($driver->office);  //$driver->office;


  $auth_user= authSession();
  return view('driver.create',compact('driver','auth_user'))->render();

  
 
  return $office->officeName; 

  $rm->store_driver_area( 500 , (38.5555),(31.5555));

  return $rm->get_driver_area(500);
  // NewOrder::
    $driverssss = ["1","2","3","5","3"," ","15"];
    $drivers_int = array_map('intval', $driverssss);
    $driverssss = array_unique($driverssss);

    return $drivers = Driver::select('id')->whereIn('id', $driverssss)->get();

    $driverssss = array_unique( $driverssss );
    $drivers_int = array_map( 'intval' , $driverssss);
   // $dd = array_unique( $drivers_int );
    
  return print_r( $drivers_int );
  
//return $drivers = Driver::select('id')->whereIn('id', [1,2,3])->where('isConected',true)->get();
    //broadcast(new SearchOnDriver($data =[], 77));
return 'done';

    // broadcast(new DeleteOrder(1,1));
  $user= User::find(11);
  //PrivateNotification
  // Notification::send($user , new PrivateNotification(
  //   new NotificationModel(
  //           'فردو السجاد الأحمر',
  //           'حي الله ذكوور الملك نورت فليت يا خال',
  //           'http://gggggggddd',
  //       )));
  
// Notification::send($user , new BroadcastUserNotification(new NotificationModel(
//     'صباح الخير',
//     'bbbbbbbbbb',
//     'http://ggggggg'
// )));
  
//    $user->notify(new BroadcastUserNotification([
//     'id' => (string) \Illuminate\Support\Str::uuid(),
// ]));
    return 'done';
    //return  Carbon::now()->format('Y-m-d H:i:s');
    // broadcast(new DriverPositionChanged($data['driverId'] , $data['latitude'] ,  $data['longitude']));

    //33.488213, 36.321101
    $g = new Geohash();

    $geoHash = $g->encode(33.490511, 36.311380, 6);
    $areaKey = $geoHash ;

    $arr = [
        'dri' =>[2,1,3],
        'ff'=>99
    ];

    Redis::set('order.1:notAcceptedByDriver', json_encode(
        $arr
    ));

    $r = json_decode(Redis::get('order.1:notAcceptedByDriver'), true);
    return  $r;

    // $radius = ($order_info['radius'] + 0.2 < 2) ? $order_info['radius'] + 0.2 : $order_info['radius'];



    //Redis::Set('aa',3);
    // Redis::del('aa');

    Redis::set('driver.1:eara',$areaKey);
    Redis::del('driver.1:eara');

    $areaKey = Redis::get('driver.1:eara');


    Redis::geoadd($areaKey, 33.490511, 36.311380, 15);
    Redis::geoadd($areaKey, 33.490511, 36.311380, 16);
    Redis::geoadd($areaKey, 33.490511, 36.311380, 17);

    Redis::zrem($areaKey,15 );

    return Redis::georadius($areaKey, 33.490511, 36.311380 , 3 , 'km');
    Redis::geoadd($areaKey, 33.490511, 36.311380, 35);
    Redis::geoadd($areaKey, 33.790511, 36.811380, 10);



   //  $s = new SearchOnDriver();

    //return Redis::georadius($areaKey, 33.490511, 36.311380 , 100 , 'km');

    //broadcast(new \App\Events\DeleteOrder(5 , 1 ));

    $g = new Geohash();
    $hash = $g->encode(33.490511, 36.311380, 6);
    $neighbors = $g->getNeighbors($hash);
    echo "Hash: $hash\n";
    echo json_encode($neighbors).'\n' ;

    $hash = $g->encode(33.488213 , 36.321101 , 6);
    echo "Hash: $hash\n";

// foreach( json_decode($Neigh)  as  $ne){
//         echo $ne;
//     }

    return '';
});



Route::get('/notification', function(){
    // broadcast(new DeleteOrder(1,1));
    $user = Driver::where(['id'=> 1])->get();
    //PrivateNotification
  // Notification::send($user , new PrivateNotification(
  //   new NotificationModel(
  //           'فردو السجاد الأحمر',
  //           'حي الله ذكوور الملك نورت فليت يا خال',
  //           'http://gggggggddd'
  //         )
  // ));





// Notification::send($user , new BroadcastUserNotification(new NotificationModel(
//     'صباح الخير',
//     'bbbbbbbbbb',
//     'http://ggggggg'
// )));
  
//    $user->notify(new BroadcastUserNotification([
//     'id' => (string) \Illuminate\Support\Str::uuid(),
// ]));
    return 'new notification send';
 
});

Route::get('hi',function(){

  return '<h1>مرحبا ابو يامن  <h1>';
});

Route::get('push/driver/notification', function(){
    // broadcast(new DeleteOrder(1,1));
    $user = Driver::where(['id'=> 2])->get();
    //PrivateNotification
    // Notification::sendNow($user , new PrivateNotification(
    //   new NotificationModel(
    //           'Hello Driver!',
    //           'hello from fleet app ',
    //           '/storage/services/2.png'
    //         )
    // ));

  return 'notification send to driver';
});


Route::get('push/user/notification', function(){
  $notificationModel = new NotificationModel(
    'testd4s5d45s',
   'test',
   'e',
   'eee',
   'ddd',
 );

 $repo = new UserReadRepository();
 $repo->notifyUser( 3,  $notificationModel  );

  return 'done';
});
