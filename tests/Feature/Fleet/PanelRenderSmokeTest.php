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
                'statusFilter' => '',
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
