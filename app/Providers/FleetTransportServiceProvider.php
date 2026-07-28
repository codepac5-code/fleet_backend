<?php

namespace App\Providers;

use App\Http\Core\Classes\Dispatch\DispatchService;
use App\Http\Core\Classes\Event\CompositePublisher;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Classes\Event\EventPublisher;
use App\Http\Core\Classes\Event\RedisEventPublisher;
use App\Http\Core\Classes\Notification\EventNotificationPublisher;
use App\Http\Core\Classes\Notification\FcmPushSender;
use App\Http\Core\Classes\Notification\LaravelMailSender;
use App\Http\Core\Classes\Notification\MailSender;
use App\Http\Core\Classes\Notification\NotificationService;
use App\Http\Core\Classes\Notification\PushSender;
use App\Http\Core\Classes\Notification\TemplateRenderer;
use Illuminate\Support\ServiceProvider;

class FleetTransportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PushSender::class, FcmPushSender::class);
        $this->app->bind(MailSender::class, LaravelMailSender::class);
        $this->app->bind(
            \App\Http\Core\Classes\Subscription\Billing\StripeInvoiceItemClient::class,
            \App\Http\Core\Classes\Subscription\Billing\StripeSdkInvoiceItemClient::class
        );
        $this->app->bind(\App\Http\Core\Classes\Subscription\Billing\OverageBillingGateway::class, function ($app) {
            if (config('services.stripe.overage_billing') && config('services.stripe.secret')) {
                return $app->make(\App\Http\Core\Classes\Subscription\Billing\StripeOverageBillingGateway::class);
            }

            return $app->make(\App\Http\Core\Classes\Subscription\Billing\ManualOverageBillingGateway::class);
        });

        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService(
                new TemplateRenderer(),
                $app->make(PushSender::class),
                $app->make(MailSender::class)
            );
        });

        $this->app->bind(EventPublisher::class, function ($app) {
            return new CompositePublisher(
                new RedisEventPublisher(),
                new EventNotificationPublisher($app->make(NotificationService::class)),
                new \App\Http\Core\Classes\Event\OrderBoardPublisher()
            );
        });

        $this->app->singleton(DispatchService::class, function () {
            return new DispatchService(new EventBus());
        });

        $this->app->bind(\App\Http\Core\Classes\Ride\DriverTripService::class, function ($app) {
            return new \App\Http\Core\Classes\Ride\DriverTripService(
                $app->make(\App\Http\Core\Repositories\Ride\RideBookingRepository::class),
                $app->make(\App\Http\Core\Repositories\Dispatch\DispatchJobRepository::class),
                $app->make(\App\Http\Core\Classes\Ride\RideLifecycleService::class),
                $app->make(\App\Http\Core\Classes\Ledger\FleetWalletService::class),
                $app->make(DispatchService::class),
                $app->make(\App\Http\Core\Classes\Pricing\TariffResolver::class),
                $app->make(\App\Http\Core\Classes\Pricing\PricingService::class),
                new EventBus(),
                new RedisEventPublisher()
            );
        });

        $this->app->singleton(\App\Http\Core\Classes\Chat\ChatService::class, function ($app) {
            return new \App\Http\Core\Classes\Chat\ChatService(
                $app->make(\App\Http\Core\Repositories\Chat\ChatRepository::class),
                new EventBus()
            );
        });

        $this->app->singleton(\App\Http\Core\Classes\Ride\RideLifecycleService::class, function ($app) {
            return new \App\Http\Core\Classes\Ride\RideLifecycleService(
                $app->make(\App\Http\Core\Classes\Ledger\BookingSettlementService::class),
                new EventBus(),
                $app->make(\App\Http\Core\Classes\Subscription\PlanOverageService::class)
            );
        });

        $this->app->bind(\App\Http\Core\Classes\Payment\PayoutService::class, function ($app) {
            return new \App\Http\Core\Classes\Payment\PayoutService(
                $app->make(\App\Http\Core\Classes\Ledger\FleetWalletService::class),
                new EventBus()
            );
        });

        $this->app->bind(\App\Http\Core\Classes\Rating\RatingService::class, function ($app) {
            return new \App\Http\Core\Classes\Rating\RatingService(
                $app->make(\App\Http\Core\Repositories\Rating\RideRatingRepository::class),
                new EventBus()
            );
        });
    }

    public function boot(): void
    {
        // Queue-worker liveness for the ops panel: the worker fires Looping each
        // loop iteration, so a fresh heartbeat means it is alive and draining.
        $this->app['events']->listen(\Illuminate\Queue\Events\Looping::class, function () {
            app(\App\Http\Core\Classes\Ops\HeartbeatService::class)->beat('queue-worker');
        });
    }
}
