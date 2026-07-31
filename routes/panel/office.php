<?php

use Illuminate\Support\Facades\Route;
use App\Http\Services\Panel\Shared\Authorization\PanelPermission as Perm;
use App\Http\Services\Panel\Shared\Notifications\Controller\NotificationsPageController;
use App\Http\Services\Panel\Shared\Notifications\Controller\MarkNotificationsReadController;
use App\Http\Services\Panel\Home\Controller\HomeController;
use App\Http\Services\Panel\Home\Controller\HomeStatsController;
use App\Http\Services\Panel\Home\Controller\HomeLiveController;
use App\Http\Services\Panel\Home\Controller\MapDriversController;
use App\Http\Services\Panel\Shared\Wallet\Controller\RevealWalletController;
use App\Http\Services\Panel\Shared\Wallet\Controller\HideWalletController;
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
use App\Http\Services\Panel\Bookings\Controller\SettleBookingController;
use App\Http\Services\Panel\Dispatch\Controller\CandidatesController;
use App\Http\Services\Panel\Dispatch\Controller\OfferWaveController;
use App\Http\Services\Panel\Payouts\Controller\RequestOfficePayoutController;
use App\Http\Services\Panel\RiderSupport\Controller\RiderSupportPageController;
use App\Http\Services\Panel\RiderSupport\Controller\RiderSupportThreadPageController;
use App\Http\Services\Panel\RiderSupport\Controller\RiderSupportReplyController;
use App\Http\Services\Panel\RiderSupport\Controller\RatingsPageController;
use App\Http\Services\Panel\RiderSupport\Controller\FlagRatingController;
use App\Http\Services\Panel\DriverOps\Controller\DriverSafetyPageController;
use App\Http\Services\Panel\DriverOps\Controller\DriverApplicationsPageController;
use App\Http\Services\Panel\DriverOps\Controller\ReviewDriverApplicationController;
use App\Http\Services\Panel\DriverOps\Controller\DriverPresencePageController;
use App\Http\Services\Panel\DriverOps\Controller\ForceDriverOfflineController;
use App\Http\Services\Panel\Payouts\Controller\PayoutsPageController;
use App\Http\Services\Panel\Reports\Controller\ReportsPageController;
use App\Http\Services\Panel\Subscriptions\Controller\SubscriptionPageController;
use App\Http\Services\Panel\Subscriptions\Controller\StartCheckoutController;
use App\Http\Services\Panel\Pricing\Controller\CorridorsPageController;
use App\Http\Services\Panel\Pricing\Controller\SaveCorridorController;
use App\Http\Services\Panel\Pricing\Controller\DeleteCorridorController;
use App\Http\Services\Panel\Chat\Controller\OfficeChatPageController;
use App\Http\Services\Panel\Chat\Controller\OfficeChatThreadPageController;
use App\Http\Services\Panel\Chat\Controller\OfficeChatReplyController;

