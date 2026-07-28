<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerService;
use App\Http\Core\Classes\Ride\RideBookingService;
use App\Models\Currency;
use App\Models\Office;
use App\Models\OfficeSubServicePrice;
use App\Models\RideBooking;
use App\Models\Service;
use App\Models\ServiceTariff;
use App\Models\SubService;
use App\Models\User;

/**
 * Rider-app gaps: office discovery, the wallet statement, and trip sharing.
 *
 * Two contracts moved out from under this file:
 *
 *  - office browsing is now `POST user/offices/search` with a full route
 *    envelope, not `GET user/offices?service=…&search=…`. Supply is derived
 *    from OfficeSubServicePrice → SubService (the office's published price
 *    list), NOT from ServiceTariff, and results are ordered by the office's
 *    distance to the PICKUP point. ServiceTariff still exists, but only to
 *    quote a fare onto each card once a serviceClass is supplied.
 *    There is no free-text `search` filter on this endpoint at all.
 *
 *  - `GET user/wallet/transactions` answers {items, nextCursor} in DECIMAL
 *    money (WalletService::transactions via MoneyPresenter), not raw
 *    `amount_minor`/`direction` ledger rows. Debits are signed negative rather
 *    than carrying a direction flag.
 */
class RiderGapsTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2024_10_29_211028_create_offices_table.php',
        // Office supply for the marketplace search.
        '2024_10_26_104402_create_services_table.php',
        '2024_10_26_104427_create_sub_services_table.php',
        '2026_01_03_025343_create_office_sub_service_prices_table.php',
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_06_24_000004_create_commission_snapshots_table.php',
        '2026_06_25_000003_create_ledger_payments_table.php',
        '2026_06_25_000005_create_dispatch_jobs_table.php',
        '2026_06_25_000017_create_ride_ratings_table.php',
        '2026_07_01_000002_create_service_tariffs_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_11_000002_add_service_to_service_tariffs_table.php',
        '2026_07_11_000004_add_titles_to_ride_bookings_table.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
    ];

    // currencies lives on the global (shared) connection, not the tenant shard.
    protected array $globalMigrations = [
        '2026_06_19_000002_create_currencies_table.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Currency::query()->create([
            'code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'decimals' => 2,
            'exchange_rate' => 1, 'is_default' => true, 'is_active' => true,
        ]);
    }

    private function asUser(int $id = 7): self
    {
        $u = new User();
        $u->id = $id;

        return $this->actingAs($u, 'user');
    }

    private function office(int $id, string $name, ?float $lat = null, ?float $lng = null): void
    {
        $o = new Office();
        $o->id = $id;
        $o->forceFill([
            'officeName' => $name, 'email' => 'o' . $id . '@x.test', 'password' => 'x',
            'status' => 1, 'rating' => 4.5, 'lat' => $lat, 'lng' => $lng,
        ]);
        $o->save();
    }

    /** A published service class, and the offices that sell it. */
    private function publish(string $className, array $officeIds): SubService
    {
        $service = Service::query()->create([
            'title' => $className, 'title_en' => $className, 'image' => 'svc.png',
            'status' => 1, 'travel_service' => false,
        ]);

        $sub = SubService::query()->create([
            'name' => $className, 'name_en' => $className, 'serviceId' => $service->id,
            'status' => 1, 'is_travel' => false, 'openPrice' => 5, 'kmPrice' => 2, 'minutePrice' => 1,
        ]);

        foreach ($officeIds as $officeId) {
            OfficeSubServicePrice::query()->create([
                'office_id' => $officeId, 'sub_service_id' => $sub->id,
                'openPrice' => 5, 'kmPrice' => 2, 'minutePrice' => 1,
            ]);
        }

        return $sub;
    }

    private function searchBody(array $overrides = []): array
    {
        return ['route' => array_merge([
            'pickup' => ['lat' => 25.28, 'lng' => 51.53],
            'dropoff' => ['lat' => 25.30, 'lng' => 51.55],
        ], $overrides)];
    }

    // ── office discovery ────────────────────────────────────────────────────

    /**
     * Only offices that actually publish the requested class are offered — an
     * office with no price row for it must not surface, or the rider books a
     * service the office does not sell.
     */
    public function test_offices_search_filters_by_published_service_class(): void
    {
        $this->office(3, 'Doha Cars', 25.28, 51.53);
        $this->office(5, 'Airport Express', 25.29, 51.54);

        $travel = $this->publish('Travel', [5]);

        $res = $this->asUser()->postJson('user/offices/search', $this->searchBody([
            'service' => $travel->serviceId,
            'serviceClass' => $travel->id,
        ]))->assertStatus(200);

        $this->assertCount(1, $res->json('data.offices'));
        $this->assertSame(5, $res->json('data.offices.0.id'));
        $this->assertSame('Airport Express', $res->json('data.offices.0.officeName'));
    }

    /** Every office publishing the class is returned when they all sell it. */
    public function test_offices_search_lists_all_offices_selling_the_class(): void
    {
        $this->office(3, 'Doha Cars', 25.28, 51.53);
        $this->office(5, 'Airport Express', 25.29, 51.54);

        $standard = $this->publish('Standard', [3, 5]);

        $res = $this->asUser()->postJson('user/offices/search', $this->searchBody([
            'service' => $standard->serviceId,
            'serviceClass' => $standard->id,
        ]))->assertStatus(200);

        $this->assertCount(2, $res->json('data.offices'));
    }

    /** Cards are ordered by the office's own distance to the pickup point. */
    public function test_offices_search_orders_nearest_first(): void
    {
        $this->office(3, 'Far Office', 25.60, 51.90);
        $this->office(5, 'Near Office', 25.281, 51.531);

        $standard = $this->publish('Standard', [3, 5]);

        $res = $this->asUser()->postJson('user/offices/search', $this->searchBody([
            'service' => $standard->serviceId,
            'serviceClass' => $standard->id,
        ]))->assertStatus(200);

        $this->assertSame(5, $res->json('data.offices.0.id'));
        $this->assertSame(3, $res->json('data.offices.1.id'));
        $this->assertNotNull($res->json('data.offices.0.eta_minutes'));
    }

    /** A class the catalogue does not carry matches no supply — empty, not an error. */
    public function test_offices_search_unknown_class_returns_nothing(): void
    {
        $this->office(3, 'Doha Cars', 25.28, 51.53);
        $this->publish('Standard', [3]);

        $this->asUser()->postJson('user/offices/search', $this->searchBody(['serviceClass' => 'nope']))
            ->assertStatus(200)
            ->assertJsonPath('data.offices', []);
    }

    public function test_offices_search_requires_a_route(): void
    {
        $this->asUser()->postJson('user/offices/search', ['route' => ['pickup' => ['lat' => 25.28, 'lng' => 51.53]]])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    /** With a serviceClass on file the card carries a quoted fare from the tariff engine. */
    public function test_offices_search_quotes_a_fare_when_a_tariff_exists(): void
    {
        $this->office(5, 'Airport Express', 25.28, 51.53);
        $standard = $this->publish('Standard', [5]);

        ServiceTariff::query()->create([
            'office_id' => 5, 'service' => (string) $standard->serviceId, 'service_class' => (string) $standard->id,
            'currency_code' => 'USD', 'pricing_style' => 'fixed', 'fixed_minor' => 5000,
        ]);

        $res = $this->asUser()->postJson('user/offices/search', $this->searchBody([
            'service' => $standard->serviceId,
            'serviceClass' => $standard->id,
        ]))->assertStatus(200);

        $this->assertSame(5000, $res->json('data.offices.0.fare_minor'));
        $this->assertSame('fixed', $res->json('data.offices.0.pricing_style'));
        $this->assertSame('USD', $res->json('data.offices.0.currency_code'));
    }

    // ── wallet statement ────────────────────────────────────────────────────

    public function test_wallet_transactions_lists_ledger_entries(): void
    {
        (new FleetWalletService(new LedgerService()))->topUp(7, 9000, 'USD', 'gap-topup', 'test', 1);

        $res = $this->asUser()->getJson('user/wallet/transactions?currency_code=USD')->assertStatus(200);

        $this->assertNotEmpty($res->json('data.items'));
        // Decimal money, not minor units; a credit is positive. Cast because a
        // whole-number float serialises to the JSON integer 90.
        $this->assertSame(90.0, (float) $res->json('data.items.0.amount'));
        $this->assertSame(90.0, (float) $res->json('data.items.0.balance_after'));
        $this->assertSame('topup', $res->json('data.items.0.transaction_type'));
    }

    /** One rider's statement never contains another rider's entries. */
    public function test_wallet_transactions_are_scoped_to_the_caller(): void
    {
        $wallet = new FleetWalletService(new LedgerService());
        $wallet->topUp(7, 9000, 'USD', 'gap-topup-7', 'test', 1);
        $wallet->topUp(8, 1500, 'USD', 'gap-topup-8', 'test', 1);

        $mine = $this->asUser(7)->getJson('user/wallet/transactions?currency_code=USD')->assertStatus(200);
        $this->assertCount(1, $mine->json('data.items'));
        $this->assertSame(90.0, (float) $mine->json('data.items.0.amount'));

        $theirs = $this->asUser(8)->getJson('user/wallet/transactions?currency_code=USD')->assertStatus(200);
        $this->assertCount(1, $theirs->json('data.items'));
        $this->assertSame(15.0, (float) $theirs->json('data.items.0.amount'));
    }

    // ── trip sharing ────────────────────────────────────────────────────────

    public function test_shared_trip_page_valid_and_invalid_token(): void
    {
        $booking = new RideBooking();
        $booking->id = 900;
        $booking->forceFill([
            'user_id' => 7, 'office_id' => 3, 'service' => 'ride', 'service_class' => 'standard',
            'pricing_style' => 'meter', 'status' => 'matching',
            'pickup_lat' => 25.1, 'pickup_lng' => 51.2, 'pickup_title' => 'Home',
            'dropoff_lat' => 25.2, 'dropoff_lng' => 51.3, 'dropoff_title' => 'Airport',
            'currency_code' => 'USD',
        ]);
        $booking->save();

        $share = app(RideBookingService::class)->share(7, 900);
        $url = parse_url($share['share_url'], PHP_URL_PATH);

        $this->get($url)->assertStatus(200)->assertSee('Airport');
        $this->get('/t/900-badtoken')->assertStatus(404);
    }
}
