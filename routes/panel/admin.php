<?php

use Illuminate\Support\Facades\Route;
use App\Http\Services\Panel\Shared\Authorization\PanelPermission as Perm;
use App\Http\Services\Panel\Shared\Notifications\Controller\NotificationsPageController;
use App\Http\Services\Panel\Shared\Notifications\Controller\MarkNotificationsReadController;
use App\Http\Services\Panel\Home\Controller\HomeController;
use App\Http\Services\Panel\Home\Controller\HomeStatsController;
use App\Http\Services\Panel\Home\Controller\HomeLiveController;
use App\Http\Services\Panel\Home\Controller\MapDriversController;
use App\Http\Services\Panel\Admin\Controller\SwitchCountryController;
use App\Http\Services\Panel\Admin\Documents\Controller\DocumentsPageController;
use App\Http\Services\Panel\Admin\Documents\Controller\CreateDocumentController;
use App\Http\Services\Panel\Admin\Documents\Controller\StoreDocumentController;
use App\Http\Services\Panel\Admin\Documents\Controller\EditDocumentController;
use App\Http\Services\Panel\Admin\Documents\Controller\UpdateDocumentController;
use App\Http\Services\Panel\Admin\Documents\Controller\ToggleDocumentStatusController;
use App\Http\Services\Panel\Admin\Documents\Controller\ToggleDocumentRequiredController;
use App\Http\Services\Panel\Admin\Documents\Controller\DeleteDocumentController;
use App\Http\Services\Panel\Admin\Settings\Controller\SettingsPageController;
use App\Http\Services\Panel\Admin\Settings\Controller\UpdateCommissionsController;
use App\Http\Services\Panel\Admin\Settings\Controller\UpdateSystemController;
use App\Http\Services\Panel\Admin\Settings\Controller\PaymentSettingsPageController;
use App\Http\Services\Panel\Admin\Settings\Controller\SavePaymentSettingsController;
use App\Http\Services\Panel\Admin\Settings\Controller\WhatsappSettingsPageController;
use App\Http\Services\Panel\Admin\Settings\Controller\SaveWhatsappSettingsController;
use App\Http\Services\Panel\Admin\Settings\Controller\TestWhatsappController;
use App\Http\Services\Panel\Admin\Currencies\Controller\CurrenciesPageController;
use App\Http\Services\Panel\Admin\Currencies\Controller\StoreCurrencyController;
use App\Http\Services\Panel\Admin\Currencies\Controller\ToggleCurrencyController;
use App\Http\Services\Panel\Admin\Currencies\Controller\UpdateCurrencyController;
use App\Http\Services\Panel\Pricing\Controller\CorridorsPageController;
use App\Http\Services\Panel\Pricing\Controller\SaveCorridorController;
use App\Http\Services\Panel\Pricing\Controller\DeleteCorridorController;
use App\Http\Services\Panel\Shared\Wallet\Controller\RevealWalletController;
use App\Http\Services\Panel\Shared\Wallet\Controller\HideWalletController;
use App\Http\Services\Panel\Admin\Offices\Controller\OfficesPageController;
use App\Http\Services\Panel\Admin\Offices\Controller\CreateOfficeController;
use App\Http\Services\Panel\Admin\Offices\Controller\StoreOfficeController;
use App\Http\Services\Panel\Admin\Offices\Controller\ShowOfficeController;
use App\Http\Services\Panel\Admin\Offices\Controller\OfficeStatsController;
use App\Http\Services\Panel\Admin\Offices\Controller\OfficeFeedController;
use App\Http\Services\Panel\Admin\Offices\Controller\AddOfficeBalanceController;
use App\Http\Services\Panel\Admin\Offices\Controller\SettleOfficeWalletController;
use App\Http\Services\Panel\Admin\Offices\Controller\EditOfficeController;
use App\Http\Services\Panel\Admin\Offices\Controller\UpdateOfficeController;
use App\Http\Services\Panel\Admin\Offices\Controller\ToggleOfficeStatusController;
use App\Http\Services\Panel\Admin\Offices\Controller\DeleteOfficeController;
use App\Http\Services\Panel\Admin\Permissions\Controller\EditOfficePermissionsController;
use App\Http\Services\Panel\Admin\Permissions\Controller\UpdateOfficePermissionsController;
use App\Http\Services\Panel\Employees\Controller\EmployeesPageController;
use App\Http\Services\Panel\Employees\Controller\CreateEmployeeController;
use App\Http\Services\Panel\Employees\Controller\StoreEmployeeController;
use App\Http\Services\Panel\Employees\Controller\EditEmployeeController;
use App\Http\Services\Panel\Employees\Controller\UpdateEmployeeController;
use App\Http\Services\Panel\Employees\Controller\ToggleEmployeeStatusController;
use App\Http\Services\Panel\Employees\Controller\DeleteEmployeeController;
use App\Http\Services\Panel\Employees\Controller\EditEmployeePermissionsController;
use App\Http\Services\Panel\Employees\Controller\UpdateEmployeePermissionsController;
use App\Http\Services\Panel\Drivers\Controller\DriversPageController;
use App\Http\Services\Panel\Drivers\Controller\CreateDriverController;
use App\Http\Services\Panel\Drivers\Controller\StoreDriverController;
use App\Http\Services\Panel\Drivers\Controller\EditDriverController;
use App\Http\Services\Panel\Drivers\Controller\UpdateDriverController;
use App\Http\Services\Panel\Drivers\Controller\ToggleDriverStatusController;
use App\Http\Services\Panel\Drivers\Controller\DeleteDriverController;
use App\Http\Services\Panel\Drivers\Controller\ShowDriverController;
use App\Http\Services\Panel\Drivers\Controller\DriverStatsController;
use App\Http\Services\Panel\Drivers\Controller\DriverRatingsController;
use App\Http\Services\Panel\Drivers\Controller\StoreDriverDocumentController;
use App\Http\Services\Panel\Drivers\Controller\UpdateDriverDocumentStatusController;
use App\Http\Services\Panel\Drivers\Controller\DeleteDriverDocumentController;
use App\Http\Services\Panel\Vehicles\Controller\VehiclesPageController;
use App\Http\Services\Panel\Vehicles\Controller\CreateVehicleController;
use App\Http\Services\Panel\Vehicles\Controller\StoreVehicleController;
use App\Http\Services\Panel\Vehicles\Controller\EditVehicleController;
use App\Http\Services\Panel\Vehicles\Controller\UpdateVehicleController;
use App\Http\Services\Panel\Vehicles\Controller\DeleteVehicleController;
use App\Http\Services\Panel\Vehicles\Controller\EditVehicleServicesController;
use App\Http\Services\Panel\Vehicles\Controller\UpdateVehicleServicesController;
use App\Http\Services\Panel\Users\Controller\UsersPageController;
use App\Http\Services\Panel\Users\Controller\CreateUserController;
use App\Http\Services\Panel\Users\Controller\StoreUserController;
use App\Http\Services\Panel\Users\Controller\EditUserController;
use App\Http\Services\Panel\Users\Controller\UpdateUserController;
use App\Http\Services\Panel\Users\Controller\ToggleUserStatusController;
use App\Http\Services\Panel\Users\Controller\DeleteUserController;
use App\Http\Services\Panel\Bookings\Controller\BookingsPageController;
use App\Http\Services\Panel\Bookings\Controller\ShowBookingController;
use App\Http\Services\Panel\Bookings\Controller\UpdateBookingStatusController;
use App\Http\Services\Panel\Bookings\Controller\ScheduledBoardController;
use App\Http\Services\Panel\Bookings\Controller\ScheduledDataController;
use App\Http\Services\Panel\Bookings\Controller\LiveBoardController;
use App\Http\Services\Panel\Bookings\Controller\LiveSummaryController;
use App\Http\Services\Panel\Bookings\Controller\LiveCompanyController;
use App\Http\Services\Panel\Bookings\Controller\LiveFindController;
use App\Http\Services\Panel\Bookings\Controller\LiveActionController;
use App\Http\Services\Panel\OfficeBookings\Controller\CreateOfficeBookingPageController;
use App\Http\Services\Panel\OfficeBookings\Controller\StoreOfficeBookingController;
use App\Http\Services\Panel\OfficeBookings\Controller\QuoteOfficeBookingController;
use App\Http\Services\Panel\OfficeBookings\Controller\OfficeBookingsListController;
use App\Http\Services\Panel\Bookings\Controller\AssignDriverController;
use App\Http\Services\Panel\Bookings\Controller\CancelBookingController;
use App\Http\Services\Panel\Wallet\Controller\TransactionsPageController;
use App\Http\Services\Panel\Subscriptions\Controller\AssignOfficeSubscriptionController;
use App\Http\Services\Panel\Subscriptions\Controller\ShowOfficeSubscriptionController;
use App\Http\Services\Panel\Payouts\Controller\PayPayoutController;
use App\Http\Services\Panel\Payouts\Controller\RejectPayoutController;
use App\Http\Services\Panel\RiderSupport\Controller\RiderSupportPageController;
use App\Http\Services\Panel\RiderSupport\Controller\RiderSupportThreadPageController;
use App\Http\Services\Panel\RiderSupport\Controller\RiderSupportReplyController;
use App\Http\Services\Panel\RiderSupport\Controller\RiderSupportStatusController;
use App\Http\Services\Panel\RiderSupport\Controller\RatingsPageController;
use App\Http\Services\Panel\RiderSupport\Controller\FlagRatingController;
use App\Http\Services\Panel\DriverOps\Controller\DriverSafetyPageController;
use App\Http\Services\Panel\DriverOps\Controller\DriverApplicationsPageController;
use App\Http\Services\Panel\DriverOps\Controller\ReviewDriverApplicationController;
use App\Http\Services\Panel\DriverOps\Controller\DriverPresencePageController;
use App\Http\Services\Panel\DriverOps\Controller\ForceDriverOfflineController;
use App\Http\Services\Panel\Content\Controller\SiteSettingsPageController;
use App\Http\Services\Panel\Content\Controller\SaveSiteSettingsController;
use App\Http\Services\Panel\Content\Controller\FaqsPageController;
use App\Http\Services\Panel\Content\Controller\SaveFaqController;
use App\Http\Services\Panel\Content\Controller\DeleteFaqController;
use App\Http\Services\Panel\Leads\Controller\SubmissionsHubPageController;
use App\Http\Services\Panel\Leads\Controller\DriverLeadsPageController;
use App\Http\Services\Panel\Leads\Controller\UpdateDriverLeadStatusController;
use App\Http\Services\Panel\Leads\Controller\OfficeRequestsPageController;
use App\Http\Services\Panel\Leads\Controller\MarkOfficeRequestReviewedController;
use App\Http\Services\Panel\Leads\Controller\ContactMessagesPageController;
use App\Http\Services\Panel\Leads\Controller\MarkContactMessageReviewedController;
use App\Http\Services\Panel\Subscriptions\Controller\OfficeSubscriptionsPageController;
use App\Http\Services\Panel\Support\Controller\ComplaintsPageController;
use App\Http\Services\Panel\Support\Controller\UpdateComplaintStatusController;
use App\Http\Services\Panel\Support\Controller\LostItemsPageController;
use App\Http\Services\Panel\Support\Controller\UpdateLostItemStatusController;
use App\Http\Services\Panel\Support\Controller\FamilyMembersPageController;
use App\Http\Services\Panel\Corporate\Controller\CorporateInvoicesPageController;
use App\Http\Services\Panel\Corporate\Controller\UpdateCorporateInvoiceStatusController;
use App\Http\Services\Panel\Regions\Controller\RegionsBillingPageController;
use App\Http\Services\Panel\Regions\Controller\UpdateRegionBillingController;
use App\Http\Services\Panel\Admin\Countries\Controller\CountriesPageController;
use App\Http\Services\Panel\Admin\Countries\Controller\StoreCountryController;
use App\Http\Services\Panel\Admin\Countries\Controller\UpdateCountryController;
use App\Http\Services\Panel\Admin\Countries\Controller\ToggleCountryController;
use App\Http\Services\Panel\Admin\Countries\Controller\TestConnectionController;
use App\Http\Services\Panel\Admin\Countries\Controller\ProvisionCountryController;
use App\Http\Services\Panel\Payouts\Controller\PayoutsPageController;
use App\Http\Services\Panel\Reports\Controller\ReportsPageController;
use App\Http\Services\Panel\Services\Controller\ServicesPageController;
use App\Http\Services\Panel\Services\Controller\ShowServiceController;
use App\Http\Services\Panel\Services\Controller\ServiceStatsController;
use App\Http\Services\Panel\Services\Controller\ServiceFeedController;
use App\Http\Services\Panel\Services\Controller\CreateServiceController;
use App\Http\Services\Panel\Services\Controller\StoreServiceController;
use App\Http\Services\Panel\Services\Controller\EditServiceController;
use App\Http\Services\Panel\Services\Controller\UpdateServiceController;
use App\Http\Services\Panel\Services\Controller\ToggleServiceController;
use App\Http\Services\Panel\Services\Controller\DeleteServiceController;
use App\Http\Services\Panel\Services\Controller\SubServicesPageController;
use App\Http\Services\Panel\Services\Controller\CreateSubServiceController;
use App\Http\Services\Panel\Services\Controller\StoreSubServiceController;
use App\Http\Services\Panel\Services\Controller\EditSubServiceController;
use App\Http\Services\Panel\Services\Controller\UpdateSubServiceController;
use App\Http\Services\Panel\Services\Controller\ToggleSubServiceController;
use App\Http\Services\Panel\Services\Controller\DeleteSubServiceController;
use App\Http\Services\Panel\Services\Controller\EditOfficePricingController;
use App\Http\Services\Panel\Services\Controller\UpdateOfficePricingController;

