<?php

use App\Http\Services\Driver\Controllers\DriverApplicationsController;
use App\Http\Services\Driver\Controllers\DriverAuthController;
use App\Http\Services\Driver\Controllers\DriverDocumentController;
use App\Http\Services\Driver\Controllers\DriverFoundItemController;
use App\Http\Services\Driver\Controllers\DriverHelpController;
use App\Http\Services\Driver\Controllers\DriverHomeController;
use App\Http\Services\Driver\Controllers\DriverNotificationsController;
use App\Http\Services\Driver\Controllers\DriverOffersController;
use App\Http\Services\Driver\Controllers\DriverPlacesController;
use App\Http\Services\Driver\Controllers\DriverPresenceController;
use App\Http\Services\Driver\Controllers\DriverProfileController;
use App\Http\Services\Driver\Controllers\DriverSafetyController;
use App\Http\Services\Driver\Controllers\DriverScheduledController;
use App\Http\Services\Driver\Controllers\DriverSettingsController;
use App\Http\Services\Driver\Controllers\DriverSupportController;
use App\Http\Services\Driver\Controllers\CancellationReasonsController;
use App\Http\Services\Driver\Controllers\DriverTripController;
use App\Http\Services\Driver\Controllers\DriverTripQueryController;
use App\Http\Services\Driver\Controllers\DriverWalletController;
use App\Http\Services\Driver\Controllers\DriverZoneController;
use Illuminate\Support\Facades\Route;

