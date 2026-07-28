<?php
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DriverJobApplicationController;
use App\Http\Controllers\OfficeRequestController;
use App\Http\Controllers\SubServicesPricingController;
use App\Http\Core\Algorithms\SearchOnDriverAlgorithm;
use App\Http\Core\Classes\Integration\Stripe\StripeService;
use App\Http\Core\GoogleService;
use App\Http\Core\WhatsappMessageService;
use App\Http\Services\CreateStripePaymentIntent\Controller\CreateStripePaymentIntentController;
use App\Http\Services\Dashboard\BookingManagement\AssignToDriver\Controller\AssignToDriverController;
use App\Http\Services\Dashboard\CommissionManagement\ToView\CommissionsLayout;
use App\Http\Services\Dashboard\DriverJobApplicationsMangement\DriverJobApplicationList\Controller\DriverJobApplicationListController;
use App\Http\Services\Dashboard\DriverJobApplicationsMangement\ToView\IndexDriverJobApplicationController;
use App\Http\Services\Dashboard\OfficeManagement\AddBalanceToWallet\Controller\AddBalanceToWalletController;
use App\Models\Country;
use App\Models\DriverJobApplication;
use App\Models\OfficeSubServicePrice;
use App\Models\SubService;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Admin;
use App\Models\Driver;
use App\Models\Office;
use App\Models\Rating;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Sk\Geohash\Geohash;
use App\Events\NewOrder;
use App\Models\Employee;
use App\Events\HoldOrder;
use App\Events\TestEvent;
use App\Events\NewMessage;
use App\Models\Permission;
use App\Models\UserReport;
use App\Events\DeleteOrder;
use App\Models\Commissions;
use App\Models\FleetOffice;
use App\Events\MyRedisEvent;
use Illuminate\Http\Request;
use App\Events\SearchOnDriver;
use PHPUnit\Framework\isEmpty;
use App\Jobs\SearchOnDriverJob;
use App\Models\FrontendSetting;
use App\Models\ParentPermission;
use Yajra\DataTables\DataTables;
use App\Models\WalletTransaction;
use Illuminate\Redis\RedisManager;
use App\Models\PublicUserAppSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Events\DriverPositionChanged;
use App\Http\Core\Const\APIs\MTN_API;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use App\Http\Core\Const\Options\Roles;
use App\Models\UserNotification_model;
use App\Notifications\UserNotification;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\testController;
use App\Http\Core\Response\SendResponse;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\TicketController;
use App\Http\Core\Classes\StatisticsEvent;
use App\Jobs\ScheduledOrders\ReminderUser;
use App\Notifications\PrivateNotification;
use Spatie\Permission\PermissionRegistrar;
use App\Http\Core\Classes\RedisManagerData;
use App\Http\Core\Classes\WalletManagement;
use App\Http\Core\Models\NotificationModel;
use App\Http\Middleware\LanguageMiddleware;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Controllers\HandymanController;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\Const\Options\Permissions;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LargeScaleNotification;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PermissionController;
use App\Http\Core\Classes\DashboardEventsName;
use App\Http\Middleware\AuthSessionMiddleware;
use App\Http\Core\Classes\CommissionManagement;
use App\Notifications\BroadcastUserNotification;
use App\Http\Controllers\FleetDashboardController;
use App\Http\Controllers\OfficeCustomerController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Core\Const\Selected\SelectByLanguage;
use Illuminate\Notifications\DatabaseNotification;
use App\Http\Controllers\WalletTransactionController;
use App\Notifications\BroadcastSuperAdminNotification;
use App\Http\Services\Driver\Earning\Logic\EarningOutput;
use App\Http\Core\Classes\Operations\FleetSystemOperationGo;
use App\Http\Repositories\UserRepositories\UserReadRepository;
use App\Http\Services\SharedServices\SwitchLanguageController;
use App\Http\Services\Dashboard\Home\Controller\HomeController;
use App\Http\Services\Dashboard\Front\Controller\FrontController;
use App\Http\Repositories\DriverRepositories\DriverReadRepository;
use App\Http\Repositories\SliderRepositories\SliderReadRepository;
use app\Http\Repositories\OfficeRepositories\OfficeCreateRepository;
use App\Http\Services\Dashboard\CouponManagement\ToView\IndexCoupon;
use App\Http\Services\Dashboard\CouponManagement\ToView\CU_CouponPage;
use App\Http\Services\Dashboard\Auth\Logout\Controller\LogoutController;
use App\Http\Services\Dashboard\BookingManagement\IndexOrdersController;
use App\Http\Services\Dashboard\BookingManagement\officeOrderController;
use App\Http\Services\Dashboard\HelpDeskManagement\ToView\IndexHelpDesk;
use App\Http\Services\Dashboard\BookingManagement\IndexBookingController;
use App\Http\Services\Dashboard\Settings\ToView\ToViewSettingsController;
use App\Http\Services\Dashboard\VehicleManagement\IndexVehicleController;
use App\Http\Services\Dashboard\AddBalance\Controller\AddBalanceController;
use App\Http\Services\Dashboard\Transactions\ToView\IndexPaymentController;
use App\Http\Services\Dashboard\UsersManagement\ToView\IndexUserController;
use App\Http\Services\Dashboard\BrandManagement\Views\IndexVBrandController;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\Order\OrderRedisModel;
use App\Http\Services\Dashboard\DriverManagement\Views\IndexDriverController;
use App\Http\Services\Dashboard\UsersManagement\ToView\CU_UserPageController;
use App\Http\Services\Dashboard\BannersManagement\Views\IndexBannerController;
use App\Http\Services\Dashboard\BookingManagement\Views\Create_PDF_Controller;
use App\Http\Services\Dashboard\BookingManagement\Views\ShowBookingLayoutPage;
use App\Http\Services\Dashboard\BrandManagement\Views\CU_VBrandPageController;
use App\Http\Services\Dashboard\OfficeManagement\ToView\IndexOfficeController;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\Driver\DriverRedisModel;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\Order\OngoingRedisModel;
use App\Http\Services\Dashboard\DriverManagement\Views\CU_DriverPageController;
use App\Http\Services\Dashboard\RoleAndPermission\ToView\AddRoleViewController;
use App\Http\Services\Dashboard\ServiceManagement\Views\IndexServiceController;
use App\Http\Services\Dashboard\BannersManagement\Views\CU_BannerPageController;
use App\Http\Services\Dashboard\OfficeManagement\ToView\CU_OfficePageController;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\FleetWallet\BalanceStatus;
use App\Http\Services\Dashboard\BookingManagement\Views\BookingDetailsController;
use App\Http\Services\Dashboard\ServiceManagement\Views\CU_ServicePageController;
use App\Http\Services\Dashboard\WalletHistory\Controller\WalletHistoryController;
use App\Http\Services\Dashboard\EmployeeManagement\ToView\IndexEmployeeController;
use App\Http\Services\Dashboard\RatingManagement\ToView\IndexUserRatingController;
use App\Http\Services\User\GetPaymentMethod\Controller\GetPaymentMethodController;
use App\Http\Services\Dashboard\EmployeeManagement\ToView\CU_EmployeePageController;
use App\Http\Services\Dashboard\RatingManagement\ToView\IndexDriverRatingController;
use App\Http\Services\Dashboard\SubServiceManagement\ToView\IndexSubServiceController;
use App\Http\Services\User\UserHelpSuggestion\Controller\UserHelpSuggestionController;
use App\Http\Services\Dashboard\CommissionManagement\ToView\ViewUpdateDriverCommission;
use App\Http\Services\Dashboard\CommissionManagement\ToView\ViewUpdateOfficeCommission;
use App\Http\Services\Dashboard\GetHomeStatistic\Controller\GetHomeStatisticController;
use App\Http\Services\Dashboard\GetOfficeReviews\Controller\GetOfficeReviewsController;
use App\Http\Services\Dashboard\PublicServices\AjaxLists\Controller\AjaxListsController;
use App\Http\Services\Dashboard\SubServiceManagement\ToView\CU_SubServicePageController;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\FleetWallet\FleetWalletRedisModel;
use App\Http\Services\Api\Send_SMS_Message_Api\Controller\Send_SMS_Message_ApiController;
use App\Http\Services\Dashboard\OfficeManagement\ShowOffice\Controller\ShowOfficeController;
use App\Http\Services\Dashboard\Transactions\ViewPayments\Controller\ViewPaymentsController;
use App\Http\Services\Dashboard\NotificationsManagement\Views\PushNotificationViewController;
use App\Http\Services\Dashboard\RoleAndPermission\AddNewRole\Controller\AddNewRoleController;
use App\Http\Services\Dashboard\UsersManagement\DestroyUser\Controller\DestroyUserController;
use App\Http\Services\Dashboard\PublicServices\ChangeStatus\Controller\ChangeStatusController;
// use App\Http\Services\Driver\GetDriverNotification\Controller\GetDriverNotificationController;
use App\Http\Services\Dashboard\BookingManagement\ShowBooking\Controller\ShowBookingController;
use App\Http\Services\Dashboard\BookingManagement\ViewBooking\Controller\ViewBookingController;
use App\Http\Services\Dashboard\ServiceManagement\ViewService\Controller\ViewServiceController;
use App\Http\Services\Dashboard\OfficeManagement\UpdateOffice\Controller\UpdateOfficeController;
use App\Http\Services\Dashboard\BrandManagement\DestroyVBrand\Controller\DestroyVBrandController;
use App\Http\Services\Dashboard\BrandManagement\ViewBrandList\Controller\ViewBrandListController;
use App\Http\Services\Dashboard\RolesManagement\ViewRolesList\Controller\ViewRolesListController;
use App\Http\Services\Dashboard\ServiceManagement\CheckInTrash\Controller\CheckInTrashController;
use App\Http\Services\Dashboard\UsersManagement\ViewUsersList\Controller\ViewUsersListController;
use App\Http\Services\Dashboard\CouponManagement\DestroyCoupon\Controller\DestroyCouponController;
use App\Http\Services\Dashboard\DriverManagement\DestroyDriver\Controller\DestroyDriverController;
use App\Http\Services\Dashboard\HelpDeskManagement\ViewHelpDesk\Controller\ViewHelpDeskController;
use App\Http\Services\Dashboard\OfficeManagement\DestroyOffice\Controller\DestroyOfficeController;
use App\Http\Services\Dashboard\RedisApi\GetOrdersByStatus\Controller\GetOrdersByStatusController;
use App\Http\Services\Dashboard\BannersManagement\DestroyBanner\Controller\DestroyBannerController;
use App\Http\Services\Dashboard\ServiceManagement\ActionService\Controller\ActionServiceController;
use App\Http\Services\Dashboard\ServiceManagement\DeleteService\Controller\DeleteServiceController;
use App\Http\Services\Dashboard\OfficeManagement\ViewOfficeList\Controller\ViewOfficeListController;
use App\Http\Services\Dashboard\Settings\GetOfficeComission\Controller\GetOfficeComissionController;
use App\Http\Services\Dashboard\Settings\LayoutSettingsPage\Controller\LayoutSettingsPageController;
use App\Http\Services\Dashboard\ServiceManagement\DestroyService\Controller\DestroyServiceController;
use App\Http\Services\Dashboard\VehicleManagement\DestroyVehicle\Controller\DestroyVehicleController;
use App\Http\Services\Dashboard\CouponManagement\ViewCouponsList\Controller\ViewCouponsListController;
use App\Http\Services\Dashboard\DriverManagement\GetOrderHistory\Controller\GetOrderHistoryController;
use App\Http\Services\Dashboard\DriverManagement\ViewDriversList\Controller\ViewDriversListController;
use App\Http\Services\Dashboard\BannersManagement\ViewBannersList\Controller\ViewBannersListController;
use App\Http\Services\Dashboard\VehicleManagement\ViewVehicleList\Controller\ViewVehicleListController;
use App\Http\Services\Dashboard\EmployeeManagement\DestroyEmployee\Controller\DestroyEmployeeController;
use App\Http\Services\Dashboard\Settings\SaveTermAndCondition\Controller\SaveTermAndConditionController;
use App\Http\Services\Dashboard\BookingManagement\Follow\FollowOrdersToView\FollowOrdersToViewController;
use App\Http\Services\Dashboard\RoleAndPermission\Role_Layout_Page\Controller\Role_Layout_PageController;
use App\Http\Services\Dashboard\DriverManagement\ViewOfficeDrivers\Controller\ViewOfficeDriversController;
use App\Http\Services\Dashboard\EmployeeManagement\ViewEmployeeList\Controller\ViewEmployeeListController;
use App\Http\Services\Dashboard\BookingManagement\ChangeOrderStatus\Controller\ChangeOrderStatusController;
use App\Http\Services\Dashboard\ServiceManagement\BulkActionService\Controller\BulkActionServiceController;
use App\Http\Services\Dashboard\UsersManagement\CreateOrUpdateUser\Controller\CreateOrUpdateUserController;
use App\Http\Services\Dashboard\BrandManagement\CreateOrUpdateBrand\Controller\CreateOrUpdateBrandController;
use App\Http\Services\Dashboard\CommissionManagement\UpdateCommissions\Controller\UpdateCommissionsController;
use App\Http\Services\Dashboard\OfficeManagement\ViewVehicleByOffice\Controller\ViewVehicleByOfficeController;
use App\Http\Services\Dashboard\SubServiceManagement\DestroySubService\Controller\DestroySubServiceController;
use App\Http\Services\Dashboard\BookingManagement\BookingStatusUpdate\Controller\BookingStatusUpdateController;
use App\Http\Services\Dashboard\NotificationsManagement\PushNotification\Controller\PushNotificationController;
use App\Http\Services\Dashboard\CouponManagement\CreateOrUpdateCoupon\Controller\CreateOrUpdateCouponController;
use App\Http\Services\Dashboard\DriverManagement\CreateOrUpdateDriver\Controller\CreateOrUpdateDriverController;
use App\Http\Services\Dashboard\OfficeManagement\CreateOrUpdateOffice\Controller\CreateOrUpdateOfficeController;
use App\Http\Services\Dashboard\RatingManagement\UserRattingIndexData\Controller\UserRattingIndexDataController;
use App\Http\Services\Dashboard\RedisApi\GetOnlyNewOrdersByStatus\Controller\GetOnlyNewOrdersByStatusController;
use App\Http\Services\Dashboard\SubServiceManagement\ViewSubServiceList\Controller\ViewSubServiceListController;
use App\Http\Services\Dashboard\BannersManagement\CreateOrUpdateBanner\Controller\CreateOrUpdateBannerController;
use App\Http\Services\Dashboard\VehicleManagement\Pages\CreateVehiclePage\Controller\CreateVehiclePageController;
use App\Http\Services\Dashboard\ServiceManagement\CreateOrUpdateService\Controller\CreateOrUpdateServiceController;
use App\Http\Services\Dashboard\VehicleManagement\CreateOrUpdateVehicle\Controller\CreateOrUpdateVehicleController;
use App\Http\Services\Dashboard\RatingManagement\DriverRattingIndexData\Controller\DriverRattingIndexDataController;
use App\Http\Services\Dashboard\SubServiceManagement\BulkActionSubService\Controller\BulkActionSubServiceController;
use App\Http\Services\Dashboard\RoleAndPermission\UpdateRolesPermissions\Controller\UpdateRolesPermissionsController;
use App\Http\Services\Dashboard\EmployeeManagement\CreateOrUpdateEmployee\Controller\CreateOrUpdateEmployeeController;
use App\Http\Services\Dashboard\BookingManagement\Follow\FollowOrderLayoutData\Controller\FollowOrderLayoutDataController;
use App\Http\Services\Dashboard\CommissionManagement\UpdateOfficeCommissions\Controller\UpdateOfficeCommissionsController;
use App\Http\Services\Dashboard\BookingManagement\Follow\FollowOrderOnMapToView\Controller\FollowOrderOnMapToViewController;
use App\Http\Services\Dashboard\SubServiceManagement\CreateOrUpdateSubService\Controller\CreateOrUpdateSubServiceController;
use App\Models\SystemSetting;
use App\Models\InfrastructureNode;

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
   })->name('user.forgot_password.legacy');


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
   })->name('setting.index.legacy');



   Route::get('/logout', function () {
    //  $user = User::where('id', 1)->first();
    return view('frontend.index');
    //return view('service.index');
   })->name('logout.legacy');

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


