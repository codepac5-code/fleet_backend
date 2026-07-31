<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Const\Notification\TemplateCatalog;
use App\Http\Services\Panel\Drivers\Logic\DocumentStatus;
use App\Models\Admin;
use App\Models\Office;
use App\Models\RideBooking;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Renders every NEW panel page's CONTENT to catch the render-time bugs the rest
 * of the suite never sees (it never renders authenticated panel pages): a blade
 * that uses `$entity` whose controller forgot to pass it, or a `route()` to a
 * name that isn't registered. The real `panel.layouts.master` pulls in
 * DB-heavy composers (NotificationFeed etc.), so we swap it for a minimal stub
 * that just yields the content — enough to exercise every variable and route
 * the page's own blade references.
 *
 * When you add a panel page, add one line here with its view + the exact data
 * its controller passes. If the controller drops a variable the blade needs,
 * this fails instead of the browser.
 */
class PanelRenderSmokeTest extends TestCase
{
    private string $stubDir;

    protected function setUp(): void
    {
        parent::setUp();

        // Minimal stand-in for panel.layouts.master — no sidebar, no composers.
        $this->stubDir = storage_path('framework/testing/panel-stub-' . uniqid());
        File::ensureDirectoryExists($this->stubDir . '/panel/layouts');
        File::put($this->stubDir . '/panel/layouts/master.blade.php', "@yield('content')\n");
        View::getFinder()->prependLocation($this->stubDir);
        View::getFinder()->flush();

        // The web middleware group always shares an (empty) error bag; a direct
        // render doesn't, so mirror it — every panel page reads $errors.
        View::share('errors', new ViewErrorBag());

        // The layout stub + several content blades read the acting panel user.
        $admin = new Admin();
        $admin->id = 1;
        $this->actingAs($admin, 'admin');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->stubDir);
        parent::tearDown();
    }

    #[DataProvider('pages')]
    public function test_panel_page_renders(string $view, array $data): void
    {
        $html = View::make($view, $data)->render();

        $this->assertIsString($html);
    }

    public static function pages(): array
    {
        $tmplKey = array_key_first(TemplateCatalog::TEMPLATES);
        $tmplDef = TemplateCatalog::TEMPLATES[$tmplKey];

        return [
            'announcements' => ['panel.announcements.index', [
                'entity' => 'admin', 'isAdmin' => true, 'riderCount' => 0, 'driverCount' => 0,
                'countries' => collect([
                    (new \App\Models\InfrastructureNode())->forceFill(['id' => 3, 'name' => 'Syria']),
                ]),
                'targetCountry' => (new \App\Models\InfrastructureNode())->forceFill(['id' => 3, 'name' => 'Syria']),
            ]],
            'driver-presence' => ['panel.driver-presence.index', [
                'entity' => 'admin', 'isAdmin' => true,
                'rows' => collect([]),
                'counts' => ['online' => 0, 'busy' => 0, 'offline' => 0],
            ]],
            'coupons' => ['panel.coupons.index', [
                'coupons' => collect([]),
            ]],
            'audit' => ['panel.audit.index', [
                'logs' => new LengthAwarePaginator([], 0, 40),
                'action' => '', 'actorType' => '', 'actorTypes' => [],
            ]],
            'ops' => ['panel.ops.index', [
                'pending' => 0, 'byQueue' => [], 'failedCount' => 0, 'failed' => [], 'outboxPending' => 0,
                'daemons' => (new \App\Http\Core\Classes\Ops\HeartbeatService())->all(),
            ]],
            'notification-templates.index' => ['panel.notification-templates.index', [
                'templates' => collect([]),
            ]],
            'notification-templates.edit' => ['panel.notification-templates.edit', [
                'key' => $tmplKey,
                'def' => $tmplDef,
                'current' => [
                    'subject' => $tmplDef['subject'] ?? [],
                    'body' => $tmplDef['body'] ?? [],
                    'channels' => $tmplDef['channels'] ?? ['inapp'],
                    'is_active' => false,
                ],
                'overridden' => false,
            ]],
            'settings.payments' => ['panel.settings.payments', [
                'publicKey' => '', 'secretHint' => null, 'webhookHint' => null,
            ]],
            'settings.whatsapp' => ['panel.settings.whatsapp', [
                'baseUrl' => '', 'prefix' => 'whatsapp/api/v1', 'sessionId' => '', 'tokenHint' => null,
            ]],
            'cities' => ['panel.cities.index', [
                'cities' => collect([]),
            ]],
            'bookings.legacy' => ['panel.bookings.index', [
                'entity' => 'admin', 'isAdmin' => true,
                'search' => '', 'statusFilter' => null, 'officeFilter' => null,
                'statusOptions' => [], 'officeOptions' => [],
                'bookings' => new LengthAwarePaginator([], 0, 15),
                // The warning only renders when app rides exist that this page cannot show.
                'appRideCount' => 197,
            ]],
            'sub-services.travel' => ['panel.services.sub-services.index', [
                'entity' => 'admin',
                'service' => (new \App\Models\Service())->forceFill(['id' => 2, 'title' => 'سفر', 'title_en' => 'Travel Service', 'travel_service' => 1]),
                'subServices' => collect([
                    (new \App\Models\SubService())->forceFill(['id' => 4, 'name' => 'مطار', 'name_en' => 'Airport Pickup', 'is_travel' => 1, 'status' => 1]),
                    (new \App\Models\SubService())->forceFill(['id' => 5, 'name' => 'بين المدن', 'name_en' => 'Inter-City', 'is_travel' => 1, 'status' => 1]),
                ]),
                'isTravel' => true,
                'currency' => 'SYP',
                // Sub-service 5 has no corridors — the row must warn, not show a price.
                'corridors' => [4 => ['count' => 3, 'min' => 60.0, 'max' => 140.0]],
            ]],
            'sub-services.meter' => ['panel.services.sub-services.index', [
                'entity' => 'admin',
                'service' => (new \App\Models\Service())->forceFill(['id' => 1, 'title' => 'رحلة', 'title_en' => 'Ride', 'travel_service' => 0]),
                'subServices' => collect([
                    (new \App\Models\SubService())->forceFill(['id' => 1, 'name' => 'عادي', 'name_en' => 'Standard', 'is_travel' => 0, 'status' => 1, 'openPrice' => 60, 'kmPrice' => 4, 'minutePrice' => 2]),
                ]),
                'isTravel' => false,
                'currency' => 'SYP',
                'corridors' => [],
            ]],
            // An office runs one or more main services, so the form offers a
            // list of them — here with two already ticked.
            // The office divides what the platform left it with its drivers.
            'commissions.office' => ['panel.commissions.office', [
                'entity' => 'office', 'currency' => 'SYP',
                'fleetRate' => 5.0, 'officeRate' => 20.0, 'ceiling' => 95.0, 'configured' => 20.0,
                'drivers' => [
                    ['id' => 5, 'name' => 'Ali', 'phone' => '+963900', 'override' => null, 'effective' => 20.0],
                    ['id' => 6, 'name' => 'Samer', 'phone' => '+963901', 'override' => 12.5, 'effective' => 12.5],
                ],
            ]],
            // An office that has taken nothing yet, and no drivers to show.
            'commissions.office.empty' => ['panel.commissions.office', [
                'entity' => 'office', 'currency' => 'SYP',
                'fleetRate' => 5.0, 'officeRate' => 0.0, 'ceiling' => 95.0, 'configured' => null,
                'drivers' => [],
            ]],
            'offices.form.edit' => ['panel.offices.form', [
                'entity' => 'admin', 'user' => null,
                'office' => (new Office())->forceFill(['id' => 5, 'officeName' => 'Damascus Luxury Fleet', 'email' => 'damascusluxury@fleet.plus', 'status' => 1]),
                'services' => collect([
                    (new \App\Models\Service())->forceFill(['id' => 1, 'title' => 'تاكسي المدينة', 'title_en' => 'City Taxi', 'travel_service' => 0]),
                    (new \App\Models\Service())->forceFill(['id' => 2, 'title' => 'خدمة السفر', 'title_en' => 'Travel Service', 'travel_service' => 1]),
                ]),
                'assignedServiceIds' => [1, 2],
                'defaultFleetRate' => 5.0,
            ]],
            'office.pricing' => ['panel.services.pricing', [
                'entity' => 'admin',
                'office' => (new Office())->forceFill(['id' => 5, 'officeName' => 'Damascus Luxury Fleet', 'fleetDues' => 0, 'driversDues' => 0]),
                'catalog' => [], 'prices' => [],
                'snapshot' => [
                    'currency' => 'SYP',
                    'corridors' => ['count' => 1, 'min' => 445.0, 'max' => 445.0, 'rows' => [
                        ['sub_service' => 'Airport', 'from' => 'Damascus', 'to' => 'Homs', 'price' => 445.0],
                    ]],
                    'earnings' => ['lifetimeMinor' => 125000, 'monthMinor' => 22000, 'rides' => 9],
                    'finance' => ['walletMinor' => 5000, 'fleetDues' => 0.0, 'driversDues' => 0.0],
                    'subscription' => ['mode' => 'commission', 'row' => null],
                ],
            ]],
            'office.pricing.no-corridors' => ['panel.services.pricing', [
                'entity' => 'admin',
                'office' => (new Office())->forceFill(['id' => 6, 'officeName' => 'Al Sham', 'fleetDues' => 120.5, 'driversDues' => 0]),
                'catalog' => [], 'prices' => [],
                'snapshot' => [
                    'currency' => 'QAR',
                    'corridors' => ['count' => 0, 'min' => null, 'max' => null, 'rows' => []],
                    'earnings' => ['lifetimeMinor' => 0, 'monthMinor' => 0, 'rides' => 0],
                    'finance' => ['walletMinor' => 0, 'fleetDues' => 120.5, 'driversDues' => 0.0],
                    // Subscription country with no subscription: must warn.
                    'subscription' => ['mode' => 'subscription', 'row' => null],
                ],
            ]],
            'reports.fleet' => ['panel.reports.index', [
                'entity' => 'admin', 'isAdmin' => true, 'currency' => 'USD',
                'summary' => [
                    'currency_code' => 'USD', 'rides' => 12, 'gross_minor' => 480000,
                    'fleet_revenue_minor' => 48000, 'office_payouts_minor' => 96000,
                    'driver_payouts_minor' => 336000, 'revenue_balance_minor' => 83000,
                    'subscription_revenue_minor' => 35000, 'subscription_payments' => 1,
                ],
            ]],
            'wallet.transactions' => ['panel.wallet.index', [
                'entity' => 'admin', 'user' => null, 'isAdmin' => true,
                'search' => '', 'statusFilter' => null,
                'summary' => [
                    ['label' => 'Total', 'icon' => 'bi-arrow-left-right', 'value' => 0, 'money' => false],
                ],
                'transactions' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'subscriptionPayments' => [
                    ['id' => 40, 'office' => 'Al Sham', 'plan' => 'business', 'amount_minor' => 35000, 'currency' => 'USD', 'at' => '2026-07-29 03:10'],
                ],
            ]],
            'admin.subscriptions' => ['panel.admin.subscriptions.index', [
                'entity' => 'admin', 'mode' => 'subscription', 'currency' => 'USD',
                'subscriptions' => collect([]), 'officeNames' => collect([]),
                'overageByOffice' => [], 'statusFilter' => null,
                'counts' => ['trialing' => 2, 'active' => 3, 'past_due' => 1, 'total' => 6],
                'money' => ['mrrMinor' => 29700, 'atRiskMinor' => 9900, 'trialMinor' => 19800, 'overageMinor' => 1500],
                'attention' => [
                    'endingSoon' => [['office_id' => 4, 'office' => 'Al Sham', 'plan' => 'business', 'days' => 2]],
                    'pastDue' => [['office_id' => 5, 'office' => 'Doha Fleet', 'plan' => 'starter']],
                    'unsubscribed' => [['office_id' => 6, 'office' => 'New Office']],
                ],
            ]],
            // A commission country must explain the empty table, not just show it.
            'admin.subscriptions.commission' => ['panel.admin.subscriptions.index', [
                'entity' => 'admin', 'mode' => 'commission', 'currency' => 'SYP',
                'subscriptions' => collect([]), 'officeNames' => collect([]),
                'overageByOffice' => [], 'statusFilter' => null,
                'counts' => ['trialing' => 0, 'active' => 0, 'past_due' => 0, 'total' => 0],
                'money' => ['mrrMinor' => 0, 'atRiskMinor' => 0, 'trialMinor' => 0, 'overageMinor' => 0],
                'attention' => ['endingSoon' => [], 'pastDue' => [], 'unsubscribed' => []],
            ]],
            // An office inside a trial must be offered a way to pay before it
            // ends — its own plan used to be the one disabled button on the page.
            'subscription.trialing' => ['panel.subscription.index', [
                'entity' => 'office', 'mode' => 'subscription',
                'trialUsed' => true, 'trialDaysLeft' => 5, 'preselected' => null,
                'usage' => [], 'overagePending' => 0,
                'plans' => [[
                    'key' => 'business', 'name' => 'Business', 'price_minor' => 9900, 'currency_code' => 'USD',
                    'fleet_commission_rate' => 12.0, 'trial_days' => 14, 'is_popular' => true, 'features' => [],
                ]],
                'subscription' => [
                    'plan_key' => 'business', 'fleet_commission_rate' => 12.0, 'office_commission_rate' => 8.0,
                    'price_minor' => 9900, 'currency_code' => 'USD', 'status' => 'trialing',
                    'trial_ends_at' => '2030-01-01 00:00:00', 'current_period_end' => null, 'cancel_at_period_end' => false,
                ],
            ]],
            'subscription.none' => ['panel.subscription.index', [
                'entity' => 'office', 'mode' => 'subscription',
                'trialUsed' => false, 'trialDaysLeft' => 0, 'preselected' => null,
                'usage' => [], 'overagePending' => 0,
                'plans' => [[
                    'key' => 'starter', 'name' => 'Starter', 'price_minor' => 4900, 'currency_code' => 'USD',
                    'fleet_commission_rate' => 15.0, 'trial_days' => 14, 'is_popular' => false, 'features' => [],
                ]],
                'subscription' => null,
            ]],
            'subscription.commission' => ['panel.subscription.index', [
                'entity' => 'office', 'mode' => 'commission',
                'trialUsed' => false, 'trialDaysLeft' => 0, 'preselected' => null,
                'usage' => [], 'overagePending' => 0, 'plans' => [], 'subscription' => null,
            ]],
            // The office form now carries the main-service picker.
            'offices.form.new' => ['panel.offices.form', [
                'entity' => 'admin', 'user' => null, 'office' => null,
                'services' => collect([
                    (new \App\Models\Service())->forceFill(['id' => 1, 'title' => 'تاكسي', 'title_en' => 'City Taxi', 'travel_service' => 0]),
                    (new \App\Models\Service())->forceFill(['id' => 2, 'title' => 'سفر', 'title_en' => 'Travel', 'travel_service' => 1]),
                ]),
            ]],
            'my-services' => ['panel.services.my-services', [
                'entity' => 'office',
                'catalog' => [], 'prices' => [], 'currency' => 'SYP',
                'service' => null, 'assigned' => false,
            ]],
            // A travel office prices corridors, not metres — the screen must say
            // so and drop the open/km/minute columns entirely.
            'my-services.travel' => ['panel.services.my-services', [
                'entity' => 'office',
                'prices' => [], 'currency' => 'SYP', 'assigned' => true,
                'service' => (object) ['travel_service' => 1],
                'catalog' => [[
                    'id' => 2, 'title' => 'Travel Service', 'status' => 1, 'isTravel' => true,
                    'subServices' => [['id' => 4, 'name' => 'استقبال من المطار', 'status' => 1, 'openPrice' => 0.0, 'kmPrice' => 0.0, 'minutePrice' => 0.0]],
                ]],
            ]],
            'pricing.corridors' => ['panel.pricing.corridors', [
                'entity' => 'admin', 'isAdmin' => true,
                'routes' => [], 'subServices' => collect([]), 'cities' => collect([]),
                'offices' => collect([]), 'currency' => 'USD',
            ]],
            'app-status' => ['panel.app-status.index', [
                'maintenance' => false, 'maintenance_message' => '',
                'android_min' => '', 'android_latest' => '', 'ios_min' => '', 'ios_latest' => '',
            ]],
            'office-documents' => ['panel.offices.documents', [
                'office' => (new Office())->forceFill(['id' => 1, 'officeName' => 'Test Office']),
                'documents' => collect([]),
                'statusOptions' => DocumentStatus::all(),
            ]],
            'plans.index' => ['panel.plans.index', [
                'plans' => collect([]),
            ]],
            'plans.form' => ['panel.plans.form', [
                'plan' => null,
            ]],
            'legal' => ['panel.legal.index', [
                'terms_en' => '', 'terms_ar' => '', 'privacy_en' => '', 'privacy_ar' => '',
            ]],
            'vehicle-brands' => ['panel.vehicle-brands.index', [
                'brands' => collect([]),
            ]],
            'cancellation-reasons' => ['panel.cancellation-reasons.index', [
                'reasons' => collect([]),
            ]],
            'pricing.corridors' => ['panel.pricing.corridors', [
                'entity' => 'admin', 'isAdmin' => true,
                'routes' => [[
                    'id' => 1, 'sub_service' => 'Intercity', 'sub_service_id' => 4,
                    'departure' => 'Damascus', 'departure_city_id' => 1,
                    'arrival' => 'Homs', 'arrival_city_id' => 2,
                    'office' => 'Test Office', 'office_id' => 3, 'trip_price' => 125.0,
                ], [
                    // Office-less legacy row: must render the "never offered" warning.
                    'id' => 2, 'sub_service' => 'Intercity', 'sub_service_id' => 4,
                    'departure' => 'Homs', 'departure_city_id' => 2,
                    'arrival' => 'Homs', 'arrival_city_id' => 2,
                    'office' => null, 'office_id' => null, 'trip_price' => 90.0,
                ]],
                'subServices' => collect([]), 'cities' => collect([]), 'offices' => collect([]),
                'currency' => 'SYP',
            ]],
            'pricing.corridors.empty' => ['panel.pricing.corridors', [
                'entity' => 'office', 'isAdmin' => false,
                'routes' => [], 'subServices' => collect([]), 'cities' => collect([]),
                'offices' => collect([]), 'currency' => 'SYP',
            ]],
            'employees.permissions' => ['panel.employees.permissions', [
                'entity' => 'office',
                'employee' => (new \App\Models\Employee())->forceFill(['id' => 3, 'firstName' => 'Sara', 'lastName' => 'H', 'role' => 'agent']),
                'groups' => [],
                'granted' => [],
                'preset' => ['view dashboard'],
                'roleLabel' => 'Agent',
                'roleDescription' => 'Day-to-day operations.',
                'customised' => false,
            ]],
            'users.show' => ['panel.users.show', [
                'entity' => 'admin', 'isAdmin' => true,
                'rider' => (new \App\Models\User())->forceFill([
                    'id' => 5, 'firstName' => 'Test', 'lastName' => 'Rider', 'phoneNumber' => '+963900',
                    'isActive' => 1, 'is_registered' => 1, 'locale' => 'ar',
                ]),
                'overview' => [
                    'total' => 0, 'completed' => 0, 'cancelled' => 0, 'scheduled' => 0,
                    'spentMinor' => 0, 'spentThisMonthMinor' => 0, 'currency' => 'SYP',
                    'firstRideAt' => null, 'lastRideAt' => null,
                ],
                'rides' => collect([]),
                'ratings' => ['givenCount' => 0, 'givenAverage' => null, 'receivedCount' => 0, 'receivedAverage' => null],
                'walletMinor' => 0, 'walletCurrency' => 'SYP',
                'support' => ['complaints' => collect([]), 'openComplaints' => 0, 'lostItems' => collect([])],
            ]],
            'security.setup' => ['panel.security.index', [
                'entity' => 'admin', 'enabled' => false, 'required' => false,
                'pending' => ['secret' => 'ABCD', 'formatted' => 'ABCD', 'uri' => 'otpauth://totp/x'],
                'recoveryCodes' => null, 'recoveryLeft' => 0,
            ]],
            'security.enabled' => ['panel.security.index', [
                'entity' => 'office', 'enabled' => true, 'required' => false,
                'pending' => null, 'recoveryCodes' => ['AAAAA-BBBBB'], 'recoveryLeft' => 7,
            ]],
            'settings.security' => ['panel.settings.security', [
                'requirement' => '', 'records' => collect([]),
            ]],
            'vehicle-catalog' => ['panel.vehicle-catalog.index', [
                'brands' => collect([]), 'brandNames' => collect([]),
                'models' => collect([]), 'colors' => collect([]),
            ]],
            'growth.referrals' => ['panel.growth.referrals', [
                'settings' => new \App\Models\ReferralSetting(),
                'currency' => 'SYP',
                'referrals' => collect([]),
                'names' => collect([]),
                'counts' => ['pending' => 0, 'rewarded' => 0, 'paidMinor' => 0],
            ]],
            'growth.incentives' => ['panel.growth.incentives', [
                'rules' => collect([]),
                'current' => collect([]),
                'currency' => 'SYP',
                'windows' => \App\Models\DriverIncentive::WINDOWS,
                'paidTotalMinor' => 0,
            ]],
            'rating-tags' => ['panel.rating-tags.index', [
                'tags' => collect([]),
            ]],
            'complaints.admin' => ['panel.support.complaints', [
                'entity' => 'admin', 'complaints' => collect([]),
                'statusFilter' => null, 'aboutFilter' => null,
                'counts' => ['open' => 0, 'urgent' => 0, 'resolved' => 0, 'total' => 0],
            ]],
            'complaints.office' => ['panel.support.complaints', [
                'entity' => 'office', 'complaints' => collect([]),
                'statusFilter' => null, 'aboutFilter' => null,
                'counts' => ['open' => 0, 'urgent' => 0, 'resolved' => 0, 'total' => 0],
            ]],
            'overage-invoices' => ['panel.admin.overage-invoices.index', [
                'entity' => 'admin',
                'invoices' => [],
                'officeNames' => collect([]),
                'statusFilter' => null,
                'pendingMinor' => 0,
                'collectedMinor' => 0,
            ]],
            'office-bookings.create' => ['panel.office-bookings.create', [
                'entity' => 'office', 'isAdmin' => false, 'officeId' => 1, 'ready' => true,
                'offices' => [], 'mapCenter' => ['lat' => 33.5, 'lng' => 36.3], 'googleMapsKey' => 'KEY',
                'tariffs' => [['service' => 'ride', 'service_class' => 'standard', 'currency' => 'SYP']],
                'drivers' => [['id' => 5, 'name' => 'Ali', 'phone' => '+963900', 'photo' => null, 'car' => ['brand' => 'Kia', 'model' => 'Rio', 'plate' => 'ABC']]],
            ]],
            'rides.index' => ['panel.rides.index', [
                'entity' => 'admin', 'isAdmin' => true,
                'rows' => collect([]),
                'statusFilter' => '', 'search' => '',
                'statuses' => ['scheduled', 'matching', 'completed', 'cancelled'],
                'counts' => ['total' => 0, 'scheduled' => 0, 'live' => 0, 'completed' => 0, 'cancelled' => 0],
            ]],
            'rides.show' => ['panel.rides.show', [
                'entity' => 'admin',
                'booking' => (new RideBooking())->forceFill([
                    'id' => 1, 'status' => 'completed', 'source' => 'rider', 'service' => 'ride',
                    'service_class' => 'standard', 'payment_method' => 'wallet',
                    'pickup_title' => 'A', 'pickup_lat' => 1, 'pickup_lng' => 2,
                    'dropoff_title' => 'B', 'dropoff_lat' => 3, 'dropoff_lng' => 4,
                    'fare_minor' => 5000, 'discount_minor' => 0, 'total_minor' => 5000,
                    'currency_code' => 'USD', 'driver_id' => 9,
                ]),
                'customerName' => 'Test Rider', 'customerPhone' => '+100', 'driverName' => 'Test Driver',
                'driverPhone' => '+200', 'vehicle' => null, 'officeName' => 'Test Office',
                'commission' => null, 'riderRating' => null, 'driverRating' => null,
                'path' => [], 'mapKey' => null,
                'canRefund' => true,
            ]],
        ];
    }
}
