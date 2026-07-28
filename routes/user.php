<?php

use App\Http\Services\User\Auth\Controllers\AuthController;
use App\Http\Services\User\B2B\Controllers\CorporateController;
use App\Http\Services\User\B2B\Controllers\FamilyController;
use App\Http\Services\User\Booking\Controllers\BookingController;
use App\Http\Services\User\Booking\Controllers\CancellationReasonsController;
use App\Http\Services\User\Marketplace\Controllers\FavoritesController;
use App\Http\Services\User\Marketplace\Controllers\GeocodeController;
use App\Http\Services\User\Marketplace\Controllers\MarketplaceController;
use App\Http\Services\User\Support\Controllers\ComplaintsController;
use App\Http\Services\User\Support\Controllers\CountryController;
use App\Http\Services\User\Support\Controllers\HelpController;
use App\Http\Services\User\Support\Controllers\TicketsController;
use App\Http\Services\User\Notifications\Controllers\DevicesController;
use App\Http\Services\User\Notifications\Controllers\NotificationsController;
use App\Http\Services\User\Payments\Controllers\PaymentMethodsController;
use App\Http\Services\User\Payments\Controllers\PromosController;
use App\Http\Services\User\Payments\Controllers\StripePaymentsController;
use App\Http\Services\User\Payments\Controllers\WalletController;
use App\Http\Services\User\Profile\Controllers\PlacesController;
use App\Http\Services\User\Profile\Controllers\ProfileController;
use App\Http\Services\User\Profile\Controllers\SafetyContactsController;
use App\Http\Services\User\Scheduled\Controllers\ScheduledController;
use App\Http\Services\User\Trips\Controllers\MessagesController;
use App\Http\Services\User\Trips\Controllers\RideEditController;
use App\Http\Services\User\Trips\Controllers\TripsController;
use Illuminate\Support\Facades\Route;