// Route::get('/scheduledRideData', [BookingController::class, 'scheduledRideData'])->name('page.scheduledRideData');


Route::group([ 'middleware' => ['auth:office,admin,employee' ,'set-language','Resolved-Shard','Configure-Database'] ], function() {
// 'multiple-database',




Route::get('/scheduled-ride-data', [BookingController::class, 'index']);
Route::get('/drivers', [BookingController::class, 'drivers']);

Route::post('/trips/assign-driver', [BookingController::class, 'assignDriver']);
Route::post('/trips/cancel', [BookingController::class, 'cancel']);

Route::get('/trips/{id}', [BookingController::class, 'show']);
Route::get('/trips/stats', [BookingController::class, 'stats']);
Route::get('/scheduled-ride/view', [BookingController::class, 'view'])->name('scheduled.rides.view');


Route::get('cities', function () {
    return \App\Models\City::select('id','name')->get();
});

Route::get('drivers-list', function () {
    return \App\Models\Driver::select('id','firstName','lastName')->get();
});




// Route::get('/scheduledRideData', [BookingController::class, 'scheduledRideData'])->name('page.scheduledRideData');



Route::get('/admin/office-requests', [OfficeRequestController::class, 'index'])->name('office.requests.index');
Route::patch('/admin/office-requests/{id}/status', [OfficeRequestController::class, 'updateStatus'])->name('office.requests.updateStatus');

Route::get('/admin/contact-messages', [\App\Http\Controllers\ContactMessageController::class, 'index'])->name('contact.messages.index');
Route::patch('/admin/contact-messages/{id}/status', [\App\Http\Controllers\ContactMessageController::class, 'updateStatus'])->name('contact.messages.updateStatus');

Route::get('/admin/site-content', [\App\Http\Controllers\SiteContentController::class, 'index'])->name('admin.site-content.index');
Route::put('/admin/site-content', [\App\Http\Controllers\SiteContentController::class, 'update'])->name('admin.site-content.update');

Route::get('/admin/site-faqs', [\App\Http\Controllers\SiteFaqController::class, 'index'])->name('admin.site-faqs.index');
Route::post('/admin/site-faqs', [\App\Http\Controllers\SiteFaqController::class, 'store'])->name('admin.site-faqs.store');
Route::put('/admin/site-faqs/{id}', [\App\Http\Controllers\SiteFaqController::class, 'update'])->name('admin.site-faqs.update');
Route::delete('/admin/site-faqs/{id}', [\App\Http\Controllers\SiteFaqController::class, 'destroy'])->name('admin.site-faqs.destroy');
Route::post('/admin/site-faqs/{id}/toggle', [\App\Http\Controllers\SiteFaqController::class, 'toggle'])->name('admin.site-faqs.toggle');

Route::get('/admin/submissions', [\App\Http\Controllers\SubmissionsController::class, 'hub'])->name('admin.submissions.hub');
Route::get('/admin/submissions/drivers', [\App\Http\Controllers\SubmissionsController::class, 'drivers'])->name('admin.submissions.drivers');
Route::patch('/admin/submissions/drivers/{id}/status', [\App\Http\Controllers\SubmissionsController::class, 'driverStatus'])->name('admin.submissions.drivers.status');

Route::get('/admin/plans', [\App\Http\Controllers\SubscriptionPlanAdminController::class, 'index'])->name('admin.plans.index');
Route::post('/admin/plans', [\App\Http\Controllers\SubscriptionPlanAdminController::class, 'store'])->name('admin.plans.store');
Route::put('/admin/plans/{id}', [\App\Http\Controllers\SubscriptionPlanAdminController::class, 'update'])->name('admin.plans.update');
Route::delete('/admin/plans/{id}', [\App\Http\Controllers\SubscriptionPlanAdminController::class, 'destroy'])->name('admin.plans.destroy');
Route::post('/admin/plans/{id}/toggle', [\App\Http\Controllers\SubscriptionPlanAdminController::class, 'toggle'])->name('admin.plans.toggle');
Route::post('/admin/plans/seed', [\App\Http\Controllers\SubscriptionPlanAdminController::class, 'seed'])->name('admin.plans.seed');


Route::get('/get-map-center', function() {
    $region = session('region', 'qa');

    $capitals = [
        'sy' => ['lat' => 33.51389, 'lng' => 36.27639],
        'us' => ['lat' => 38.8977,  'lng' => -77.0365],
        'qa' => ['lat' => 25.2854,  'lng' => 51.5310],
    ];

    $center = $capitals[$region] ?? ['lat' => 33.51389, 'lng' =>  36.27639];

    return response()->json($center);
});

Route::prefix('settings')->group(function () {

    Route::get('/', function () {
        return view('settings.admin-settings.index');
    })->name('settings.index');


    Route::get('/general', function () {
    $countries = Country::on('mysql')->where('is_active', true)
        ->orderBy('name')
        ->get()
        ->toArray();


        $languages = [
            ['code' => 'ar', 'ar_name' => 'العربية' ,'en_name' => 'Arabic'],
            ['code' => 'en', 'ar_name' => 'الانكليزية', 'en_name' => 'English'],
        ];

        // $currencies = [
        //     ['code' => 'SYP', 'symbol' => 'ل.س'],
        //     ['code' => 'USD', 'symbol' => '$'],
        // ];

        $currentSettings = [
            'country'  => optional(SystemSetting::where('key','country')->first())->value,
            'language' => optional(SystemSetting::where('key','language')->first())->value,
            'currency' => optional(SystemSetting::where('key','currency')->first())->value,
            'timezone' => optional(SystemSetting::where('key','timezone')->first())->value,
        ];

        $timezones = \DateTimeZone::listIdentifiers();

        return view('settings.admin-settings.partials.general',[
        'countries' => $countries,
        'languages' => $languages,
        'timezones' => $timezones,
        'settings' => $currentSettings
    ]);
    })->name('settings.general');








Route::post('settings/generals/update', function (Request $request) {

    $validatedData = $request->validate([
        'countryId' => 'required|integer',
        'language' => 'required|string|in:ar,en',
        'currency' => 'required|string',
        'timezone' => 'required|string|in:' . implode(',', \DateTimeZone::listIdentifiers()),
    ]);

    $country =  Country::on('global')->where('id', $validatedData['countryId'])->first();

    $node = InfrastructureNode::query()
        ->where('country_code', $country->iso2)
        ->where('type', 'country')
        ->first();



    if (!$node || !$country) {
        return redirect()->back()
            ->withErrors(['countryId' => 'الدولة المختارة غير مدعومة ']);
    }

    SystemSetting::updateOrCreate(
        ['key' => 'country'],
        ['value' => $country->toJson()]
    );

    SystemSetting::updateOrCreate(
        ['key' => 'language'],
        ['value' => $validatedData['language']]
    );

    SystemSetting::updateOrCreate(
        ['key' => 'currency'],
        ['value' => $validatedData['currency']]
    );

    SystemSetting::updateOrCreate(
        ['key' => 'timezone'],
        ['value' => $validatedData['timezone']]
    );

    Session::put('active_shard_id', $node->id);

    return redirect()->route('home')
        ->with('success', 'تم تحديث الإعدادات بنجاح');

})->name('settings.generals.update');


    /*
    |------------------------------------------------------------------
    | Currency exchange rates (global `currencies` table)
    |------------------------------------------------------------------
    | The exchange rate is business-owned — SYP↔USD has no reliable feed —
    | so an admin sets it here. `exchange_rate` = units of the currency per
    | 1 unit of the DEFAULT currency; the CurrencyConverter refuses to convert
    | while a rate is 0, so a wallet top-up cannot be mis-priced. This unblocks
    | the USD→SYP wallet credit for the Syrian rider.
    */
    Route::get('/currencies', function () {
        $currencies = \App\Models\Currency::query()
            ->orderByDesc('is_default')
            ->orderBy('code')
            ->get();

        $default = $currencies->firstWhere('is_default', true)
            ?? $currencies->firstWhere('code', 'SAR');

        return view('settings.admin-settings.partials.currencies', [
            'currencies' => $currencies,
            'default' => $default,
        ]);
    })->name('settings.currencies');

    Route::post('/currencies/update', function (Request $request) {
        // rates: { <code>: <rate> } — every editable (non-default) currency.
        $validated = $request->validate([
            'rates' => ['required', 'array'],
            'rates.*' => ['required', 'numeric', 'min:0', 'max:100000000'],
        ]);

        foreach ($validated['rates'] as $code => $rate) {
            \App\Models\Currency::query()
                ->where('code', $code)
                ->where('is_default', false) // the base is always 1.0 — never edit it
                ->update(['exchange_rate' => $rate]);
        }

        return redirect()->route('settings.index')
            ->with('success', 'تم تحديث أسعار الصرف بنجاح');
    })->name('settings.currencies.update');


    // Route::post('settings/generals/update', action: function (Request $request) {

    // // return response()->json($request->all());
    //     $validatedData = $request->validate([
    //         'countryId' => 'required',
    //         'language' => 'required|string|in:ar,en',
    //         'currency' => 'required|string',
    //         'timezone' => 'required|string|in:' . implode(',', \DateTimeZone::listIdentifiers()),
    //     ]);

    //     $country =  Country::on('mysql')->where('id', $validatedData['countryId'])->first();
    //     SystemSetting::updateOrCreate(
    //         ['key' => 'country'],
    //         ['value' => $country->toJson()]
    //     );

    //     SystemSetting::updateOrCreate(
    //         ['key' => 'language'],
    //         ['value' => $validatedData['language']]
    //     );

    //     SystemSetting::updateOrCreate(
    //         ['key' => 'currency'],
    //         ['value' => $validatedData['currency']]
    //     );

    //     SystemSetting::updateOrCreate(
    //         ['key' => 'timezone'],
    //         ['value' => $validatedData['timezone']]
    //     );

    //     Session::put('region', $country->iso2);
    //    return redirect()->route('home')->with('success', 'تم تحديث الإعدادات بنجاح');

    // })->name('settings.generals.update');


    // Route::get('/account', function () {
    //     return view('settings.partials.account');
    // })->name('settings.account');

    // Route::get('/system', function () {
    //     return view('settings.partials.system');
    // })->name('settings.system');

    // Route::get('/notifications', function () {
    //     return view('settings.partials.notifications');
    // })->name('settings.notifications');

    // Route::get('/security', function () {
    //     return view('settings.partials.security');
    // })->name('settings.security');

});


Route::prefix('my-services')->group(function () {
    Route::get('/', [SubServicesPricingController::class, 'page'])->name('my-services.page');
    Route::get('/index', [SubServicesPricingController::class, 'index'])->name('my-services.index');
    Route::get('/show/{id}', [SubServicesPricingController::class, 'show'])->name('my-services.show');
    Route::put('/update/{id}', [SubServicesPricingController::class, 'update'])->name('my-services.update');
    Route::get('/get-routes/{id}/routes', [SubServicesPricingController::class, 'getRoutes'])->name('my-services.get-routes');
    Route::put('/update-routes/{id}/routes', [SubServicesPricingController::class, 'updateRoutes'])->name('my-services.update-routes');

    Route::get('/cities/{countryId}', [SubServicesPricingController::class, 'cities'])->name('my-services.cities');

   Route::get('/countries', [SubServicesPricingController::class, 'countries'])->name('my-services.countries');
});



    Route::get('/get-available-drivers', function(Request $request){


    $search = $request->query('search', '');
    $page = (int) $request->query('page', 1);
    $perPage = (int) $request->query('perPage', 10);

    $query = new Driver();
    $query->scopeForCurrentUser();

    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('firstName', 'like', "%{$search}%")
              ->orWhere('lastName', 'like', "%{$search}%");
        });
    }

    $total = $query->count();
    $drivers = $query->orderBy('firstName')
                     ->skip(($page - 1) * $perPage)
                     ->take($perPage)
                     ->get();

    return response()->json([
        'drivers' => $drivers,
        'pagination' => [
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'hasMore' => ($page * $perPage) < $total,
        ]
    ]);
})->name('drivers.list');