/*
| In "All countries" mode the `dynamic` connection points at cross-DB UNION
| VIEWS, which MySQL cannot write to — a save came back as a raw
| "target table is not updatable" SQL error. Guarding route by route left 101 of
| 134 writes exposed, so the guard is applied to the whole group (reads pass
| through untouched) and the handful of genuinely platform-wide writes opt OUT
| explicitly below. A new tenant write is now protected by default.
*/
Route::middleware(['set-language', 'panel.country-db', 'auth:admin', 'panel.2fa', 'panel.single-shard'])
    ->group(function () {

        Route::get('/', HomeController::class)->name('home');
        Route::get('home/stats', HomeStatsController::class)->name('home.stats');
        Route::get('home/live', HomeLiveController::class)->name('home.live');
        Route::get('map/drivers', MapDriversController::class)->name('map.drivers');

        Route::get('notifications', NotificationsPageController::class)->name('notifications.index');
        Route::post('notifications/read', MarkNotificationsReadController::class)->withoutMiddleware('panel.single-shard')->name('notifications.read');

        Route::post('switch-country', SwitchCountryController::class)->withoutMiddleware('panel.single-shard')->name('switch-country');

        Route::get('offices', OfficesPageController::class)->middleware('permission:' . Perm::VIEW_OFFICE_LIST)->name('office.index');
        Route::get('offices/create', CreateOfficeController::class)->middleware('permission:' . Perm::ADD_OFFICE)->name('office.create');
        Route::post('offices', StoreOfficeController::class)->middleware('permission:' . Perm::ADD_OFFICE)->name('office.store');
        Route::get('offices/{office}', ShowOfficeController::class)->middleware('permission:' . Perm::VIEW_OFFICE_LIST)->whereNumber('office')->name('office.show');
        Route::get('offices/{office}/stats', OfficeStatsController::class)->middleware('permission:' . Perm::VIEW_OFFICE_LIST)->whereNumber('office')->name('office.stats');
        Route::get('offices/{office}/feed', OfficeFeedController::class)->middleware('permission:' . Perm::VIEW_OFFICE_LIST)->whereNumber('office')->name('office.feed');
        Route::put('offices/{office}/balance', AddOfficeBalanceController::class)->middleware('permission:' . Perm::UPDATE_OFFICE)->whereNumber('office')->name('office.balance.add');
        Route::put('offices/{office}/settle', SettleOfficeWalletController::class)->middleware('permission:' . Perm::UPDATE_OFFICE)->whereNumber('office')->name('office.wallet.settle');
        Route::get('offices/{office}/edit', EditOfficeController::class)->middleware('permission:' . Perm::UPDATE_OFFICE)->whereNumber('office')->name('office.edit');
        Route::put('offices/{office}', UpdateOfficeController::class)->middleware('permission:' . Perm::UPDATE_OFFICE)->whereNumber('office')->name('office.update');
        Route::post('offices/{office}/toggle', ToggleOfficeStatusController::class)->middleware('permission:' . Perm::UPDATE_OFFICE)->whereNumber('office')->name('office.toggle');
        Route::delete('offices/{office}', DeleteOfficeController::class)->middleware('permission:' . Perm::DELETE_OFFICE)->whereNumber('office')->name('office.destroy');
        Route::get('offices/{office}/permissions', EditOfficePermissionsController::class)->middleware('permission:' . Perm::ASSIGN_PERMISSIONS)->whereNumber('office')->name('office.permissions.edit');
        Route::put('offices/{office}/permissions', UpdateOfficePermissionsController::class)->middleware('permission:' . Perm::ASSIGN_PERMISSIONS)->whereNumber('office')->name('office.permissions.update');
        Route::get('offices/{office}/pricing', EditOfficePricingController::class)->middleware('permission:' . Perm::VIEW_SUB_SERVICE_LIST)->whereNumber('office')->name('office.pricing.edit');
        Route::put('offices/{office}/pricing', UpdateOfficePricingController::class)->middleware('permission:' . Perm::EDIT_SUB_SERVICE)->whereNumber('office')->name('office.pricing.update');
        Route::get('offices/{office}/subscription', ShowOfficeSubscriptionController::class)->middleware('permission:' . Perm::VIEW_OFFICE_LIST)->whereNumber('office')->name('office.subscription.show');
        Route::get('offices/{office}/documents', \App\Http\Services\Panel\Admin\Offices\Controller\OfficeDocumentsPageController::class)->middleware('permission:' . Perm::VIEW_OFFICE_LIST)->whereNumber('office')->name('office.documents');
        Route::post('offices/{office}/documents', \App\Http\Services\Panel\Admin\Offices\Controller\StoreOfficeDocumentController::class)->middleware('permission:' . Perm::UPDATE_OFFICE, 'panel.single-shard')->whereNumber('office')->name('office.documents.store');
        Route::put('offices/{office}/documents/{document}', \App\Http\Services\Panel\Admin\Offices\Controller\UpdateOfficeDocumentStatusController::class)->middleware('permission:' . Perm::UPDATE_OFFICE, 'panel.single-shard')->whereNumber('office')->whereNumber('document')->name('office.documents.status');
        Route::delete('offices/{office}/documents/{document}', \App\Http\Services\Panel\Admin\Offices\Controller\DeleteOfficeDocumentController::class)->middleware('permission:' . Perm::UPDATE_OFFICE, 'panel.single-shard')->whereNumber('office')->whereNumber('document')->name('office.documents.destroy');
        Route::put('offices/{office}/subscription', AssignOfficeSubscriptionController::class)->middleware('permission:' . Perm::UPDATE_OFFICE)->whereNumber('office')->name('office.subscription.update');

        Route::get('employees', EmployeesPageController::class)->middleware('permission:' . Perm::VIEW_EMPLOYEE_LIST)->name('employee.index');
        Route::get('employees/create', CreateEmployeeController::class)->middleware('permission:' . Perm::ADD_EMPLOYEE)->name('employee.create');
        Route::post('employees', StoreEmployeeController::class)->middleware('permission:' . Perm::ADD_EMPLOYEE)->name('employee.store');
        Route::get('employees/{employee}/edit', EditEmployeeController::class)->middleware('permission:' . Perm::UPDATE_EMPLOYEE)->whereNumber('employee')->name('employee.edit');
        Route::put('employees/{employee}', UpdateEmployeeController::class)->middleware('permission:' . Perm::UPDATE_EMPLOYEE)->whereNumber('employee')->name('employee.update');
        Route::post('employees/{employee}/toggle', ToggleEmployeeStatusController::class)->middleware('permission:' . Perm::UPDATE_EMPLOYEE)->whereNumber('employee')->name('employee.toggle');
        Route::delete('employees/{employee}', DeleteEmployeeController::class)->middleware('permission:' . Perm::DELETE_EMPLOYEE)->whereNumber('employee')->name('employee.destroy');
        Route::get('employees/{employee}/permissions', EditEmployeePermissionsController::class)->middleware('permission:' . Perm::ASSIGN_PERMISSIONS)->whereNumber('employee')->name('employee.permissions.edit');
        Route::put('employees/{employee}/permissions', UpdateEmployeePermissionsController::class)->middleware('permission:' . Perm::ASSIGN_PERMISSIONS)->whereNumber('employee')->name('employee.permissions.update');
        Route::post('employees/{employee}/permissions/reset', \App\Http\Services\Panel\Employees\Controller\ResetEmployeePermissionsController::class)->middleware('permission:' . Perm::ASSIGN_PERMISSIONS, 'panel.single-shard')->whereNumber('employee')->name('employee.permissions.reset');

        Route::get('drivers/export', \App\Http\Services\Panel\Drivers\Controller\ExportDriversController::class)->middleware('permission:' . Perm::VIEW_DRIVER_LIST)->name('driver.export');
        Route::get('drivers', DriversPageController::class)->middleware('permission:' . Perm::VIEW_DRIVER_LIST)->name('driver.index');
        Route::get('drivers/create', CreateDriverController::class)->middleware('permission:' . Perm::ADD_DRIVER)->name('driver.create');
        Route::post('drivers', StoreDriverController::class)->middleware('permission:' . Perm::ADD_DRIVER)->name('driver.store');
        Route::get('drivers/{driver}', ShowDriverController::class)->middleware('permission:' . Perm::VIEW_DRIVER_LIST)->whereNumber('driver')->name('driver.show');
        Route::get('drivers/{driver}/stats', DriverStatsController::class)->middleware('permission:' . Perm::VIEW_DRIVER_LIST)->whereNumber('driver')->name('driver.stats');
        Route::get('drivers/{driver}/ratings', DriverRatingsController::class)->middleware('permission:' . Perm::VIEW_DRIVER_LIST)->whereNumber('driver')->name('driver.ratings');
        Route::post('drivers/{driver}/commission', \App\Http\Services\Panel\Drivers\Controller\UpdateDriverCommissionController::class)->middleware('permission:' . Perm::EDIT_COMMISSION, 'panel.single-shard')->whereNumber('driver')->name('driver.commission');
        Route::post('drivers/{driver}/dues/settle', \App\Http\Services\Panel\Drivers\Controller\SettleDriverDuesController::class)->middleware('permission:' . Perm::VIEW_PAYMENTS, 'panel.single-shard')->whereNumber('driver')->name('driver.dues.settle');
        Route::post('drivers/{driver}/documents', StoreDriverDocumentController::class)->middleware('permission:' . Perm::EDIT_DRIVER)->whereNumber('driver')->name('driver.documents.store');
        Route::put('drivers/{driver}/documents/{document}', UpdateDriverDocumentStatusController::class)->middleware('permission:' . Perm::EDIT_DRIVER)->whereNumber('driver')->whereNumber('document')->name('driver.documents.status');
        Route::delete('drivers/{driver}/documents/{document}', DeleteDriverDocumentController::class)->middleware('permission:' . Perm::EDIT_DRIVER)->whereNumber('driver')->whereNumber('document')->name('driver.documents.destroy');

        Route::get('vehicles', VehiclesPageController::class)->middleware('permission:' . Perm::VIEW_VEHICLE_LIST)->name('vehicle.index');
        Route::get('vehicles/create', CreateVehicleController::class)->middleware('permission:' . Perm::ADD_VEHICLE)->name('vehicle.create');
        Route::post('vehicles', StoreVehicleController::class)->middleware('permission:' . Perm::ADD_VEHICLE)->name('vehicle.store');
        Route::get('vehicles/{vehicle}/edit', EditVehicleController::class)->middleware('permission:' . Perm::UPDATE_VEHICLE)->whereNumber('vehicle')->name('vehicle.edit');
        Route::put('vehicles/{vehicle}', UpdateVehicleController::class)->middleware('permission:' . Perm::UPDATE_VEHICLE)->whereNumber('vehicle')->name('vehicle.update');
        Route::delete('vehicles/{vehicle}', DeleteVehicleController::class)->middleware('permission:' . Perm::DELETE_VEHICLE)->whereNumber('vehicle')->name('vehicle.destroy');
        Route::get('vehicles/{vehicle}/services', EditVehicleServicesController::class)->middleware('permission:' . Perm::UPDATE_VEHICLE)->whereNumber('vehicle')->name('vehicle.services.edit');
        Route::put('vehicles/{vehicle}/services', UpdateVehicleServicesController::class)->middleware('permission:' . Perm::UPDATE_VEHICLE)->whereNumber('vehicle')->name('vehicle.services.update');
        Route::get('drivers/{driver}/edit', EditDriverController::class)->middleware('permission:' . Perm::EDIT_DRIVER)->whereNumber('driver')->name('driver.edit');
        Route::put('drivers/{driver}', UpdateDriverController::class)->middleware('permission:' . Perm::EDIT_DRIVER)->whereNumber('driver')->name('driver.update');
        Route::post('drivers/{driver}/toggle', ToggleDriverStatusController::class)->middleware('permission:' . Perm::EDIT_DRIVER)->whereNumber('driver')->name('driver.toggle');
        Route::post('drivers/{driver}/suspend', \App\Http\Services\Panel\Drivers\Controller\SuspendDriverController::class)->middleware('permission:' . Perm::EDIT_DRIVER, 'panel.single-shard')->whereNumber('driver')->name('driver.suspend');
        Route::delete('drivers/{driver}', DeleteDriverController::class)->middleware('permission:' . Perm::DELETE_DRIVER)->whereNumber('driver')->name('driver.destroy');

        Route::get('users', UsersPageController::class)->middleware('permission:' . Perm::VIEW_USER_LIST)->name('user.index');
        Route::get('users/create', CreateUserController::class)->middleware('permission:' . Perm::ADD_USER)->name('user.create');
        Route::post('users', StoreUserController::class)->middleware('permission:' . Perm::ADD_USER)->name('user.store');
        Route::get('users/{user}', \App\Http\Services\Panel\Users\Controller\ShowUserController::class)->middleware('permission:' . Perm::VIEW_USER_LIST)->whereNumber('user')->name('user.show');
        Route::get('users/{user}/edit', EditUserController::class)->middleware('permission:' . Perm::EDIT_USER)->whereNumber('user')->name('user.edit');
        Route::put('users/{user}', UpdateUserController::class)->middleware('permission:' . Perm::EDIT_USER)->whereNumber('user')->name('user.update');
        Route::post('users/{user}/toggle', ToggleUserStatusController::class)->middleware('permission:' . Perm::EDIT_USER)->whereNumber('user')->name('user.toggle');
        Route::delete('users/{user}', DeleteUserController::class)->middleware('permission:' . Perm::DELETE_USER)->whereNumber('user')->name('user.destroy');

        Route::get('office-bookings', OfficeBookingsListController::class)->middleware('permission:' . Perm::ORDER_HISTORY)->name('office-bookings.index');
        Route::get('office-bookings/create', CreateOfficeBookingPageController::class)->middleware('permission:' . Perm::EDIT_ORDER_STATUS)->name('office-bookings.create');
        Route::post('office-bookings/quote', QuoteOfficeBookingController::class)->middleware('permission:' . Perm::EDIT_ORDER_STATUS)->name('office-bookings.quote');
        Route::post('office-bookings', StoreOfficeBookingController::class)->middleware('permission:' . Perm::EDIT_ORDER_STATUS)->name('office-bookings.store');

        Route::get('rides', \App\Http\Services\Panel\Rides\Controller\RidesPageController::class)->middleware('permission:' . Perm::ORDER_HISTORY)->name('rides.index');
        Route::get('rides/{ride}', \App\Http\Services\Panel\Rides\Controller\ShowRideController::class)->whereNumber('ride')->middleware('permission:' . Perm::ORDER_HISTORY)->name('rides.show');
        Route::get('rides/{ride}/receipt', \App\Http\Services\Panel\Rides\Controller\RideReceiptController::class)->whereNumber('ride')->middleware('permission:' . Perm::ORDER_HISTORY)->name('rides.receipt');

        Route::get('bookings', BookingsPageController::class)->middleware('permission:' . Perm::ORDER_HISTORY)->name('booking.index');
        Route::get('bookings/scheduled', ScheduledBoardController::class)->middleware('permission:' . Perm::ORDER_HISTORY)->name('booking.scheduled');
        Route::get('bookings/scheduled/data', ScheduledDataController::class)->middleware('permission:' . Perm::ORDER_HISTORY)->name('booking.scheduled.data');
        Route::get('bookings/live', LiveBoardController::class)->middleware('permission:' . Perm::ORDER_HISTORY)->name('booking.live');
        Route::get('bookings/live/summary', LiveSummaryController::class)->middleware('permission:' . Perm::ORDER_HISTORY)->name('booking.live.summary');
        Route::get('bookings/live/company', LiveCompanyController::class)->middleware('permission:' . Perm::ORDER_HISTORY)->name('booking.live.company');
        Route::get('bookings/live/find', LiveFindController::class)->middleware('permission:' . Perm::ORDER_HISTORY)->name('booking.live.find');
        Route::post('bookings/live/{booking}/action', LiveActionController::class)->middleware('permission:' . Perm::EDIT_ORDER_STATUS)->whereNumber('booking')->name('booking.live.action');
        Route::get('bookings/{booking}', ShowBookingController::class)->middleware('permission:' . Perm::ORDER_HISTORY)->whereNumber('booking')->name('booking.show');
        Route::post('bookings/{booking}/refund', \App\Http\Services\Panel\Bookings\Controller\RefundBookingController::class)->middleware('permission:' . Perm::EDIT_COMMISSION, 'panel.single-shard')->whereNumber('booking')->name('booking.refund');
        Route::put('bookings/{booking}/status', UpdateBookingStatusController::class)->middleware('permission:' . Perm::EDIT_ORDER_STATUS)->whereNumber('booking')->name('booking.status.update');
        Route::post('bookings/rides/{ride}/assign', \App\Http\Services\Panel\Bookings\Controller\AssignRideDriverController::class)->middleware('permission:' . Perm::EDIT_ORDER_STATUS)->whereNumber('ride')->name('booking.rides.assign');
        Route::post('bookings/rides/{ride}/accept', \App\Http\Services\Panel\Bookings\Controller\AcceptRideController::class)->middleware('permission:' . Perm::EDIT_ORDER_STATUS)->whereNumber('ride')->name('booking.rides.accept');
        Route::post('bookings/rides/{ride}/cancel', \App\Http\Services\Panel\Bookings\Controller\CancelRideController::class)->middleware('permission:' . Perm::EDIT_ORDER_STATUS)->whereNumber('ride')->name('booking.rides.cancel');
        Route::post('bookings/{booking}/assign', AssignDriverController::class)->middleware('permission:' . Perm::EDIT_ORDER_STATUS)->whereNumber('booking')->name('booking.assign');
        Route::post('bookings/{booking}/cancel', CancelBookingController::class)->middleware('permission:' . Perm::EDIT_ORDER_STATUS)->whereNumber('booking')->name('booking.cancel');

        Route::get('transactions/export', \App\Http\Services\Panel\Wallet\Controller\ExportTransactionsController::class)->middleware('permission:' . Perm::VIEW_COMMISSION)->name('wallet.transactions.export');
        Route::get('transactions', TransactionsPageController::class)->middleware('permission:' . Perm::VIEW_COMMISSION)->name('wallet.transactions');

        Route::get('reports/fleet', ReportsPageController::class)->middleware('permission:' . Perm::VIEW_COMMISSION)->name('reports.fleet');

        Route::get('rider-support', RiderSupportPageController::class)->name('rider-support.index');
        Route::get('rider-support/{ticket}', RiderSupportThreadPageController::class)->whereNumber('ticket')->name('rider-support.show');
        Route::post('rider-support/{ticket}/reply', RiderSupportReplyController::class)->whereNumber('ticket')->name('rider-support.reply');
        Route::post('rider-support/{ticket}/status', RiderSupportStatusController::class)->whereNumber('ticket')->name('rider-support.status');
        Route::post('rider-support/{ticket}/escalate', \App\Http\Services\Panel\RiderSupport\Controller\RiderSupportEscalateController::class)->whereNumber('ticket')->name('rider-support.escalate');
        Route::get('ride-ratings', RatingsPageController::class)->name('ride-ratings.index');
        Route::post('ride-ratings/{rating}/flag', FlagRatingController::class)->whereNumber('rating')->middleware('panel.single-shard')->name('ride-ratings.flag');

        Route::get('announcements', \App\Http\Services\Panel\Announcements\Controller\PushComposerPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('announcements.index');
        Route::post('announcements/send', \App\Http\Services\Panel\Announcements\Controller\SendBroadcastPushController::class)->middleware('permission:' . Perm::VIEW_SETTINGS, 'panel.single-shard')->name('announcements.send');

        Route::get('driver-safety', DriverSafetyPageController::class)->name('driver-safety.index');
        Route::post('driver-safety/{event}', \App\Http\Services\Panel\DriverOps\Controller\UpdateDriverSafetyStatusController::class)->whereNumber('event')->middleware('panel.single-shard')->name('driver-safety.status');
        Route::get('driver-presence', DriverPresencePageController::class)->middleware('permission:' . Perm::VIEW_DRIVER_LIST)->name('driver-presence.index');
        Route::post('driver-presence/{driver}/offline', ForceDriverOfflineController::class)->whereNumber('driver')->middleware('permission:' . Perm::EDIT_DRIVER, 'panel.single-shard')->name('driver-presence.offline');
        Route::get('driver-applications', DriverApplicationsPageController::class)->name('driver-applications.index');
        Route::post('driver-applications/{application}/review', ReviewDriverApplicationController::class)->whereNumber('application')->middleware('panel.single-shard')->name('driver-applications.review');

        Route::get('payouts', PayoutsPageController::class)->middleware('permission:' . Perm::VIEW_COMMISSION)->name('payouts.index');
        Route::post('payouts/{payout}/pay', PayPayoutController::class)->middleware('permission:' . Perm::EDIT_COMMISSION)->whereNumber('payout')->name('payouts.pay');
        Route::post('payouts/{payout}/reject', RejectPayoutController::class)->middleware('permission:' . Perm::EDIT_COMMISSION)->whereNumber('payout')->name('payouts.reject');

        Route::get('services', ServicesPageController::class)->middleware('permission:' . Perm::VIEW_SERVICE_LIST)->name('service.index');
        Route::get('services/create', CreateServiceController::class)->middleware('permission:' . Perm::ADD_SERVICE)->name('service.create');
        Route::post('services', StoreServiceController::class)->middleware('permission:' . Perm::ADD_SERVICE)->name('service.store');
        Route::get('services/{service}', ShowServiceController::class)->middleware('permission:' . Perm::VIEW_SERVICE_LIST)->whereNumber('service')->name('service.show');
        Route::get('services/{service}/stats', ServiceStatsController::class)->middleware('permission:' . Perm::VIEW_SERVICE_LIST)->whereNumber('service')->name('service.stats');
        Route::get('services/{service}/feed', ServiceFeedController::class)->middleware('permission:' . Perm::VIEW_SERVICE_LIST)->whereNumber('service')->name('service.feed');
        Route::get('services/{service}/edit', EditServiceController::class)->middleware('permission:' . Perm::EDIT_SERVICE)->whereNumber('service')->name('service.edit');
        Route::put('services/{service}', UpdateServiceController::class)->middleware('permission:' . Perm::EDIT_SERVICE)->whereNumber('service')->name('service.update');
        Route::post('services/{service}/toggle', ToggleServiceController::class)->middleware('permission:' . Perm::EDIT_SERVICE)->whereNumber('service')->name('service.toggle');
        Route::delete('services/{service}', DeleteServiceController::class)->middleware('permission:' . Perm::DELETE_SERVICE)->whereNumber('service')->name('service.destroy');

        Route::get('services/{service}/sub-services', SubServicesPageController::class)->middleware('permission:' . Perm::VIEW_SUB_SERVICE_LIST)->whereNumber('service')->name('service.sub.index');
        Route::get('services/{service}/sub-services/create', CreateSubServiceController::class)->middleware('permission:' . Perm::ADD_SUB_SERVICE)->whereNumber('service')->name('service.sub.create');
        Route::post('services/{service}/sub-services', StoreSubServiceController::class)->middleware('permission:' . Perm::ADD_SUB_SERVICE)->whereNumber('service')->name('service.sub.store');
        Route::get('services/{service}/sub-services/{subService}/edit', EditSubServiceController::class)->middleware('permission:' . Perm::EDIT_SUB_SERVICE)->whereNumber('service')->whereNumber('subService')->name('service.sub.edit');
        Route::put('services/{service}/sub-services/{subService}', UpdateSubServiceController::class)->middleware('permission:' . Perm::EDIT_SUB_SERVICE)->whereNumber('service')->whereNumber('subService')->name('service.sub.update');
        Route::post('services/{service}/sub-services/{subService}/toggle', ToggleSubServiceController::class)->middleware('permission:' . Perm::EDIT_SUB_SERVICE)->whereNumber('service')->whereNumber('subService')->name('service.sub.toggle');
        Route::delete('services/{service}/sub-services/{subService}', DeleteSubServiceController::class)->middleware('permission:' . Perm::DELETE_SUB_SERVICE)->whereNumber('service')->whereNumber('subService')->name('service.sub.destroy');

        Route::post('wallet/reveal', RevealWalletController::class)->withoutMiddleware('panel.single-shard')->name('wallet.reveal');
        Route::post('wallet/hide', HideWalletController::class)->withoutMiddleware('panel.single-shard')->name('wallet.hide');

        Route::get('security', \App\Http\Services\Panel\Security\Controller\SecurityPageController::class)->name('security.index');
        Route::post('security/two-factor', \App\Http\Services\Panel\Security\Controller\StartTwoFactorController::class)->withoutMiddleware('panel.single-shard')->name('security.two-factor.start');
        Route::post('security/two-factor/confirm', \App\Http\Services\Panel\Security\Controller\ConfirmTwoFactorController::class)->withoutMiddleware('panel.single-shard')->name('security.two-factor.confirm');
        Route::post('security/two-factor/disable', \App\Http\Services\Panel\Security\Controller\DisableTwoFactorController::class)->withoutMiddleware('panel.single-shard')->name('security.two-factor.disable');

        Route::get('settings/security', \App\Http\Services\Panel\Admin\Security\Controller\StaffSecurityPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('settings.security');
        Route::post('settings/security', \App\Http\Services\Panel\Admin\Security\Controller\SaveSecurityPolicyController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('settings.security.save');
        Route::post('settings/security/{record}/reset', \App\Http\Services\Panel\Admin\Security\Controller\ResetStaffTwoFactorController::class)->whereNumber('record')->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('settings.security.reset');

        Route::get('document-types', DocumentsPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('document.index');
        Route::get('document-types/create', CreateDocumentController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('document.create');
        Route::post('document-types', StoreDocumentController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('document.store');
        Route::get('document-types/{document}/edit', EditDocumentController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->whereNumber('document')->name('document.edit');
        Route::put('document-types/{document}', UpdateDocumentController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->whereNumber('document')->name('document.update');
        Route::post('document-types/{document}/toggle', ToggleDocumentStatusController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->whereNumber('document')->name('document.toggle');
        Route::post('document-types/{document}/toggle-required', ToggleDocumentRequiredController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->whereNumber('document')->name('document.toggle-required');
        Route::delete('document-types/{document}', DeleteDocumentController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->whereNumber('document')->name('document.destroy');

        Route::get('app-status', \App\Http\Services\Panel\Admin\AppStatus\Controller\AppStatusPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('app-status.index');
        Route::post('app-status', \App\Http\Services\Panel\Admin\AppStatus\Controller\SaveAppStatusController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('app-status.save');

        Route::get('legal', \App\Http\Services\Panel\Admin\Legal\Controller\LegalContentPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('legal.index');
        Route::post('legal', \App\Http\Services\Panel\Admin\Legal\Controller\SaveLegalContentController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('legal.save');

        Route::get('site-settings', SiteSettingsPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('settings.site');
        Route::post('site-settings', SaveSiteSettingsController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('settings.site.save');
        Route::get('faqs', FaqsPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('faqs.index');
        Route::post('faqs', SaveFaqController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('faqs.save');
        Route::delete('faqs/{faq}', DeleteFaqController::class)->whereNumber('faq')->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('faqs.delete');

        Route::get('complaints', ComplaintsPageController::class)->middleware('permission:' . Perm::VIEW_TICKETS)->name('complaints.index');
        Route::post('complaints/{complaint}/status', UpdateComplaintStatusController::class)->whereNumber('complaint')->middleware('permission:' . Perm::VIEW_TICKETS)->name('complaints.status');
        Route::get('lost-items', LostItemsPageController::class)->middleware('permission:' . Perm::VIEW_TICKETS)->name('lost-items.index');
        Route::post('lost-items/{item}/status', UpdateLostItemStatusController::class)->whereNumber('item')->middleware('permission:' . Perm::VIEW_TICKETS)->name('lost-items.status');
        Route::post('lost-items/{item}/match', \App\Http\Services\Panel\Support\Controller\ConfirmLostItemMatchController::class)->whereNumber('item')->middleware('permission:' . Perm::VIEW_TICKETS)->name('lost-items.match');
        Route::get('family-members', FamilyMembersPageController::class)->middleware('permission:' . Perm::VIEW_TICKETS)->name('family-members.index');
        Route::get('corporate/invoices', CorporateInvoicesPageController::class)->middleware('permission:' . Perm::VIEW_PAYMENTS)->name('corporate.invoices');
        Route::post('corporate/invoices/{invoice}/status', UpdateCorporateInvoiceStatusController::class)->whereNumber('invoice')->middleware('permission:' . Perm::VIEW_PAYMENTS)->name('corporate.invoices.status');

        Route::get('subscriptions', OfficeSubscriptionsPageController::class)->middleware('permission:' . Perm::VIEW_OFFICE_LIST)->name('subscriptions.index');
        Route::post('subscriptions/sync', \App\Http\Services\Panel\Subscriptions\Controller\SyncSubscriptionsController::class)->middleware('permission:' . Perm::UPDATE_OFFICE, 'panel.single-shard')->name('subscriptions.sync');

        Route::get('overage-invoices', \App\Http\Services\Panel\Subscriptions\Controller\OverageInvoicesPageController::class)->middleware('permission:' . Perm::VIEW_PAYMENTS)->name('overage-invoices.index');
        Route::get('overage-invoices/export', \App\Http\Services\Panel\Subscriptions\Controller\ExportOverageInvoicesController::class)->middleware('permission:' . Perm::VIEW_PAYMENTS)->name('overage-invoices.export');
        Route::post('overage-invoices/{ref}/collect', \App\Http\Services\Panel\Subscriptions\Controller\MarkOverageCollectedController::class)->middleware('permission:' . Perm::VIEW_PAYMENTS, 'panel.single-shard')->name('overage-invoices.collect');

        Route::get('plans', \App\Http\Services\Panel\Admin\Plans\Controller\PlansPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('plans.index');
        Route::get('plans/create', \App\Http\Services\Panel\Admin\Plans\Controller\PlanFormController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('plans.create');
        Route::post('plans', \App\Http\Services\Panel\Admin\Plans\Controller\SavePlanController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('plans.store');
        Route::get('plans/{plan}/edit', \App\Http\Services\Panel\Admin\Plans\Controller\PlanFormController::class)->whereNumber('plan')->middleware('permission:' . Perm::VIEW_SETTINGS)->name('plans.edit');
        Route::put('plans/{plan}', \App\Http\Services\Panel\Admin\Plans\Controller\SavePlanController::class)->whereNumber('plan')->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('plans.update');
        Route::post('plans/{plan}/toggle', \App\Http\Services\Panel\Admin\Plans\Controller\TogglePlanController::class)->whereNumber('plan')->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('plans.toggle');

        Route::get('regions/billing', RegionsBillingPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('regions.billing');
        Route::post('regions/{node}/billing', UpdateRegionBillingController::class)->whereNumber('node')->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('regions.billing.update');

        Route::get('countries', CountriesPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('countries.index');
        Route::post('countries', StoreCountryController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('countries.store');
        Route::post('countries/test-connection', TestConnectionController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('countries.test');
        Route::put('countries/{node}', UpdateCountryController::class)->whereNumber('node')->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('countries.update');
        Route::post('countries/{node}/toggle', ToggleCountryController::class)->whereNumber('node')->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('countries.toggle');
        Route::post('countries/{node}/provision', ProvisionCountryController::class)->whereNumber('node')->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('countries.provision');

        Route::get('leads', SubmissionsHubPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('leads.hub');
        Route::get('leads/drivers', DriverLeadsPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('leads.drivers');
        Route::post('leads/drivers/{application}/status', UpdateDriverLeadStatusController::class)->whereNumber('application')->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('leads.drivers.status');
        Route::get('leads/offices', OfficeRequestsPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('leads.offices');
        Route::post('leads/offices/{request}/review', MarkOfficeRequestReviewedController::class)->whereNumber('request')->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('leads.offices.review');
        Route::post('leads/offices/{officeRequest}/decide', \App\Http\Services\Panel\Leads\Controller\ApproveOfficeRequestController::class)->whereNumber('officeRequest')->middleware('permission:' . Perm::VIEW_SETTINGS, 'panel.single-shard')->name('leads.offices.decide');
        Route::get('leads/contacts', ContactMessagesPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('leads.contacts');
        Route::post('leads/contacts/{message}/review', MarkContactMessageReviewedController::class)->whereNumber('message')->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('leads.contacts.review');
        Route::get('audit-log', \App\Http\Services\Panel\Admin\Audit\Controller\AuditLogPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('audit-log.index');
        Route::get('ops-health', \App\Http\Services\Panel\Admin\Ops\Controller\OpsHealthPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('ops.index');
        Route::post('ops-health/retry', \App\Http\Services\Panel\Admin\Ops\Controller\RetryFailedJobsController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('ops.retry');
        Route::get('notification-templates', \App\Http\Services\Panel\Admin\NotificationTemplates\Controller\NotificationTemplatesPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('notification-templates.index');
        Route::get('notification-templates/{key}', \App\Http\Services\Panel\Admin\NotificationTemplates\Controller\EditNotificationTemplateController::class)->where('key', '[a-z0-9_]+')->middleware('permission:' . Perm::VIEW_SETTINGS)->name('notification-templates.edit');
        Route::post('notification-templates/{key}', \App\Http\Services\Panel\Admin\NotificationTemplates\Controller\SaveNotificationTemplateController::class)->where('key', '[a-z0-9_]+')->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('notification-templates.save');
        Route::get('settings', SettingsPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('settings.index');
        Route::put('settings/commissions', UpdateCommissionsController::class)->middleware('permission:' . Perm::EDIT_COMMISSION)->withoutMiddleware('panel.single-shard')->name('settings.commissions.update');
        Route::put('settings/system', UpdateSystemController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('settings.system.update');
        Route::get('settings/payments', PaymentSettingsPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('settings.payments');
        Route::post('settings/payments', SavePaymentSettingsController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('settings.payments.save');
        Route::get('settings/whatsapp', WhatsappSettingsPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('settings.whatsapp');
        Route::post('settings/whatsapp', SaveWhatsappSettingsController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('settings.whatsapp.save');
        Route::post('settings/whatsapp/test', TestWhatsappController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->withoutMiddleware('panel.single-shard')->name('settings.whatsapp.test');

        Route::get('cities', \App\Http\Services\Panel\Admin\Cities\Controller\CitiesPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('cities.index');
        Route::post('cities', \App\Http\Services\Panel\Admin\Cities\Controller\StoreCityController::class)->middleware('permission:' . Perm::VIEW_SETTINGS, 'panel.single-shard')->name('cities.store');
        Route::post('cities/import', \App\Http\Services\Panel\Admin\Cities\Controller\ImportCitiesController::class)->middleware('permission:' . Perm::VIEW_SETTINGS, 'panel.single-shard')->name('cities.import');
        Route::delete('cities/{city}', \App\Http\Services\Panel\Admin\Cities\Controller\DeleteCityController::class)->whereNumber('city')->middleware('permission:' . Perm::VIEW_SETTINGS, 'panel.single-shard')->name('cities.delete');

        Route::get('pricing/corridors', CorridorsPageController::class)->middleware('permission:' . Perm::VIEW_SUB_SERVICE_LIST)->name('pricing.corridors.index');
        Route::post('pricing/corridors', SaveCorridorController::class)->middleware('permission:' . Perm::EDIT_SUB_SERVICE, 'panel.single-shard')->name('pricing.corridors.save');
        Route::delete('pricing/corridors/{route}', DeleteCorridorController::class)->whereNumber('route')->middleware('permission:' . Perm::EDIT_SUB_SERVICE, 'panel.single-shard')->name('pricing.corridors.delete');

        Route::get('cancellation-reasons', \App\Http\Services\Panel\Admin\CancellationReasons\Controller\CancellationReasonsPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('cancellation-reasons.index');
        Route::post('cancellation-reasons', \App\Http\Services\Panel\Admin\CancellationReasons\Controller\SaveCancellationReasonController::class)->middleware('permission:' . Perm::VIEW_SETTINGS, 'panel.single-shard')->name('cancellation-reasons.store');
        Route::put('cancellation-reasons/{reason}', \App\Http\Services\Panel\Admin\CancellationReasons\Controller\SaveCancellationReasonController::class)->whereNumber('reason')->middleware('permission:' . Perm::VIEW_SETTINGS, 'panel.single-shard')->name('cancellation-reasons.update');
        Route::post('cancellation-reasons/{reason}/toggle', \App\Http\Services\Panel\Admin\CancellationReasons\Controller\ToggleCancellationReasonController::class)->whereNumber('reason')->middleware('permission:' . Perm::VIEW_SETTINGS, 'panel.single-shard')->name('cancellation-reasons.toggle');

        Route::get('referrals', \App\Http\Services\Panel\Admin\Growth\Controller\ReferralsPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('referrals.index');
        Route::post('referrals', \App\Http\Services\Panel\Admin\Growth\Controller\SaveReferralSettingsController::class)->middleware('permission:' . Perm::VIEW_SETTINGS, 'panel.single-shard')->name('referrals.save');
        Route::get('incentives', \App\Http\Services\Panel\Admin\Growth\Controller\IncentivesPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('incentives.index');
        Route::post('incentives', \App\Http\Services\Panel\Admin\Growth\Controller\SaveIncentiveController::class)->middleware('permission:' . Perm::VIEW_SETTINGS, 'panel.single-shard')->name('incentives.store');
        Route::put('incentives/{incentive}', \App\Http\Services\Panel\Admin\Growth\Controller\SaveIncentiveController::class)->whereNumber('incentive')->middleware('permission:' . Perm::VIEW_SETTINGS, 'panel.single-shard')->name('incentives.update');
        Route::post('incentives/{incentive}/toggle', \App\Http\Services\Panel\Admin\Growth\Controller\ToggleIncentiveController::class)->whereNumber('incentive')->middleware('permission:' . Perm::VIEW_SETTINGS, 'panel.single-shard')->name('incentives.toggle');

        Route::get('rating-tags', \App\Http\Services\Panel\Admin\RatingTags\Controller\RatingTagsPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('rating-tags.index');
        Route::post('rating-tags', \App\Http\Services\Panel\Admin\RatingTags\Controller\SaveRatingTagController::class)->middleware('permission:' . Perm::VIEW_SETTINGS, 'panel.single-shard')->name('rating-tags.store');
        Route::put('rating-tags/{tag}', \App\Http\Services\Panel\Admin\RatingTags\Controller\SaveRatingTagController::class)->whereNumber('tag')->middleware('permission:' . Perm::VIEW_SETTINGS, 'panel.single-shard')->name('rating-tags.update');
        Route::post('rating-tags/{tag}/toggle', \App\Http\Services\Panel\Admin\RatingTags\Controller\ToggleRatingTagController::class)->whereNumber('tag')->middleware('permission:' . Perm::VIEW_SETTINGS, 'panel.single-shard')->name('rating-tags.toggle');

        Route::get('vehicle-brands', \App\Http\Services\Panel\Admin\VehicleBrands\Controller\VehicleBrandsPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('vehicle-brands.index');
        Route::post('vehicle-brands', \App\Http\Services\Panel\Admin\VehicleBrands\Controller\SaveVehicleBrandController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('vehicle-brands.store');
        Route::put('vehicle-brands/{brand}', \App\Http\Services\Panel\Admin\VehicleBrands\Controller\SaveVehicleBrandController::class)->whereNumber('brand')->middleware('permission:' . Perm::VIEW_SETTINGS)->name('vehicle-brands.update');
        Route::post('vehicle-brands/{brand}/toggle', \App\Http\Services\Panel\Admin\VehicleBrands\Controller\ToggleVehicleBrandController::class)->whereNumber('brand')->middleware('permission:' . Perm::VIEW_SETTINGS)->name('vehicle-brands.toggle');

        Route::get('vehicle-catalog', \App\Http\Services\Panel\Admin\VehicleCatalog\Controller\VehicleCatalogPageController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('vehicle-catalog.index');
        Route::post('vehicle-models', \App\Http\Services\Panel\Admin\VehicleCatalog\Controller\SaveVehicleModelController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('vehicle-models.store');
        Route::put('vehicle-models/{model}', \App\Http\Services\Panel\Admin\VehicleCatalog\Controller\SaveVehicleModelController::class)->whereNumber('model')->middleware('permission:' . Perm::VIEW_SETTINGS)->name('vehicle-models.update');
        Route::post('vehicle-colors', \App\Http\Services\Panel\Admin\VehicleCatalog\Controller\SaveVehicleColorController::class)->middleware('permission:' . Perm::VIEW_SETTINGS)->name('vehicle-colors.store');
        Route::put('vehicle-colors/{color}', \App\Http\Services\Panel\Admin\VehicleCatalog\Controller\SaveVehicleColorController::class)->whereNumber('color')->middleware('permission:' . Perm::VIEW_SETTINGS)->name('vehicle-colors.update');
        Route::post('vehicle-catalog/{type}/{id}/toggle', \App\Http\Services\Panel\Admin\VehicleCatalog\Controller\ToggleVehicleCatalogEntryController::class)->whereIn('type', ['model', 'color'])->whereNumber('id')->middleware('permission:' . Perm::VIEW_SETTINGS)->name('vehicle-catalog.toggle');

        Route::get('coupons', \App\Http\Services\Panel\Admin\Coupons\Controller\CouponsPageController::class)->middleware('permission:' . Perm::VIEW_COUPON_LIST)->name('coupons.index');
        Route::post('coupons', \App\Http\Services\Panel\Admin\Coupons\Controller\StoreCouponController::class)->middleware('permission:' . Perm::VIEW_COUPON_LIST, 'panel.single-shard')->name('coupons.store');
        Route::post('coupons/{coupon}/toggle', \App\Http\Services\Panel\Admin\Coupons\Controller\ToggleCouponController::class)->whereNumber('coupon')->middleware('permission:' . Perm::VIEW_COUPON_LIST, 'panel.single-shard')->name('coupons.toggle');
        Route::delete('coupons/{coupon}', \App\Http\Services\Panel\Admin\Coupons\Controller\DeleteCouponController::class)->whereNumber('coupon')->middleware('permission:' . Perm::VIEW_COUPON_LIST, 'panel.single-shard')->name('coupons.delete');

        Route::get('currencies', CurrenciesPageController::class)->middleware('permission:' . Perm::MANAGE_CURRENCIES)->name('currencies.index');
        Route::post('currencies', StoreCurrencyController::class)->middleware('permission:' . Perm::MANAGE_CURRENCIES)->withoutMiddleware('panel.single-shard')->name('currencies.store');
        Route::put('currencies/{currency}', UpdateCurrencyController::class)->middleware('permission:' . Perm::MANAGE_CURRENCIES)->withoutMiddleware('panel.single-shard')->name('currencies.update');
        Route::post('currencies/{currency}/toggle', ToggleCurrencyController::class)->middleware('permission:' . Perm::MANAGE_CURRENCIES)->withoutMiddleware('panel.single-shard')->name('currencies.toggle');

    });