Route::middleware('user-api')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('otp/request', [AuthController::class, 'requestOtp']);
        Route::post('otp/verify', [AuthController::class, 'verifyOtp']);
        Route::post('register', [AuthController::class, 'register']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('social', [AuthController::class, 'social']);
    });

    // Public reference data — the login country picker loads this before auth.
    Route::get('countries', [CountryController::class, 'index']);

    // See the note in routes/driver.php — `auth:user` accepts a driver's token
    // whenever the ids collide, so the audience scope is the real boundary.
    Route::middleware(['auth:user', 'token-audience:user'])->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/phone/change', [AuthController::class, 'changePhone']);
        Route::post('auth/phone/change/verify', [AuthController::class, 'verifyPhoneChange']);

        Route::get('me', [AuthController::class, 'me']);
        Route::patch('me', [ProfileController::class, 'update']);
        Route::post('me/photo', [ProfileController::class, 'uploadPhoto']);
        Route::delete('account', [ProfileController::class, 'destroy']);

        Route::get('me/notifications-prefs', [ProfileController::class, 'notificationPrefs']);
        Route::patch('me/notifications-prefs', [ProfileController::class, 'updateNotificationPrefs']);
        Route::get('me/privacy', [ProfileController::class, 'privacy']);
        Route::patch('me/privacy', [ProfileController::class, 'updatePrivacy']);

        Route::get('me/places', [PlacesController::class, 'index']);
        Route::post('me/places', [PlacesController::class, 'store']);
        Route::patch('me/places/{id}', [PlacesController::class, 'update'])->whereNumber('id');
        Route::delete('me/places/{id}', [PlacesController::class, 'destroy'])->whereNumber('id');

        Route::get('me/safety-contacts', [SafetyContactsController::class, 'index']);
        Route::post('me/safety-contacts', [SafetyContactsController::class, 'store']);
        Route::patch('me/safety-contacts/auto-share', [SafetyContactsController::class, 'autoShareToggle']);
        Route::delete('me/safety-contacts/{id}', [SafetyContactsController::class, 'destroy'])->whereNumber('id');

        Route::get('me/favorites', [FavoritesController::class, 'index']);
        Route::post('me/favorites/{officeId}', [FavoritesController::class, 'store'])->whereNumber('officeId');
        Route::delete('me/favorites/{officeId}', [FavoritesController::class, 'destroy'])->whereNumber('officeId');

        Route::get('catalog/services', [MarketplaceController::class, 'catalogServices']);
        Route::get('catalog/classes', [MarketplaceController::class, 'catalogClasses']);
        Route::post('offices/search', [MarketplaceController::class, 'officesSearch']);
        Route::get('offices/{id}', [MarketplaceController::class, 'officeProfile'])->whereNumber('id');
        Route::post('routes/estimate', [MarketplaceController::class, 'estimate']);
        Route::get('places/suggest', [MarketplaceController::class, 'placesSuggest']);
        Route::get('places/details', [MarketplaceController::class, 'placeDetails']);

        Route::get('bookings/cancellation-reasons', [CancellationReasonsController::class, 'index']);
        Route::post('bookings', [BookingController::class, 'store']);
        Route::post('bookings/{id}/cancel', [BookingController::class, 'cancel'])->whereNumber('id');
        Route::post('bookings/{id}/change-office', [BookingController::class, 'changeOffice'])->whereNumber('id');
        Route::get('bookings/{id}', [BookingController::class, 'show'])->whereNumber('id');

        Route::get('trips/rating-tags', [\App\Http\Services\User\Trips\Controllers\RatingTagsController::class, 'index']);
        Route::get('trips', [TripsController::class, 'index']);
        Route::get('trips/{id}', [TripsController::class, 'show'])->whereNumber('id');
        Route::post('trips/{id}/rating', [TripsController::class, 'rate'])->whereNumber('id');
        Route::post('trips/{id}/lost-item', [TripsController::class, 'lostItem'])->whereNumber('id');
        // Governed lost & found — the rider's own reports + withdraw.
        Route::get('trips/lost-items', [TripsController::class, 'lostItems']);
        Route::post('trips/lost-items/{item}/cancel', [TripsController::class, 'cancelLostItem'])->whereNumber('item');
        Route::post('trips/{id}/change-route', [RideEditController::class, 'changeRoute'])->whereNumber('id');
        Route::post('trips/{id}/add-stop', [RideEditController::class, 'addStop'])->whereNumber('id');
        Route::get('trips/{id}/messages', [MessagesController::class, 'index'])->whereNumber('id');
        Route::post('trips/{id}/messages', [MessagesController::class, 'store'])->whereNumber('id');

        Route::get('notifications', [NotificationsController::class, 'index']);
        Route::post('notifications/read-all', [NotificationsController::class, 'readAll']);
        Route::post('notifications/{id}/read', [NotificationsController::class, 'read'])->whereNumber('id');
        Route::post('devices', [DevicesController::class, 'store']);
        Route::delete('devices/{token}', [DevicesController::class, 'destroy']);

        Route::get('wallet', [WalletController::class, 'balance']);
        Route::get('wallet/transactions', [WalletController::class, 'transactions']);
        Route::get('wallet/topup-options', [WalletController::class, 'topUpOptions']);
        Route::get('wallet/topup-quote', [WalletController::class, 'topUpQuote']);
        Route::post('wallet/topup', [WalletController::class, 'topUp']);
        Route::post('wallet/topup/verify', [WalletController::class, 'verifyTopUp']);

        Route::get('payment-methods', [PaymentMethodsController::class, 'index']);
        Route::post('payment-methods', [PaymentMethodsController::class, 'store']);
        Route::patch('payment-methods/{id}', [PaymentMethodsController::class, 'setDefault'])->whereNumber('id');
        Route::delete('payment-methods/{id}', [PaymentMethodsController::class, 'destroy'])->whereNumber('id');

        Route::get('promos', [PromosController::class, 'index']);
        Route::post('promos/redeem', [PromosController::class, 'redeem']);

        Route::post('payments/stripe/setup-intent', [StripePaymentsController::class, 'setupIntent']);
        Route::post('payments/stripe/payment-intent', [StripePaymentsController::class, 'paymentIntent']);

        Route::post('scheduled', [ScheduledController::class, 'store']);
        Route::get('scheduled/{id}', [ScheduledController::class, 'show'])->whereNumber('id');
        Route::patch('scheduled/{id}', [ScheduledController::class, 'update'])->whereNumber('id');
        Route::delete('scheduled/{id}', [ScheduledController::class, 'destroy'])->whereNumber('id');

        // Office-mediated fixed A-to-Z: compare offers → pick an office → cancel.
        Route::get('fixed/cities', [\App\Http\Services\User\Fixed\Controllers\FixedTripController::class, 'cities']);
        Route::get('fixed/sub-services', [\App\Http\Services\User\Fixed\Controllers\FixedTripController::class, 'subServices']);
        Route::post('fixed/offers', [\App\Http\Services\User\Fixed\Controllers\FixedTripController::class, 'offers']);
        Route::post('fixed/select', [\App\Http\Services\User\Fixed\Controllers\FixedTripController::class, 'select']);
        Route::get('fixed/{id}', [\App\Http\Services\User\Fixed\Controllers\FixedTripController::class, 'show'])->whereNumber('id');
        Route::post('fixed/{id}/cancel', [\App\Http\Services\User\Fixed\Controllers\FixedTripController::class, 'cancel'])->whereNumber('id');

        Route::post('geocode/reverse', [GeocodeController::class, 'reverse']);

        Route::get('tickets', [TicketsController::class, 'index']);
        Route::post('tickets', [TicketsController::class, 'store']);
        Route::get('tickets/{id}', [TicketsController::class, 'show'])->whereNumber('id');
        Route::post('complaints', [ComplaintsController::class, 'store']);
        Route::post('complaints/photo', [ComplaintsController::class, 'uploadPhoto']);
        Route::get('help/articles', [HelpController::class, 'index']);
        Route::get('help/articles/{id}', [HelpController::class, 'show'])->whereNumber('id');
        Route::get('help/contact', [HelpController::class, 'contact']);

        Route::get('corporate/invoices', [CorporateController::class, 'index']);
        Route::get('family/members', [FamilyController::class, 'index']);
        Route::post('family/members', [FamilyController::class, 'store']);
        Route::patch('family/members/{id}', [FamilyController::class, 'update'])->whereNumber('id');
        Route::delete('family/members/{id}', [FamilyController::class, 'destroy'])->whereNumber('id');
    });
});