Route::post('/orders/assign-driver', AssignToDriverController::class)->name('orders.assignDriver');


// Route::get('office/get/wallet-balance', [AddBalanceToWalletController::class])
//     ->name('office.get-wallet-balance');



Route::get('driver/get/wallet-balance', function(Request $request){

            $driver_id = $request->query('driver_id');
            $driver = Driver::find($driver_id);

            if(!$driver){
                return response()->json([
                    'success' => false,
                    'message' => __('messages.error_occurred')
                ]);
            }

            return response()->json([
                'success' => true,
                'balance' => $driver->walletBalance ?? 0
            ]);
})->name('driver.get-wallet-balance');


Route::post('driver/add-balance-to-wallet', function(Request $request){

            $driver_id = $request->input('driver_id');
            $amount    = $request->input('amount');

            $driver = Driver::find($driver_id);
            if(!$driver){
                return response()->json([
                    'success' => false,
                    'message' => __('messages.error_occurred')
                ]);
            }


        if (Auth::guard('office')->check()) {
             $office  = Office::find(Auth::id());
                if($office->walletBalance< $amount){
                        return response()->json([
                    'success' => false,
                    'message' => __('ليس لديك رصيد كافي في محفظتك')
                ]);
        }}

        else if (Auth::guard('employee')->check()) {
            $employee = Auth::guard('employee')->user();
            if ($employee->officeId) {
                $office  = Office::find($employee->officeId);
                if($office->walletBalance< $amount){
                        return response()->json([
                    'success' => false,
                    'message' => __('ليس لديك رصيد كافي في محفظتك')
                ]);
            }
        }
    }


            $driver->walletBalance = ($driver->walletBalance ?? 0) + $amount;
            $driver->save();

            return response()->json([
                'success' => true,
                'balance' => $driver->walletBalance,
                'message' => __('messages.balance_added_success')
            ]);

})->name('driver.add-blance-to-wallet');






