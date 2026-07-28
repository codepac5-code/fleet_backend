<?php

namespace App\Providers;
use Barryvdh\Debugbar\Facade as Debugbar;

use App\Http\Core\Classes\Account\CardGateway;
use App\Http\Core\Classes\Account\NullCardGateway;
use App\Http\Core\Classes\Account\StripeCardGateway;
use App\Http\Core\Classes\Auth\DriverTokenIssuer;
use App\Http\Core\Classes\Auth\PassportDriverTokenIssuer;
use App\Http\Core\Classes\Auth\PassportTokenIssuer;
use App\Http\Core\Classes\Auth\SocialVerifier;
use App\Http\Core\Classes\Auth\TokenIssuer;
use App\Http\Core\Classes\Auth\UnconfiguredSocialVerifier;
use App\Http\Core\Classes\Places\GeocodingProvider;
use App\Http\Core\Classes\Places\GoogleGeocodingProvider;
use App\Http\Core\Classes\Places\NullGeocodingProvider;
use App\Models\SiteSetting;
use App\Http\Core\Repositories\Account\EloquentRiderPaymentMethodRepository;
use App\Http\Core\Repositories\Account\EloquentRiderProfileRepository;
use App\Http\Core\Repositories\Account\EloquentSafetyContactRepository;
use App\Http\Core\Repositories\Account\RiderPaymentMethodRepository;
use App\Http\Core\Repositories\Account\RiderProfileRepository;
use App\Http\Core\Repositories\Account\SafetyContactRepository;
use App\Http\Core\Repositories\Chat\ChatRepository;
use App\Http\Core\Repositories\Chat\EloquentChatRepository;
use App\Http\Core\Repositories\Dispatch\DispatchJobRepository;
use App\Http\Core\Repositories\Dispatch\EloquentDispatchJobRepository;
use App\Http\Core\Repositories\Driver\DriverDirectoryRepository;
use App\Http\Core\Repositories\Driver\EloquentDriverDirectoryRepository;
use App\Http\Core\Repositories\Ledger\CommissionSnapshotRepository;
use App\Http\Core\Repositories\Ledger\EloquentCommissionSnapshotRepository;
use App\Http\Core\Repositories\Ledger\EloquentDriverStatementRepository;
use App\Http\Core\Repositories\Ledger\EloquentLedgerStatementRepository;
use App\Http\Core\Repositories\Ledger\DriverStatementRepository;
use App\Http\Core\Repositories\Ledger\LedgerStatementRepository;
use App\Http\Core\Repositories\Places\EloquentSavedPlaceRepository;
use App\Http\Core\Repositories\Places\SavedPlaceRepository;
use App\Http\Core\Repositories\Rating\EloquentRideRatingRepository;
use App\Http\Core\Repositories\Rating\RideRatingRepository;
use App\Http\Core\Repositories\Ride\BookingChatRepository;
use App\Http\Core\Repositories\Ride\EloquentBookingChatRepository;
use App\Http\Core\Repositories\Ride\EloquentRideBookingRepository;
use App\Http\Core\Repositories\Ride\RideBookingRepository;
use App\Http\Core\Repositories\Support\EloquentDriverSafetyRepository;
use App\Http\Core\Repositories\Support\EloquentRiderSupportRepository;
use App\Http\Core\Repositories\Support\DriverSafetyRepository;
use App\Http\Core\Repositories\Support\RiderSupportRepository;
use App\Http\Core\Const\Auth\TokenAudience;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TokenIssuer::class, PassportTokenIssuer::class);
        $this->app->bind(DriverTokenIssuer::class, PassportDriverTokenIssuer::class);
        $this->app->bind(SocialVerifier::class, UnconfiguredSocialVerifier::class);

        $this->app->when([
            \App\Http\Services\User\Auth\Logic\ChallengeOtpLogic::class,
            \App\Http\Services\User\Auth\Logic\PhoneChangeService::class,
            \App\Http\Core\Classes\Ride\OfficeBookingService::class,
        ])->needs(\App\Http\Core\Classes\Notification\SmsSender::class)
            ->give(\App\Http\Core\Classes\Notification\WhatsappSender::class);
        $this->app->bind(GeocodingProvider::class, fn () => config('services.google_maps.key')
            ? new GoogleGeocodingProvider()
            : new NullGeocodingProvider());
        $this->app->bind(CardGateway::class, fn () => config('services.stripe.secret')
            ? new StripeCardGateway()
            : new NullCardGateway());
        $this->app->bind(RideBookingRepository::class, EloquentRideBookingRepository::class);
        $this->app->bind(RiderSupportRepository::class, EloquentRiderSupportRepository::class);
        $this->app->bind(DriverSafetyRepository::class, EloquentDriverSafetyRepository::class);
        $this->app->bind(RiderProfileRepository::class, EloquentRiderProfileRepository::class);
        $this->app->bind(SafetyContactRepository::class, EloquentSafetyContactRepository::class);
        $this->app->bind(RiderPaymentMethodRepository::class, EloquentRiderPaymentMethodRepository::class);
        $this->app->bind(SavedPlaceRepository::class, EloquentSavedPlaceRepository::class);
        $this->app->bind(DispatchJobRepository::class, EloquentDispatchJobRepository::class);
        $this->app->bind(RideRatingRepository::class, EloquentRideRatingRepository::class);
        $this->app->bind(BookingChatRepository::class, EloquentBookingChatRepository::class);
        $this->app->bind(CommissionSnapshotRepository::class, EloquentCommissionSnapshotRepository::class);
        $this->app->bind(LedgerStatementRepository::class, EloquentLedgerStatementRepository::class);
        $this->app->bind(DriverStatementRepository::class, EloquentDriverStatementRepository::class);
        $this->app->bind(ChatRepository::class, EloquentChatRepository::class);
        $this->app->bind(DriverDirectoryRepository::class, EloquentDriverDirectoryRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Rider vs driver audience, carried as a token scope. Passport skips its
        // own provider check because the personal-access client has a NULL
        // provider, so this is what keeps the two apps' tokens apart.
        Passport::tokensCan(TokenAudience::all());

        $this->applyIntegrationSettingsOverride();

        $locale = session('locale', config('app.locale'));
        App::setLocale($locale);

                if (app()->environment('local') && request()->ip() === '127.0.0.1') {
            Debugbar::enable();
        } else {
            Debugbar::disable();
        }

        //   view()->share('socketIoUrl', config('app.socket_io_url'));
    }

    /**
     * Let admins manage integration keys from the panel without redeploying.
     * Values saved in SiteSetting override the env-backed config; when a key is
     * unset the env default is preserved (AppSettings::string falls back to the
     * current config value, and we only override with a non-empty result). This
     * keeps every existing `config('services.stripe.*')` read site unchanged.
     */
    private function applyIntegrationSettingsOverride(): void
    {
        $map = [
            'stripe_secret'         => 'services.stripe.secret',
            'stripe_public'         => 'services.stripe.public',
            'stripe_webhook_secret' => 'services.stripe.webhook_secret',
            'whatsapp_base_url'     => 'services.whatsapp.base_url',
            'whatsapp_prefix'       => 'services.whatsapp.prefix',
            'whatsapp_token'        => 'services.whatsapp.token',
            'whatsapp_session_id'   => 'services.whatsapp.session_id',
        ];

        // Read directly (not via the shared AppSettings latch) so a missing
        // table during setup/tests never poisons other settings consumers.
        try {
            $stored = SiteSetting::query()
                ->whereIn('key', array_keys($map))
                ->pluck('value', 'key');
        } catch (\Throwable $e) {
            return;
        }

        foreach ($map as $settingKey => $configKey) {
            $value = trim((string) ($stored[$settingKey] ?? ''));

            if ($value !== '') {
                config([$configKey => $value]);
            }
        }
    }
}
