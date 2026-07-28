<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerService;
use App\Models\EventOutbox;
use App\Models\Office;
use App\Models\ServiceTariff;
use App\Models\User;

class RiderV2BookingTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2024_10_29_211028_create_offices_table.php',
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_06_24_000004_create_commission_snapshots_table.php',
        '2026_06_25_000003_create_ledger_payments_table.php',
        '2026_06_25_000004_create_driver_presence_table.php',
        '2026_06_25_000005_create_dispatch_jobs_table.php',
        '2026_06_25_000006_create_dispatch_offers_table.php',
        '2026_06_25_000007_create_event_outbox_table.php',
        '2026_06_25_000017_create_ride_ratings_table.php',
        '2026_07_01_000002_create_service_tariffs_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_11_000004_add_titles_to_ride_bookings_table.php',
        '2026_07_11_000008_add_schedule_to_ride_bookings_table.php',
        '2026_07_11_000009_add_change_revision_to_ride_bookings_table.php',
        '2026_07_14_000001_add_office_booking_fields_to_ride_bookings.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
        '2026_07_16_000002_add_stops_to_ride_bookings.php',
    ];

    private function asUser(int $id = 7): self
    {
        $user = new User();
        $user->id = $id;

        return $this->actingAs($user, 'user');
    }

    private function office(): Office
    {
        return Office::query()->create([
            'officeName' => 'Al Fleet', 'email' => 'o@x.qa', 'password' => 'x',
            'contactNumber' => '33001234', 'address' => 'West Bay, Doha', 'country' => 'QA',
            'city' => 'Doha', 'region' => 'Doha', 'status' => 1, 'is_verified' => true, 'lat' => 25.28, 'lng' => 51.53,
        ]);
    }

    private function seedTariff(int $office = 1, string $style = 'fixed', int $fixed = 5000): void
    {
        ServiceTariff::query()->create([
            'office_id' => $office, 'service' => 'ride', 'service_class' => 'standard',
            'currency_code' => 'USD', 'pricing_style' => $style,
            'base_minor' => 500, 'per_km_minor' => 200, 'per_minute_minor' => 30,
            'minimum_minor' => 1000, 'fixed_minor' => $fixed,
        ]);
    }

    private function fund(int $userId, int $amount): void
    {
        (new FleetWalletService(new LedgerService()))->topUp($userId, $amount, 'USD', 'fund:' . $userId, 'test', 1);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'service' => 'ride',
            'service_class' => 'standard',
            'office_id' => 1,
            'pickup_lat' => 25.2854, 'pickup_lng' => 51.5310,
            'pickup_title' => 'Al Sadd', 'pickup_note' => 'North gate, blue door',
            'dropoff_lat' => 25.2700, 'dropoff_lng' => 51.6000,
            'dropoff_title' => 'Airport',
        ], $override);
    }

    public function test_create_starts_matching_and_emits_status(): void
    {
        $this->office();
        $this->seedTariff();
        $this->fund(7, 8000);

        $res = $this->asUser()->postJson('user/bookings', $this->payload(['idempotency_key' => 'b1']))
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'matching')
            ->assertJsonPath('data.pricing_style', 'fixed')
            ->assertJsonPath('data.total_minor', 5000)
            ->assertJsonPath('data.held_minor', 5000)
            ->assertJsonPath('data.user_id', 7)
            ->assertJsonPath('data.pickup_note', 'North gate, blue door')
            ->assertJsonPath('data.source', 'rider');

        $id = $res->json('data.id');

        $event = EventOutbox::query()->where('type', 'booking.status_changed')->first();
        $this->assertNotNull($event);
        $this->assertSame('matching', $event->payload['status']);
        $this->assertSame('rider', $event->payload['source']);
        $this->assertContains('user.7', $event->channels);
        $this->assertContains('booking.' . $id, $event->channels);
    }

    public function test_create_cash_skips_hold(): void
    {
        $this->office();
        $this->seedTariff();

        $this->asUser()->postJson('user/bookings', $this->payload(['payment_method' => 'cash', 'idempotency_key' => 'b-cash']))
            ->assertStatus(201)
            ->assertJsonPath('data.held_minor', 0)
            ->assertJsonPath('data.status', 'matching');
    }

    public function test_create_insufficient_funds_is_422(): void
    {
        $this->office();
        $this->seedTariff();

        $this->asUser()->postJson('user/bookings', $this->payload(['idempotency_key' => 'b-nf']))
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'insufficient_funds');
    }

    public function test_create_missing_tariff_is_404(): void
    {
        $this->office();
        $this->fund(7, 8000);

        $this->asUser()->postJson('user/bookings', $this->payload(['office_id' => 999, 'idempotency_key' => 'b-nt']))
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'tariff_not_found');
    }

    public function test_create_is_idempotent_via_header(): void
    {
        $this->office();
        $this->seedTariff();
        $this->fund(7, 8000);

        $a = $this->asUser()->postJson('user/bookings', $this->payload(), ['Idempotency-Key' => 'hdr-1'])->assertStatus(201);
        $b = $this->asUser()->postJson('user/bookings', $this->payload(), ['Idempotency-Key' => 'hdr-1'])->assertStatus(201);

        $this->assertSame($a->json('data.id'), $b->json('data.id'));
    }

    public function test_validation_rejects_missing_coords(): void
    {
        $this->asUser()->postJson('user/bookings', ['service_class' => 'standard', 'office_id' => 3])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_show_returns_flat_row_with_office_and_owner_only(): void
    {
        $this->office();
        $this->seedTariff();
        $this->fund(7, 8000);

        $id = $this->asUser(7)->postJson('user/bookings', $this->payload(['idempotency_key' => 'b-own']))->json('data.id');

        $this->asUser(7)->getJson("user/bookings/{$id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $id)
            ->assertJsonPath('data.status', 'matching')
            ->assertJsonPath('data.office.officeName', 'Al Fleet');

        $this->asUser(8)->getJson("user/bookings/{$id}")->assertStatus(404);
    }

    public function test_create_persists_multi_stops_and_returns_them(): void
    {
        $this->office();
        $this->seedTariff();
        $this->fund(7, 8000);

        $stops = [
            ['lat' => 33.5138, 'lng' => 36.2765, 'title' => 'Umayyad Square'],
            ['lat' => 33.5020, 'lng' => 36.2900, 'title' => 'Abbasiyyin'],
        ];

        $id = $this->asUser()->postJson('user/bookings', $this->payload([
            'idempotency_key' => 'b-stops',
            'stops' => $stops,
        ]))->assertStatus(201)->json('data.id');

        // Persisted on the booking row, in the order the rider added them.
        $this->asUser()->getJson("user/bookings/{$id}")
            ->assertStatus(200)
            ->assertJsonPath('data.stops.0.title', 'Umayyad Square')
            ->assertJsonPath('data.stops.0.lat', 33.5138)
            ->assertJsonPath('data.stops.1.title', 'Abbasiyyin')
            ->assertJsonPath('data.stops.1.lng', 36.2900);
    }

    public function test_create_rejects_more_than_five_stops(): void
    {
        $this->office();
        $this->seedTariff();
        $this->fund(7, 8000);

        $stops = array_fill(0, 6, ['lat' => 33.51, 'lng' => 36.27]);

        $this->asUser()->postJson('user/bookings', $this->payload([
            'idempotency_key' => 'b-stops-max',
            'stops' => $stops,
        ]))->assertStatus(422);
    }

    public function test_cancel_refunds_and_emits_cancelled_with_source_reason(): void
    {
        $this->office();
        $this->seedTariff();
        $this->fund(7, 8000);

        $id = $this->asUser()->postJson('user/bookings', $this->payload(['idempotency_key' => 'b-cxl']))->json('data.id');

        $this->asUser()->postJson("user/bookings/{$id}/cancel", ['reason' => 'Changed my mind'])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled')
            ->assertJsonPath('data.cancel_reason', 'Changed my mind');

        $balance = (new FleetWalletService(new LedgerService()))->walletBalanceMinor('user', 7, 'USD');
        $this->assertSame(8000, $balance);

        $cancelled = EventOutbox::query()->where('type', 'booking.status_changed')
            ->get()->firstWhere(fn ($e) => ($e->payload['status'] ?? null) === 'cancelled');
        $this->assertNotNull($cancelled);
        $this->assertSame('rider', $cancelled->payload['source']);
        $this->assertSame('Changed my mind', $cancelled->payload['reason']);
    }
}