Route::get('office/get/wallet-balance', function(Request $request){

            $office_id = $request->query('office_id');
            $office = Office::find($office_id);

            if(!$office){
                return response()->json([
                    'success' => false,
                    'message' => __('messages.error_occurred'),
                ]);
            }

            return response()->json([
                'success' => true,
                'balance' => $office->walletBalance ?? 0
            ]);

})->name('office.get-wallet-balance');

Route::post('office/add-balance-to-wallet', function(Request $request){

            $office_id = $request->input('office_id');
            $amount    = $request->input('amount');

            $office = Office::find($office_id);
            if(!$office){
                return response()->json([
                    'success' => false,
                    'message' => __('messages.error_occurred')
                ]);
            }

            $office->walletBalance = ($office->walletBalance ?? 0) + $amount;
            $office->save();

            return response()->json([
                'success' => true,
                'balance' => $office->walletBalance,
                'message' => __('messages.balance_added_success')
            ]);

})->name('office.add-blance-to-wallet');

    Route::prefix('driver-applications')->group(function () {
    Route::get('/', IndexDriverJobApplicationController::class)->name('driver-applications.index');
    Route::get('/data', DriverJobApplicationListController::class)->name('driver-applications.data');
    Route::get('/create', [DriverJobApplicationController::class, 'create'])->name('driver-applications.create');
    Route::post('/', [DriverJobApplicationController::class, 'store'])->name('driver-applications.store');
    Route::get('/{id}', [DriverJobApplicationController::class, 'show'])->name('driver-applications.show');
    Route::get('/{id}/edit', [DriverJobApplicationController::class, 'edit'])->name('driver-applications.edit');
    Route::put('/{id}', [DriverJobApplicationController::class, 'update'])->name('driver-applications.update');
    Route::put('/{id}/status', [DriverJobApplicationController::class, 'updateStatus'])->name('driver-applications.update-status');
    Route::delete('/{id}', [DriverJobApplicationController::class, 'destroy'])->name('driver-applications.destroy');
});

Route::get('/driver1', function () {
    $application = DriverJobApplication::first();
    return view('driver',compact('application'));
   })->name('driver1');



    Route::get('test-postman', function(){
        return 'done';
    });

  Route::post('/office/customers/data', [OfficeCustomerController::class, 'officeCustomersData'])->name('office.customers.data');

  Route::get('office/{officeId}/customer/{customerId}', [OfficeCustomerController::class, 'show'])->name('customer.show');
  Route::get('/customers', [OfficeCustomerController::class, 'index'])
      ->name('customer.index');

  Route::prefix('wallet-transactions')->group(function () {
    Route::get('/', [WalletTransactionController::class, 'index'])->name('wallet-transactions.index');
    Route::get('/data', [WalletTransactionController::class, 'data'])->name('wallet-transactions.data');
    Route::get('/{id}', [WalletTransactionController::class, 'show'])->name('wallet-transactions.show');
});

  Route::group(['prefix' => 'department'], function () {

    Route::get('/', [DepartmentController::class, 'index'])->name('department.index');

    Route::get('/index-data', [DepartmentController::class, 'getData'])->name('department.index-data');

    Route::get('/create_page', [DepartmentController::class, 'create'])->name('department.create');

    Route::post('/bulk-action', [DepartmentController::class, 'bulkAction'])->name('departments.bulk-action');

    Route::delete('/destroy/{department}', [DepartmentController::class, 'destroy'])->name('department.destroy');

    Route::get('/edit/{department}', [DepartmentController::class, 'edit'])->name('departments.edit');

});



  Route::resource('departments', DepartmentController::class)->except(['edit']);

  // Route::resource('departments', DepartmentController::class);