// Same rule as the admin group: every write is single-country by default (see
// the note in routes/panel/admin.php); session-only toggles opt out below.
Route::middleware(['set-language', 'panel.country-db', 'auth:office', 'panel.2fa', 'panel.single-shard'])
    ->group(function () {

        Route::get('/', HomeController::class)->name('home');
        Route::get('home/stats', HomeStatsController::class)->name('home.stats');
        Route::get('home/live', HomeLiveController::class)->name('home.live');
        Route::get('map/drivers', MapDriversController::class)->name('map.drivers');

        Route::get('notifications', NotificationsPageController::class)->name('notifications.index');
        Route::post('notifications/read', MarkNotificationsReadController::class)->withoutMiddleware('panel.single-shard')->name('notifications.read');

        Route::post('wallet/reveal', RevealWalletController::class)->withoutMiddleware('panel.single-shard')->name('wallet.reveal');
        Route::post('wallet/hide', HideWalletController::class)->withoutMiddleware('panel.single-shard')->name('wallet.hide');

        Route::get('security', \App\Http\Services\Panel\Security\Controller\SecurityPageController::class)->name('security.index');
        Route::post('security/two-factor', \App\Http\Services\Panel\Security\Controller\StartTwoFactorController::class)->withoutMiddleware('panel.single-shard')->name('security.two-factor.start');
        Route::post('security/two-factor/confirm', \App\Http\Services\Panel\Security\Controller\ConfirmTwoFactorController::class)->withoutMiddleware('panel.single-shard')->name('security.two-factor.confirm');
        Route::post('security/two-factor/disable', \App\Http\Services\Panel\Security\Controller\DisableTwoFactorController::class)->withoutMiddleware('panel.single-shard')->name('security.two-factor.disable');

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
        Route::post('bookings/{booking}/settle', SettleBookingController::class)->middleware('permission:' . Perm::EDIT_ORDER_STATUS)->whereNumber('booking')->name('booking.settle');
        Route::post('bookings/{booking}/offer', OfferWaveController::class)->middleware('permission:' . Perm::EDIT_ORDER_STATUS)->whereNumber('booking')->name('booking.offer');

        // Office side of the RideBooking fixed-trip flow (distinct from the
        // legacy Order routes above): accept / decline a rider's offer, assign a
        // driver. Namespaced under fixed/ so the two engines never collide.
        Route::post('fixed/{booking}/accept', [\App\Http\Services\Panel\FixedBookings\Controller\OfficeFixedBookingController::class, 'accept'])->middleware('permission:' . Perm::EDIT_ORDER_STATUS)->whereNumber('booking')->name('fixed.accept');
        Route::post('fixed/{booking}/decline', [\App\Http\Services\Panel\FixedBookings\Controller\OfficeFixedBookingController::class, 'decline'])->middleware('permission:' . Perm::EDIT_ORDER_STATUS)->whereNumber('booking')->name('fixed.decline');
        Route::post('fixed/{booking}/assign-driver', [\App\Http\Services\Panel\FixedBookings\Controller\OfficeFixedBookingController::class, 'assignDriver'])->middleware('permission:' . Perm::EDIT_ORDER_STATUS)->whereNumber('booking')->name('fixed.assign');
        Route::get('dispatch/candidates', CandidatesController::class)->middleware('permission:' . Perm::ORDER_HISTORY)->name('dispatch.candidates');

        Route::get('transactions/export', \App\Http\Services\Panel\Wallet\Controller\ExportTransactionsController::class)->middleware('permission:' . Perm::VIEW_COMMISSION)->name('wallet.transactions.export');
        Route::get('transactions', TransactionsPageController::class)->middleware('permission:' . Perm::VIEW_COMMISSION)->name('wallet.transactions');

        Route::get('subscription', SubscriptionPageController::class)->name('subscription.show');
        Route::post('subscription/checkout', StartCheckoutController::class)->name('subscription.checkout');
        Route::post('subscription/trial', \App\Http\Services\Panel\Subscriptions\Controller\StartTrialController::class)->name('subscription.trial');
        Route::get('reports/summary', ReportsPageController::class)->middleware('permission:' . Perm::VIEW_COMMISSION)->name('reports.summary');

        Route::get('payouts', PayoutsPageController::class)->middleware('permission:' . Perm::VIEW_COMMISSION)->name('payouts.index');
        Route::post('payouts', RequestOfficePayoutController::class)->middleware('permission:' . Perm::VIEW_COMMISSION)->name('payouts.request');

        Route::get('services', \App\Http\Services\Panel\Services\Controller\MyServicesPageController::class)->middleware('permission:' . Perm::VIEW_SUB_SERVICE_LIST)->name('services.mine');
        Route::put('services', \App\Http\Services\Panel\Services\Controller\UpdateMyServicesController::class)->middleware('permission:' . Perm::EDIT_SUB_SERVICE, 'panel.single-shard')->name('services.mine.update');

        Route::get('commission', \App\Http\Services\Panel\Commissions\Controller\OfficeCommissionPageController::class)->middleware('permission:' . Perm::VIEW_COMMISSION)->name('commission.index');
        Route::put('commission', \App\Http\Services\Panel\Commissions\Controller\UpdateOfficeCommissionController::class)->middleware('permission:' . Perm::EDIT_COMMISSION, 'panel.single-shard')->name('commission.update');

        Route::get('pricing/corridors', CorridorsPageController::class)->middleware('permission:' . Perm::VIEW_SUB_SERVICE_LIST)->name('pricing.corridors.index');
        Route::post('pricing/corridors', SaveCorridorController::class)->middleware('permission:' . Perm::EDIT_SUB_SERVICE, 'panel.single-shard')->name('pricing.corridors.save');
        Route::delete('pricing/corridors/{route}', DeleteCorridorController::class)->whereNumber('route')->middleware('permission:' . Perm::EDIT_SUB_SERVICE, 'panel.single-shard')->name('pricing.corridors.delete');

        Route::get('rider-support', RiderSupportPageController::class)->name('rider-support.index');
        Route::get('rider-support/{ticket}', RiderSupportThreadPageController::class)->whereNumber('ticket')->name('rider-support.show');
        Route::post('rider-support/{ticket}/reply', RiderSupportReplyController::class)->whereNumber('ticket')->name('rider-support.reply');
        Route::post('rider-support/{ticket}/status', \App\Http\Services\Panel\RiderSupport\Controller\RiderSupportStatusController::class)->whereNumber('ticket')->name('rider-support.status');
        Route::post('rider-support/{ticket}/escalate', \App\Http\Services\Panel\RiderSupport\Controller\RiderSupportEscalateController::class)->whereNumber('ticket')->name('rider-support.escalate');
        Route::get('ride-ratings', RatingsPageController::class)->name('ride-ratings.index');
        Route::post('ride-ratings/{rating}/flag', FlagRatingController::class)->whereNumber('rating')->middleware('panel.single-shard')->name('ride-ratings.flag');

        Route::get('complaints', \App\Http\Services\Panel\Support\Controller\ComplaintsPageController::class)->middleware('permission:' . Perm::VIEW_TICKETS)->name('complaints.index');
        Route::post('complaints/{complaint}/status', \App\Http\Services\Panel\Support\Controller\UpdateComplaintStatusController::class)->whereNumber('complaint')->middleware('permission:' . Perm::VIEW_TICKETS, 'panel.single-shard')->name('complaints.status');

        Route::get('lost-items', \App\Http\Services\Panel\Support\Controller\LostItemsPageController::class)->name('lost-items.index');
        Route::post('lost-items/{item}/status', \App\Http\Services\Panel\Support\Controller\UpdateLostItemStatusController::class)->whereNumber('item')->middleware('panel.single-shard')->name('lost-items.status');
        Route::post('lost-items/{item}/match', \App\Http\Services\Panel\Support\Controller\ConfirmLostItemMatchController::class)->whereNumber('item')->middleware('panel.single-shard')->name('lost-items.match');

        Route::get('announcements', \App\Http\Services\Panel\Announcements\Controller\PushComposerPageController::class)->middleware('permission:' . Perm::VIEW_DRIVER_LIST)->name('announcements.index');
        Route::post('announcements/send', \App\Http\Services\Panel\Announcements\Controller\SendBroadcastPushController::class)->middleware('permission:' . Perm::VIEW_DRIVER_LIST, 'panel.single-shard')->name('announcements.send');

        Route::get('driver-safety', DriverSafetyPageController::class)->name('driver-safety.index');
        Route::post('driver-safety/{event}', \App\Http\Services\Panel\DriverOps\Controller\UpdateDriverSafetyStatusController::class)->whereNumber('event')->middleware('panel.single-shard')->name('driver-safety.status');
        Route::get('driver-presence', DriverPresencePageController::class)->middleware('permission:' . Perm::VIEW_DRIVER_LIST)->name('driver-presence.index');
        Route::post('driver-presence/{driver}/offline', ForceDriverOfflineController::class)->whereNumber('driver')->middleware('permission:' . Perm::EDIT_DRIVER, 'panel.single-shard')->name('driver-presence.offline');
        Route::get('driver-applications', DriverApplicationsPageController::class)->name('driver-applications.index');
        Route::post('driver-applications/{application}/review', ReviewDriverApplicationController::class)->whereNumber('application')->middleware('panel.single-shard')->name('driver-applications.review');

        Route::get('chat', OfficeChatPageController::class)->name('chat.index');
        Route::get('chat/{conversation}', OfficeChatThreadPageController::class)->whereNumber('conversation')->name('chat.show');
        Route::post('chat/{conversation}/reply', OfficeChatReplyController::class)->whereNumber('conversation')->name('chat.send');

    });