Route::middleware('user-api')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('otp/request', [DriverAuthController::class, 'requestOtp']);
        Route::post('otp/verify', [DriverAuthController::class, 'verifyOtp']);
    });

    // Public reference data — the sign-in country picker loads this before auth.
    Route::get('countries', [\App\Http\Services\User\Support\Controllers\CountryController::class, 'index']);

    // Offices the driver can ask to join. Public because linking happens during
    // onboarding, before an account exists; the shard middleware already scopes
    // it to the country picked in the sign-in picker.
    Route::get('offices', [\App\Http\Services\Driver\Controllers\DriverOfficesController::class, 'index']);

    // Public onboarding write — the driver has no account yet.
    Route::post('applications', [DriverApplicationsController::class, 'apply']);

    // `auth:driver` alone is NOT an identity boundary here — Passport's provider
    // check is inert (null-provider client), so a rider's token authenticates as
    // the driver sharing its id. `token-audience` is what actually separates them.
    Route::middleware(['auth:driver', 'token-audience:driver'])->group(function () {
        Route::get('me', [DriverAuthController::class, 'me']);
        Route::patch('me', [DriverProfileController::class, 'updateMe']);
        Route::patch('vehicle', [DriverProfileController::class, 'updateVehicle']);
        Route::post('auth/logout', [DriverAuthController::class, 'logout']);

        // Home dock KPIs + go-online readiness
        Route::get('home', [DriverHomeController::class, 'home']);
        Route::get('readiness', [DriverHomeController::class, 'readiness']);

        // Wallet + earnings + payouts
        Route::get('earnings', [DriverWalletController::class, 'earnings']);
        Route::get('wallet', [DriverWalletController::class, 'wallet']);
        Route::get('wallet/transactions', [DriverWalletController::class, 'transactions']);
        Route::post('wallet/payouts', [DriverWalletController::class, 'payout']);
        Route::get('dues', [DriverWalletController::class, 'dues']);
        Route::post('dues/settle', [DriverWalletController::class, 'settleDues']);

        // Scheduled-ride marketplace (offers / claim / committed / release / reminder)
        Route::get('scheduled/offers', [DriverScheduledController::class, 'offers']);
        Route::post('scheduled/offers/{id}/claim', [DriverScheduledController::class, 'claim'])->whereNumber('id');
        Route::get('scheduled/committed', [DriverScheduledController::class, 'committed']);
        Route::post('scheduled/committed/{id}/release', [DriverScheduledController::class, 'release'])->whereNumber('id');
        Route::post('scheduled/committed/{id}/reminder', [DriverScheduledController::class, 'reminder'])->whereNumber('id');

        // Saved places
        Route::get('places/search', [DriverPlacesController::class, 'search']);
        Route::get('places', [DriverPlacesController::class, 'index']);
        Route::post('places', [DriverPlacesController::class, 'store']);
        Route::patch('places/{id}', [DriverPlacesController::class, 'update'])->whereNumber('id');
        Route::delete('places/{id}', [DriverPlacesController::class, 'destroy'])->whereNumber('id');

        // Onboarding status + app settings
        Route::get('onboarding', [DriverSettingsController::class, 'onboarding']);
        Route::patch('preferences', [DriverSettingsController::class, 'preferences']);
        Route::get('payment-settings', [DriverSettingsController::class, 'paymentSettingsShow']);
        Route::patch('payment-settings', [DriverSettingsController::class, 'paymentSettings']);
        Route::patch('permissions', [DriverSettingsController::class, 'permissions']);
        Route::post('account-deletion', [DriverSettingsController::class, 'requestDeletion']);

        // Safety — emergency contacts, SOS events, shareable status links
        Route::get('safety/contacts', [DriverSafetyController::class, 'contacts']);
        Route::post('safety/contacts', [DriverSafetyController::class, 'storeContact']);
        Route::delete('safety/contacts/{id}', [DriverSafetyController::class, 'destroyContact'])->whereNumber('id');
        Route::post('safety/sos', [DriverSafetyController::class, 'sos']);
        Route::post('safety/sos/{id}/end', [DriverSafetyController::class, 'endSos'])->whereNumber('id');
        Route::post('safety/status-links', [DriverSafetyController::class, 'createStatusLink']);
        Route::delete('safety/status-links/{id}', [DriverSafetyController::class, 'destroyStatusLink'])->whereNumber('id');

        // Found item left by a rider → lost_items
        Route::get('found-items', [DriverFoundItemController::class, 'index']);
        Route::post('trips/{id}/found-items', [DriverFoundItemController::class, 'store'])->whereNumber('id');

        // Rate the rider after a trip
        Route::post('trips/{id}/rating', [DriverTripQueryController::class, 'rateRider'])->whereNumber('id');

        // Support (tickets / trip issues / reply threads)
        Route::get('support/contact', [DriverSupportController::class, 'contact']);
        Route::post('support/tickets', [DriverSupportController::class, 'ticket']);
        Route::post('support/trip-issues', [DriverSupportController::class, 'tripIssue']);
        Route::get('support/issues/{id}/replies', [DriverSupportController::class, 'replies'])->whereNumber('id');
        Route::post('support/issues/{id}/replies', [DriverSupportController::class, 'reply'])->whereNumber('id');

        // Documents (KYC / vehicle papers)
        Route::get('documents', [DriverDocumentController::class, 'index']);
        Route::post('documents', [DriverDocumentController::class, 'store']);

        // Cockpit demand hints
        Route::get('zones/demand', [DriverZoneController::class, 'demand']);

        // Notifications + help
        Route::get('notifications', [DriverNotificationsController::class, 'index']);
        Route::post('notifications/read', [DriverNotificationsController::class, 'read']);
        Route::get('help/articles', [DriverHelpController::class, 'index']);
        Route::get('help/articles/{id}', [DriverHelpController::class, 'show'])->whereNumber('id');

        // Availability
        Route::post('presence', [DriverPresenceController::class, 'update']);

        // Dispatch offers (accept is atomic — first driver wins)
        Route::post('offers/{id}/accept', [DriverOffersController::class, 'accept'])->whereNumber('id');
        Route::post('offers/{id}/reject', [DriverOffersController::class, 'reject'])->whereNumber('id');

        // Ride reads (details / rider contact / history / cancel-impact)
        Route::get('trips/history', [DriverTripQueryController::class, 'history']);
        Route::get('trips/{id}', [DriverTripQueryController::class, 'show'])->whereNumber('id');
        Route::get('trips/{id}/rider-contact', [DriverTripQueryController::class, 'riderContact'])->whereNumber('id');
        Route::get('trips/{id}/cancel-impact', [DriverTripQueryController::class, 'cancelImpact'])->whereNumber('id');
        Route::get('trips/cancellation-reasons', [CancellationReasonsController::class, 'index']);
        Route::get('trips/rating-tags', [\App\Http\Services\Driver\Controllers\RatingTagsController::class, 'index']);
        Route::get('incentives', [\App\Http\Services\Driver\Controllers\IncentivesController::class, 'index']);

        // Ride lifecycle — each emits booking.status_changed to the rider
        Route::post('trips/{id}/navigate-pickup', [DriverTripController::class, 'navigatePickup'])->whereNumber('id');
        Route::post('trips/{id}/arrived', [DriverTripController::class, 'arrived'])->whereNumber('id');
        Route::post('trips/{id}/start', [DriverTripController::class, 'start'])->whereNumber('id');
        Route::post('trips/{id}/end', [DriverTripController::class, 'end'])->whereNumber('id');
        Route::post('trips/{id}/payment/confirm', [DriverTripController::class, 'confirmPayment'])->whereNumber('id');
        Route::post('trips/{id}/cancel', [DriverTripController::class, 'cancel'])->whereNumber('id');
        Route::post('trips/{id}/location', [DriverTripController::class, 'location'])->whereNumber('id');
    });
});