Route::get('/user/bookings/data', [\App\Http\Controllers\UserBookingController::class, 'getUserBookings'])
    ->name('user.bookings.data');



  Route::get('/driver/bookings/data',function(Request $request){
    $query = Booking::query()->where('driverId',  $request->userId);


    if ($request->startDate && $request->endDate) {
        $query->whereDate('startAt', '>=', $request->startDate)
              ->whereDate('startAt', '<=', $request->endDate);
    }

    if ($request->bookingId) {
        $query->where('id', $request->bookingId);
    }

    return DataTables::of($query)
        ->addColumn('driverCommissionValue', fn($row) => number_format($row->driverCommissionValue, 2))
        ->addColumn('fleetCommissionValue', fn($row) => number_format($row->fleetCommissionValue, 2))
        ->addColumn('officeCommissionValue', fn($row) => number_format($row->officeCommissionValue, 2))
        ->toJson();
  })->name('driver.bookings.data');




  Route::prefix('api')->group(function () {




Route::get('/dashboard-stats', function(Request $request){


  $start = Carbon::parse($request->start_date)->startOfDay();
  $end = Carbon::parse($request->end_date)->endOfDay();

      if (Auth::guard('admin')->check()) {
        $user = FleetOffice::first();
        $orders = Booking::query();
        $totalRevenue = Booking::where( 'status', OrderStatus::$Completed )
        ->whereBetween('created_at',[$start, $end])
        ->sum('fleetCommissionValue');
    }
    else if (Auth::guard('office')->check()) {
        $user = Auth::user();
        $orders = Booking::query()->where('officeId', $user->id);
        $totalRevenue = $orders->where('status',OrderStatus::$Completed)
        ->whereBetween('created_at',[$start, $end])
        ->sum('officeCommissionValue');
    }
    else if (Auth::guard('employee')->check()) {
        $employee = Auth::guard('employee')->user();
        if ($employee->office_id) {
            $user = Office::find($employee->office_id);
            $orders = Booking::query()->where('officeId', $employee->office_id);
            $totalRevenue = $orders->where('status',OrderStatus::$Completed)
            ->whereBetween('created_at',[$start, $end])
            ->sum('officeCommissionValue');
        } else {
            $user = FleetOffice::first();
            $orders = Booking::query()->where('officeId', null);
        }
    } else {
        make_exception('no user');
    }

    $orders = $orders
    ->where('status', OrderStatus::$Completed)
    ->whereBetween('created_at', [$start, $end]);

                    //  return response()->json($orders->get());
    $paymentStats = (clone $orders)
        ->selectRaw("
            COUNT(CASE WHEN paymentType = 'electronic' THEN 1 END) AS electronicPayments,
            COUNT(CASE WHEN paymentType = 'cash' THEN 1 END) AS cashPayments,
            COUNT(CASE WHEN paymentType = 'fleet_wallet' THEN 1 END) AS walletPayments,
            COUNT(*) as trips
        ")
        ->first();

    // $totalRevenue = WalletTransaction::where('to_type', get_class($user))
    //     ->where('to_id', $user->id)
    //     ->whereBetween('created_at', [$request->start, $request->end])
    //     ->sum('amount');

    $walletWithdrawn = WalletTransaction::where('from_type', get_class($user))
        ->where('from_id', $user->id)
        ->whereBetween('created_at', [$start, $end])
        ->sum('amount');

    return response()->json([
        'electronicPayments' => $paymentStats->electronicPayments ?? 0,
        'cashPayments'       => $paymentStats->cashPayments ?? 0,
        'walletPayments'     => $paymentStats->walletPayments ?? 0,
        'trips'              => $paymentStats->trips ?? 0,
        'totalRevenue'       => getPriceFormat($totalRevenue),
        'walletWithdrawn'    => getPriceFormat($walletWithdrawn),
      ]);

    });






  Route::get('/wallet-stats', function(){


    if (Auth::guard('admin')->check()) {
      $office = FleetOffice::first();
      $pendingAmount = FleetWalletRedisModel::getBalanceValueByStatus(BalanceStatus::$Pending) ?? 0;
      $driverDues = Driver::sum('fleetDues');
      $officeDues = Office::sum('fleetDues');
      $walletBalance = $office->walletBalance;

  }

  else if (Auth::guard('office')->check()) {
      $office = Auth::guard('office')->user();
      $office = Office::find($office->id);
      $pendingAmount = FleetWalletRedisModel::getBalanceValueByStatus(BalanceStatus::$Pending) ?? 0;
      $driverDues = Driver::sum('officeDues');
      $officeDues = $office->fleetDues;
      $walletBalance = $office->walletBalance;

  }

  else if (Auth::guard('employee')->check()) {
      $employee = Auth::guard('employee')->user();
      if ($employee->officeId) {
        $office = Office::find($employee->officeId);
      } else {
         return;
      }
  }


    // $walletBalance = $office->walletBalance;


    return response()->json([
        'walletBalance' =>getPriceFormat($walletBalance) ,// $availableWithdrawal ,// getPriceFormat($availableWithdrawal),
        'pendingAmount' => getPriceFormat($pendingAmount),
        'driverDues' => getPriceFormat($driverDues),
        'officeDues' => getPriceFormat($officeDues),
    ]);
  });

  });






  Route::get('/fleet/dashboard-stats', [FleetDashboardController::class, 'getDashboardStats'])->name('fleet.dashboard.stats');

  Route::get('test' ,function(){
    return view('test4');
  });


  Route::prefix('api/roles')->group(function () {
      Route::get('/', [RoleController::class, 'index']);
      Route::post('/', [RoleController::class, 'store']);
      Route::get('/{id}', [RoleController::class, 'show']);
      Route::put('/{id}', [RoleController::class, 'update']);
      Route::delete('/{id}', [RoleController::class, 'destroy']);

      Route::post('/{id}/permissions', [RoleController::class, 'assignPermissions']);



      Route::post('/{role}/permissions/assign', [RoleController::class, 'assignPermission']);
      Route::post('/{role}/permissions/remove', [RoleController::class, 'removePermission']);

  });

  Route::prefix('api/permissions')->group(function () {
      Route::get('/', [PermissionController::class, 'index']);
      Route::post('/', [PermissionController::class, 'store']);
      Route::get('/{id}', [PermissionController::class, 'show']);
      Route::put('/{id}', [PermissionController::class, 'update']);
      Route::delete('/{id}', [PermissionController::class, 'destroy']);
  });

  Route::get('role-permission-control',function(){
    return view('role.roles');
 })->name('role-permission');




  Route::get('/owners/by-type', [IssueController::class, 'getOwnersByType']);

Route::prefix('issues')->group(function () {
    Route::get('/', [IssueController::class, 'index'])->name('issues.index');
    Route::get('/data', [IssueController::class, 'data'])->name('issues.data');
    Route::delete('/{id}', [IssueController::class, 'destroy'])->name('issues.destroy');
    Route::get('/create', [IssueController::class, 'create'])->name('issues.create');
    Route::get('/{issue}/edit', [IssueController::class, 'edit'])->name('issues.edit');
    Route::put('update/{issue}', [IssueController::class, 'update'])->name('issues.update');
    Route::post('/store', [IssueController::class, 'store'])->name('issues.store');
    Route::get('show/{issue}', [IssueController::class, 'show'])->name('issues.show');

});


  Route::prefix('tickets')->group(function () {
    Route::get('{id}', [TicketController::class, 'show'])->name('tickets.show');
    // Route::post('{id}/reply', [TicketController::class, 'reply'])->name('tickets.reply');
    Route::put('{id}/status', [TicketController::class, 'updateStatus'])->name('tickets.status.update');
    Route::put('{id}/assign', [TicketController::class, 'assign'])->name('tickets.assign');

    Route::post('/{id}/reply-ajax', [TicketController::class, 'replyAjax'])->name('tickets.reply.ajax');

    Route::get('{id}/replies', [TicketController::class, 'fetchReplies'])->name('tickets.replies');


    Route::get('/tickets/{id}', [TicketController::class, 'show'])->name('tickets.show.legacy');
    Route::put('/tickets/{id}', [TicketController::class, 'update'])->name('tickets.update');
    Route::post('/tickets/{id}/close', [TicketController::class, 'close'])->name('tickets.close');
  });






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
  Route::post('update-commission', action: [OfficeController::class, 'updateCommission'])->name('office.updateCommission');
Route::post('reset-commission', [OfficeController::class, 'resetCommission'])
    ->name('office.resetCommission');
    Route::get('/', IndexOfficeController::class)->name('office.index');
    Route::get('create', CU_OfficePageController::class)->name('office.create-page');
    Route::get('provider/list/{status?}', function(){return 'office';})->name('office.pending');
    Route::post('/update',UpdateOfficeController::class)->name('office.update');
    Route::get('office-index-data', ViewOfficeListController::class)->name('office.index_data');
    Route::get('provider/approve/{id}', function(){return 'office';})->name('office.approve');
    Route::post('destroy/{id}', DestroyOfficeController::class)->name('office.destroy');
    Route::post('provider-action', function(){return 'office';})->name('office.action');
    Route::post('provider-bulk-action', function(){return 'office';})->name('office.bulk-action');
    Route::get('view', ShowOfficeController::class)->name('office.show');
    Route::get('view-drivers',ViewOfficeDriversController::class )->name('driver.byOffice');
    Route::get('view-vehicles',ViewVehicleByOfficeController::class )->name('vehicle.byOffice');


    Route::get('review', GetOfficeReviewsController::class)->name('office.review');
});

Route::group(['prefix' => 'vehicle'] , function () {
    Route::get('/', IndexVehicleController::class)->name('vehicle.index');
    Route::get('vehicle-index-data', ViewVehicleListController::class )->name('vehicle.index-data');
    Route::post('vehicle-bulk-action',BulkActionSubServiceController::class)->name('vehicle.bulk-action');
    Route::get('/create_page', CreateVehiclePageController::class )->name('vehicle.create');
    Route::post('/add', CreateOrUpdateVehicleController::class)->name('vehicle.store');
    Route::post('vehicle-action',[BulkActionSubServiceController::class ,'action'])->name('vehicle.action');
    Route::delete('destroy/{vehicleId}', DestroyVehicleController::class)->name('vehicle.destroy');
});

// Route::get('/drivers/by-office/{officeId}', [VehicleController::class, 'getDriversByOffice'])->name('drivers.byOffice');

Route::get('/drivers/by-office/{officeId}', function ($officeId) {
        $drivers = Driver::where('officeId', $officeId)->get();
        return response()->json($drivers);
})->name('drivers.byOffice');


Route::group(['prefix' => 'booking'] , function () {
    Route::get('/', IndexOrdersController::class)->name('booking.index');

    Route::get('/office-orders', officeOrderController::class)->name('office.orders');

    Route::get('/history', IndexBookingController::class)->name('booking.history');
    Route::get('booking-index-data',ViewBookingController::class )->name('booking.index_data');
//  Route::get('details/{id}',ShowBookingController::class)->name('booking.show');
    Route::post('sub-bulk-action',BulkActionSubServiceController::class)->name('booking.bulk-action');
    Route::get('/create',[ CreateOrUpdateSubServiceController::class ,'to_create'])->name('booking.create');
    Route::post('sub-service-action',[BulkActionSubServiceController::class ,'action'])->name('booking.action');
    Route::delete('destroy/{id}', DestroySubServiceController::class)->name('booking.destroy');
    Route::post('/booking-layout-page/{id}',ShowBookingLayoutPage::class)->name('booking_layout_page');
    Route::get('/booking-details', BookingDetailsController::class)->name('booking.show');

    Route::get('/ongoing-index',[FollowOrdersToViewController::class ,'ongoing_index'] )->name('follow.ongoing');
    Route::get('/follow-layout', FollowOrdersToViewController::class)->name('follow.layout');

    Route::get('/order-layout-data', FollowOrderLayoutDataController::class)->name('order-layout-data');

    Route::get('/order-on-map', FollowOrderOnMapToViewController::class)->name('order.follow.map');
});


Route::get('/invoice_pdf/{id}', Create_PDF_Controller::class )->name('invoice_pdf');
Route::post('booking-status-update', BookingStatusUpdateController::class,'updateStatus')->name('bookingStatus.update');



Route::group(['prefix' => 'employee'] , function() {
  Route::get('/',IndexEmployeeController::class)->name('employee.index');
  Route::get('employee-index-data', ViewEmployeeListController::class )->name('employee.index-data');
  Route::get('/create', CU_EmployeePageController::class )->name('employee.create');
  Route::post('/add', CreateOrUpdateEmployeeController::class)->name('employee.store');

  Route::delete('destroy/{id}', function (Request $request, $id) {
      $employee = Employee::findOrFail($id);
      $employee->delete();

      return response()->json([
          'status' => true,
          'message' => __('messages.deleted_successfully', ['form' => __('messages.employee')]),
      ]);
  })->name('employee.destroy');

  Route::post('driver-bulk-action',BulkActionSubServiceController::class)->name('employee.bulk-action');

});

Route::group(['prefix' => 'driver'] , function() {
  Route::get('/',IndexDriverController::class)->name('driver.index');
  Route::get('driver-index-data', ViewDriversListController::class )->name('driver.index-data');
  Route::get('driver-order-history', GetOrderHistoryController::class )->name('driver.order-history');
  Route::post('update-commission', [DriverController::class, 'updateCommission'])
  ->name('driver.updateCommission');
Route::post('/driver/reset-commission', [DriverController::class, 'resetCommission'])
    ->name('driver.resetCommission');


  Route::get('/create', CU_DriverPageController::class )->name('driver.create');
  Route::post('/add', CreateOrUpdateDriverController::class)->name('driver.store');
  Route::delete('destroy/{id}', DestroyDriverController::class)->name('driver.destroy');
  Route::post('driver-bulk-action',BulkActionSubServiceController::class)->name('driver.bulk-action');
  Route::post('vehicle-bulk-action',BulkActionSubServiceController::class)->name('driver.getchangepassword');
  Route::post('vehicle-action',[BulkActionSubServiceController::class ,'action'])->name('driver.action');


  Route::get('view/change-password',function(){
    $user = Driver::First();
    $pageTitle = 'change password';
    return view('driver.changepassword',compact(['user','pageTitle']));
  })->name('driver.view.change-password');

  Route::post('change-password',function(){
    $user = Driver::First();
    $pageTitle = 'change password';
    return view('driver.changepassword',compact(['user','pageTitle']));
  })->name('driver.change-password');

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


  Route::get('view/change-password',function(){
    $user = User::First();
    $pageTitle = 'change password';
    return view('customer.changepassword',compact(['user','pageTitle']));
  })->name('user.view.change-password');

  Route::post('change-password',function(){
    $user = User::First();
    $pageTitle = 'change password';
    return view('user.changepassword',compact(['user','pageTitle']));
  })->name('user.change-password');
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

  Route::group(['prefix' => 'vbrand'] , function () {

    Route::get('/', IndexVBrandController::class)->name('vbrand.index');
    Route::get('driver-index-data', ViewBrandListController::class )->name('vbrand.index-data');
    Route::get('/create', CU_VBrandPageController::class )->name('vbrand.create');
    Route::post('/add', CreateOrUpdateBrandController::class)->name('vbrand.store');
    Route::delete('destroy', DestroyVBrandController::class)->name('vbrand.destroy');


    // for delete ----
    Route::post('banner-bulk-action',BulkActionSubServiceController::class)->name('vbrand.bulk-action');
    // for delete----


    });


  Route::group(['middleware'], function () {
      // Route::resource('role', Role_Layout_PageController::class);
      Route::get('role-index-data', ViewRolesListController::class)->name('role.index_data');
      Route::post('role-bulk-action', Role_Layout_PageController::class)->name('role.bulk-action');
  });


route::post('role-permission', Role_Layout_PageController::class)->name('role_layout_page');

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


  Route::group(['prefix' => 'permissions'], function () {

    Route::get('role/add',AddRoleViewController::class)->name('role.add');
    Route::post('role/save', AddNewRoleController::class)->name('role.save');

    Route::post('update-roles-permission',


    function(Request $request){

          // if (demoUserPermission()) {
          //     return redirect()->back()->withError(__('messages.demo_permission_denied'));
          // }

          app()[PermissionRegistrar::class]->forgetCachedPermissions();

          $permissionsInput = $request->input('permissions', []);

          $roles = Role::whereNotIn('name', ['admin'])->get();

          $allPermissions = Permission::all()->keyBy('name');

          foreach ($roles as $role) {
              $role->syncPermissions([]);
          }

          foreach ($permissionsInput as $permissionName => $roleNames) {
              // $permissionName = trim(strtolower($permissionName));

              $permission = Permission::where('name', $permissionName)->first();

              foreach ($roleNames as $roleName) {

                  $role = Role::where('name', $roleName)->first();

                  if ($role) {
                       $role->givePermissionTo($permission);

                  }
              }
          }

          return redirect()
              ->route('setting.index', ['page' => 'role-permission-setup'])
              ->withSuccess(__('messages.save_form', ['form' => __('messages.permission')]));

   // UpdateRolesPermissionsController::class
    })->name('roles-permission.update');
});

Route::group(['prefix' => 'helpdesk'] , function () {
  Route::get('/', IndexHelpDesk::class)->name('helpdesk.index');

  // Route::resource('helpdesk', HelpDeskController::class);
  Route::get('helpdesk-index-data', ViewHelpDeskController::class)->name('helpdesk.index_data');

  Route::post('helpdesk-bulk-action', ViewHelpDeskController::class)->name('helpdesk.bulk-action');
  Route::post('helpdesk-action', ViewHelpDeskController::class)->name('helpdesk.action');
  Route::post('helpdesk/{id}', ViewHelpDeskController::class)->name('helpdesk.destroy');
  Route::get('helpdesk-closed/{id}', ViewHelpDeskController::class)->name('helpdesk.closed');
  Route::post('helpdesk-activity/{id}', ViewHelpDeskController::class)->name('helpdesk.activity');
});


  Route::group(['prefix' => 'payments'] , function () {
    Route::get('/', IndexPaymentController::class)->name('payment.index');
    Route::get('/payments-index-data', ViewPaymentsController::class)->name('payment.index-data');
    });

  Route::group(['prefix' => 'commission'] , function () {
    // index
    Route::get('/fleet', CommissionsLayout::class)->name('commissions.layout');


    // update logic
    Route::post('/update-fleet-commission', UpdateCommissionsController::class)->name('commissions.fleet.update');
    Route::post('/update-office-commission', UpdateOfficeCommissionsController::class)->name('commissions.office.update');

    // Route::get('/office-owner', ViewUpdateDriverCommission::class)->name('commissions.driver');

    });


Route::get('setting/{page?}',ToViewSettingsController::class)->name('setting.index');
// Route::post('/layout-page',[ ToViewSettingsController::class, 'layoutPage'])->name('layout_page');
Route::post('/layout-page',LayoutSettingsPageController::class)->name('layout_page');

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


Route::post('add-balance',  AddBalanceController::class)->name('add-balance');



Route::group(['prefix' => 'wallet'],function(){
  Route::get('history', WalletHistoryController::class)->name('wallet.history');


});


// Route::get('get/notifications2',GetDriverNotificationController::class)->name('get-notifications');


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






Route::get('live-drivers-locations',function(){
  return RedisManagerData::getOnlineDriversMapLocations();
})->name('live-drivers-locations');

// ------------ GET -----------//
Route::get('home/statistics',GetHomeStatisticController::class)->name('home.statistics');
Route::get('get/orders-by-status',GetOrdersByStatusController::class)->name('orders-by-status');
Route::get('get/only-new-orders-by-status',GetOnlyNewOrdersByStatusController::class)->name('new-orders-by-status');
// Route::get('payment/method',GetPaymentMethodController::class);

Route::post('/change-order-status' , ChangeOrderStatusController::class);

Route::get('test3',function(){
      $language_array = [
        ['id' => 'en', 'name' => 'English'],
        ['id' => 'ar', 'name' => 'العربية'],
    ];

    $data_deletion_request = "Please delete my account and associated data.";

    $setting_data = (object)[
        'id' => 1,
        'status' => 1,
        'translations' => [
            'ar' => ['value' => 'يرجى حذف حسابي وجميع البيانات المرتبطة به.'],
            'en' => ['value' => 'Please delete my account and associated data.'],
        ],
        'translate' => function($field, $lang) {
            $translations = [
                'ar' => ['value' => 'يرجى حذف حسابي وجميع البيانات المرتبطة به.'],
                'en' => ['value' => 'Please delete my account and associated data.'],
            ];
            return $translations[$lang][$field] ?? '';
        }
    ];

    $status = 1;

  return view('setting.public-setting.social' , compact(
    'language_array',
    'data_deletion_request',
    'setting_data',
    'status'
));
});

Route::get('net', function(){
  return view('nbh.n');
});






// Route::get('get/help-suggestions', UserHelpSuggestionController::class);


Route::get('/cancelled-all-orders',function(){
    Booking::query()
                ->where('status', OrderStatus::$InProgress)
                ->update(['status'=>OrderStatus::$Cancelled]);
    return 'cancelled all orders in database';
});

Route::get('now', function(){
//dd(Carbon::now());

return Carbon::now()->format('Y-m-d H:i:s');
});











Route::get('/export-ohaus-pdf-invoice', function(){

    $data = [];

    return view('pdf-invoice',[

    'companyName' => 'OHAUS CORPORATION',
        'companyAddress' => 'P.O. Box 5667, Parsippany, NJ 07054',
        'companyPhone' => '(973) 377-9000 / (800) 672-7722',
        'companyFax' => '(973) 944-7177',
        'companyWebsite' => 'www.ohaus.com',


        'invoiceNumber' => '637458087',
        'invoiceDate' => '12/17/2024',
        'orderNumber' => '901050786',
        'collectAccount' => '694758479',
        'paymentTerms' => 'Due 30 Days from Invoice',


        'customerNumber' => '400058339',
        'customerPage' => 'Customer Page 400058339',
        'currentPage' => 1,
        'totalPages' => 3,


        'billTo' => [
            'name' => 'Ramo Trading',
            'address' => '15205 Spectrum',
            'cityStateZip' => 'IRVINE, CA 92618-3425'
        ],
        'soldTo' => [
            'name' => 'Ramo Trading',
            'address' => '15205 Spectrum',
            'cityStateZip' => 'IRVINE, CA 92618-3425'
        ],
        'shipTo' => [
            'name' => 'Asaad Ramo',
            'company' => 'Ramo Trading Consulting Inc',
            'address' => '2 chaparral Court',
            'cityStateZip' => 'Rancho Sanat Margerita, CA 92688'
        ],
        'remitTo' => [
            'name' => 'Ohaus Corporation',
            'address' => '23812 Network Place',
            'cityStateZip' => 'Chicago IL 60673',
            'note' => 'Please reference invoice 637458087 with your payment'
        ],

        // معلومات الاتصال
        'customerContact' => [
            'name' => 'Asaad Ramo',
            'telephone' => '+1 (833) 669 0944',
            'email' => 'info@ramofrading.com'
        ],

        'purchaseInfo' => [
            'purchaseOrder' => 'PO 2712',
            'deliveryNote' => '99326096',
            'orderDate' => '12/16/2024',
            'shipDate' => '12/17/2024',
            'shipmentNumber' => '5606840'
        ],


        'items' => [
            [
                'description' => 'Standard Conduct 12.88mS/cm 250ml',
                'productId' => '30100444',
                'quantity' => 5,
                'unit' => 'EA',
                'pricePerUnit' => 29.00,
                'discount' => '(30.00%)',
                'total' => 101.50,
                'notes' => [
                    'Commodity Code 3105100000',
                    'Country of Origin US',
                    'Ship Via FedEx Ground',
                    'Carrier Tracking Number: 283404897460'
                ]
            ],
            [
                'description' => 'Standard Conduct 1413μS/cm 250ml',
                'productId' => '30100443',
                'quantity' => 5,
                'unit' => 'EA',
                'pricePerUnit' => 29.00,
                'discount' => '(30.00%)',
                'total' => 101.50,
                'notes' => [
                    'Commodity Code 3105100000',
                    'Country of Origin US',
                    'Ship Via FedEx Ground',
                    'Carrier Tracking Number: 283404897460'
                ]
            ]
        ],

        'subTotal' => 203.00,
        'invoiceTotal' => 203.00,

        'bankInfo' => [
            'bankName' => 'JP Morgan Chase, N.A., New York',
            'routingNumber' => '071000013',
            'accountNumber' => '722620283',
            'swiftCode' => 'CHASUS33'
        ]]);

    $pdf = Pdf::loadView('pdf-invoice', [
        'companyName' => 'OHAUS CORPORATION',
        'companyAddress' => 'P.O. Box 5667, Parsippany, NJ 07054',
        'companyPhone' => '(973) 377-9000 / (800) 672-7722',
        'companyFax' => '(973) 944-7177',
        'companyWebsite' => 'www.ohaus.com',

        'invoiceNumber' => '637458087',
        'invoiceDate' => '12/17/2024',
        'orderNumber' => '901050786',
        'collectAccount' => '694758479',
        'paymentTerms' => 'Due 30 Days from Invoice',

        'customerNumber' => '400058339',
        'customerPage' => 'Customer Page 400058339',
        'currentPage' => 1,
        'totalPages' => 3,

        'billTo' => [
            'name' => 'Ramo Trading',
            'address' => '15205 Spectrum',
            'cityStateZip' => 'IRVINE, CA 92618-3425'
        ],
        'soldTo' => [
            'name' => 'Ramo Trading',
            'address' => '15205 Spectrum',
            'cityStateZip' => 'IRVINE, CA 92618-3425'
        ],
        'shipTo' => [
            'name' => 'Asaad Ramo',
            'company' => 'Ramo Trading Consulting Inc',
            'address' => '2 chaparral Court',
            'cityStateZip' => 'Rancho Sanat Margerita, CA 92688'
        ],
        'remitTo' => [
            'name' => 'Ohaus Corporation',
            'address' => '23812 Network Place',
            'cityStateZip' => 'Chicago IL 60673',
            'note' => 'Please reference invoice 637458087 with your payment'
        ],

        // معلومات الاتصال
        'customerContact' => [
            'name' => 'Asaad Ramo',
            'telephone' => '+1 (833) 669 0944',
            'email' => 'info@ramofrading.com'
        ],

        // معلومات الشراء
        'purchaseInfo' => [
            'purchaseOrder' => 'PO 2712',
            'deliveryNote' => '99326096',
            'orderDate' => '12/16/2024',
            'shipDate' => '12/17/2024',
            'shipmentNumber' => '5606840'
        ],

        // العناصر
        'items' => [
            [
                'description' => 'Standard Conduct 12.88mS/cm 250ml',
                'productId' => '30100444',
                'quantity' => 5,
                'unit' => 'EA',
                'pricePerUnit' => 29.00,
                'discount' => '(30.00%)',
                'total' => 101.50,
                'notes' => [
                    'Commodity Code 3105100000',
                    'Country of Origin US',
                    'Ship Via FedEx Ground',
                    'Carrier Tracking Number: 283404897460'
                ]
            ],
            [
                'description' => 'Standard Conduct 1413μS/cm 250ml',
                'productId' => '30100443',
                'quantity' => 5,
                'unit' => 'EA',
                'pricePerUnit' => 29.00,
                'discount' => '(30.00%)',
                'total' => 101.50,
                'notes' => [
                    'Commodity Code 3105100000',
                    'Country of Origin US',
                    'Ship Via FedEx Ground',
                    'Carrier Tracking Number: 283404897460'
                ]
            ]
        ],

        // المجاميع
        'subTotal' => 203.00,
        'invoiceTotal' => 203.00,

        // معلومات البنك
        'bankInfo' => [
            'bankName' => 'JP Morgan Chase, N.A., New York',
            'routingNumber' => '071000013',
            'accountNumber' => '722620283',
            'swiftCode' => 'CHASUS33'
        ]]);
        return $pdf->download('invoice.pdf');
});







Route::get('/bassam', function(){

// $google = new GoogleService();
// return $google->getCountryCode(33.5138 , 36.2765);

$whatsappService = new WhatsappMessageService();

$response = $whatsappService->send('993067534','+963','test :  hi how are you? ');

return response()->json($response);

return 'done';

    app()->setLocale('ar');


//   return __('messages.fleet_commission');

return view('web-site.site');


// return 'done';
        $countries = [
            [
                'id' => 2,
                'iso2' => 'lb',
                'iso3' => 'LBN',
                'name' => 'لبنان',
                'en_name' => 'Lebanon',
                'name_on_google_map' => 'Lebanon',
                'phone_code' => '+961',
                'currency_code' => 'LBP',
                'currency_symbol' => 'ل.ل',
                'timezone' => 'Asia/Beirut',
                'flag' => asset('storage/system/flags/lb.jpg'),
                'is_active' => true,
                'is_default' => false,
        ],
            // [
            //     'iso2' => 'sy',
            //     'iso3' => 'SYR',
            //     'name' => 'سوريا',
            //     'en_name' => 'Syria',
            //     'name_on_google_map' => 'Syria',
            //     'phone_code' => '+963',
            //     'currency_code' => 'SYP',
            //     'currency_symbol' => 'ل.س',
            //     'timezone' => 'Asia/Damascus',
            //     'flag' => asset('storage/system/flags/sy.jpg'),
            //     'is_active' => true,
            //     'is_default' => true,
            // ],
            // [
            //     'iso2' => 'us',
            //     'iso3' => 'USA',
            //     'name' => 'الولايات المتحدة',
            //     'en_name' => 'United States',
            //     'name_on_google_map' => 'United States',
            //     'phone_code' => '+1',
            //     'currency_code' => 'USD',
            //     'currency_symbol' => '$',
            //     'timezone' => 'America/New_York',
            //     'flag' => asset('storage/system/flags/us.jpg'),
            //     'is_default' => false,
            // ],
            // [
            //     'iso2' => 'qa',
            //     'iso3' => 'QAT',
            //     'name' => 'قطر',
            //     'en_name' => 'Qatar',
            //     'name_on_google_map' => 'Qatar',
            //     'phone_code' => '+974',
            //     'currency_code' => 'QAR',
            //     'currency_symbol' => 'ر.ق',
            //     'timezone' => 'Asia/Qatar',
            //     'flag' => asset('storage/system/flags/qa.jpg'),
            //     'is_active' => true,
            //     'is_default' => false,
            // ],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['iso2' => $country['iso2']],
                $country
            );
        }

        return 'done';
$data = [
    'startAddress'    => 'غير محدد',
    'endAddress'      => 'غير محدد',
    'time'            => '00:00',
    'startLatitude'   => 0,
    'startLongitude'  => 0,
    'endLatitude'     => 0,
    'endLongitude'    => 0,
    'distance'        => 0,
    'couponCode'      => null,
    'subService'      => 'عام',
    'subServiceId'    => 0,
    'userId'          => 0,
    'orderId'         => 0,
    'paymentMethod'   => 'نقداً',
    'totalAmount'     => 0,
    'amount'          => 0,
    'waypoints'       => [],
    'is_scheduled'    => false,
    'scheduled_time'  => null,
];
    (new SearchOnDriverAlgorithm($data))->start();

return 'done';

    return response()->json(OfficeSubServicePrice::all());
                $sub_service_Id = 5;

                $driverIds = Driver::query()
                ->where('is_online', true)
                ->whereHas('vehicle', function ($q) use ($sub_service_Id) {
                    $q->whereHas('subServices', function ($qq) use ($sub_service_Id) {
                        $qq->where('subServiceId', $sub_service_Id);
                    });
                })->pluck('id')->toArray();

                return response()->json($driverIds);

    $subServiceId = 10;
$kmEst = 2;
$timeEst = 40;

$prices = \App\Models\OfficeSubServicePrice::with(['office:id,officeName,logo'])
    ->where('sub_service_id', $subServiceId)
    ->get()
    ->map(function($price) use ($kmEst, $timeEst) {
        return [
            'office' => [
                'id' => $price->office->id,
                'officeName' => $price->office->officeName,
                'logo' => $price->office->logo,
            ],
            'Price' => intval(
                $price->openPrice
                + ($price->kmPrice * $kmEst)
                + ($price->minutePrice * $timeEst)
            ),
        ];
    })
    ->sortBy('finalPrice')
    ->values();
    //     $subService = SubService::with(relations: ['officePrices'])->get();

    //     foreach ($subService as $value) {

    //     $officePrices = $value->officePrices->map(function($price) {
    //         return intval(
    //             $price->openPrice
    //             + ($price->kmPrice * 500)
    //             + ($price->minutePrice * 25)
    //         );
    //     });

    //     if ($officePrices->isNotEmpty()) {
    //         $value->minPrice = $officePrices->min();
    //         $value->maxPrice = $officePrices->max();
    //     } else {
    //         $priceEst =intval($value->openPrice + ($value->kmPrice * 500) + ($value->minutePrice * 25));
    //         $value->minPrice = $priceEst ;
    //         $value->maxPrice = $priceEst ;
    //     }
    // }
    return response()->json( ['prices'=> $prices] );


    $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=33.5147,36.2760&key=" . env('GOOGLE_MAPS_KEY');
    $response = Http::get($url)->json();

    foreach ($response['results'][0]['address_components'] as $component) {
        if (in_array('locality', $component['types'])) {
            return $component['long_name'];
        }
    }

    return null;


    $city = explode('،','سوريا، دمشق');

    return response()->json($city );

    // return Hash::make('at993842733');
    $order = Booking::where('driverId','!=',null)->first();
    OrderRedisModel::storeWithPagenationService( $order);
    //   $status = OrderStatus::$Pending;
    //                 $orders   = OrderRedisModel::getByStatusAfterId(
    //         $status , 1
    //     );

    //       $orders = Booking::where('id','>',756)
    //                 ->where('status' , OrderStatus::$OnGoing)->get();
    //                 $count = $orders->count();

        // $count = OrderRedisModel::get_status_count($status);


    return response()->json($order);


    $s = $stripeService->createPaymentIntent(price: 10);



    return response()->json(['status'=> 'success' ,'in'=> $s]);


//with(['subService','driver.vehicle','payment'])
   $baseQuery = Booking::with(['subService','driver.vehicle','payment'])->where('userId', 3)
            ->where('status', OrderStatus::$InProgress)
            ->where('is_scheduled', true)
            ->whereNotNull('scheduled_time')
            ->Where('scheduled_time', '<', now()->subMinutes(30))
            ->orderBy('scheduled_time', 'asc')
            // ->orderBy(column: 'created_at', 'desc')
            ->first();

            return response()->json($baseQuery);

        $nonScheduled = (clone $baseQuery)
            ->where('is_scheduled', false)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($nonScheduled) {
            $order = $nonScheduled;
        }
               $order=  (clone $baseQuery)
            ->where('is_scheduled', true)
            ->whereNotNull('scheduled_time')
            ->Where('scheduled_time', '<', now()->subMinutes(30))
            // ->orderBy(column: 'created_at', 'desc')
            ->first();

            return response()->json($order);


  // $test = new testController();
  // $test->test();

//  $order = Booking::with([
//                'subService' => function ($query) {
//                  $query->select(SelectByLanguage::$subService);
//                 }
//                  ,'driver.vehicle',
//                  'payment'=> function ($query) {
//                   $query->select(SelectByLanguage::$paymentMethod);
//                 }
//                  ,'user'])
//     //    ->where('id', )
//         ->first();
//         return response()->json($order);




  $order  = Booking::with(['driver', 'subService','payment'])->first();

  ReminderUser::dispatch(554)->delay(now()->addSeconds(50));
  info('wait... nnnn');

  return 'done';

  $uae_ar = [
    'country_name' => 'الإمارات العربية المتحدة',
    'search_country' => 'ae',
    'continent' => 'آسيا',
    'currency_name' => 'الدرهم الإماراتي',
    'currency_symbol' => 'د.إ',
    'unit' => 'د.إ',
    'symbol' => 'د.إ',
    // 'currency_subunit' => 'فلس',
    // 'phone_format' => '+971 5# ### ####',
    // 'country_code' => 'AE',
    // 'calling_code' => '+971',
    // 'timezone' => 'Asia/Dubai',
    // 'flag' => 'https://flagcdn.com/ae.svg',
    // 'latitude' => 24.0000,
    // 'longitude' => 54.0000,
    // 'currency_decimals' => 2,
    // 'is_active' => true,
    // 'iban_supported' => true,
    // 'swift_supported' => true,
];

$uae_en = [
    'country_name' => 'United Arab Emirates',
    'search_country' => 'ae',
    'continent' => 'Asia',
    'currency_name' => 'UAE Dirham',
    'currency_symbol' => 'AED',
    'unit' => 'AED',
    'symbol' => 'AED',
    // 'currency_subunit' => 'Fils',
    // 'phone_format' => '+971 5# ### ####',
    // 'country_code' => 'AE',
    // 'calling_code' => '+971',
    // 'timezone' => 'Asia/Dubai',
    // 'flag' => 'https://flagcdn.com/ae.svg',
    // 'latitude' => 24.0000,
    // 'longitude' => 54.0000,
    // 'currency_decimals' => 2,
    // 'is_active' => true,
    // 'iban_supported' => true,
    // 'swift_supported' => true,
];

PublicUserAppSetting::
create([
'type'=>'public_settings' ,
'name'=>'country_settings',
'key' =>'country',
'ar_value'=> json_encode($uae_ar) ,
'en_value'=> json_encode($uae_en)
]);


return 'done';

  return User::all();



//   storeUserNotification(
//     5,
//     'رد جديد على المشكلة',
//     'لقد تلقيت ردًا جديدًا على المشكلة رقم #123',
//     'https://cdn-icons-png.flaticon.com/512/1827/1827373.png'
// );

$admin = Admin::first();

// $permissions = $admin->getAllPermissions();
// return response($permissions);
  $admin->assignRole(Roles::Super_Admin );
  return 'done';

// User::first()->issues()->create([
//   'subject'=>'ss',
//   'description'=>'dd',
//   'photo'=>'dd',
// ]);

 $issues = User::with('issues.replies')->first();
 return response()->json($issues);
  // Role::create(['name'=>'office manager' ,'guard'=>'web']);
  $user = office::first();
  // $user->7(['office manager']);

  return 'done';

  // $admin = Admin::first();
  // $admin->assignRole(Roles::Super_Admin );

  // return UserReport::select('user_reports.*')
  // ->join('users as employees', 'employees.id', '=', 'user_reports.userId')
  // ->orderBy('employees.firstName', $order);

  $p = ParentPermission::with('permissions')->get();
  //  $p = Permission::orderBy('name','ASC')->whereNull('parent_id')->with('subpermission')->get();

   return $p;
  $driver = Driver::first();
  // DriverRedisModel::storeDriverServices($driver->id ,[3,5,9,8] );
 return $subServices = DriverRedisModel::getDriverServices($driver->id );

  // $subServices =  $driver->getSubServicesAsArray();
  if(in_array(6 , $subServices)){
    return 'aaaaaaaaa';
  }
  return 'bbbbb';

 return false;//$driver->has_sub_service(1);

  // FleetWalletModel::addBalanceValueByBalanceStatus(BalanceStatus::$Withdrawn ,0 );
  // FleetWalletModel::moveBalance(BalanceStatus::$Available ,BalanceStatus::$Withdrawn ,500 );

  return FleetWalletRedisModel::getBalanceValueByStatus(BalanceStatus::$Available);

  // event(new HoldOrder(1));


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
    'latitude' =>  38.55
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
// $drivers = Driver::select('drivers.id', 'drivers.is_online')
//     ->whereIn('drivers.id', $driverIds)
//     ->where('drivers.is_online', true)
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

//return $drivers = Driver::select('id')->whereIn('id', [1,2,3])->where('is_online',true)->get();
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
  $notificationModel = new NotificationModel(
    'testd4s5d45s',
   'test',
   'e',
   'eee',
   'ddd',
 );

 $repo = new DriverReadRepository();
 $repo->notifyDriver( 2,  $notificationModel  );

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
