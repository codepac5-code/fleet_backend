<?php

namespace Tests\Feature\Fleet;

use App\Models\Office;
use App\Models\RideBooking;
use App\Models\ServiceTariff;
use App\Models\User;

class RiderV2ScheduledTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2024_10_29_211028_create_offices_table.php',
        '2026_06_25_000005_create_dispatch_jobs_table.php',
        '2026_06_25_000017_create_ride_ratings_table.php',
        '2026_07_01_000002_create_service_tariffs_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_11_000004_add_titles_to_ride_bookings_table.php',
        '2026_07_11_000008_add_schedule_to_ride_bookings_table.php',
        '2026_07_11_000009_add_change_revision_to_ride_bookings_table.php',
        '2026_07_14_000001_add_office_booking_fields_to_ride_bookings.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
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

    private function seedTariff(int $office): void
    {
        ServiceTariff::query()->create([
            'office_id' => $office, 'service' => 'travel', 'service_class' => 'standard',
            'currency_code' => 'USD', 'pricing_style' => 'fixed', 'fixed_minor' => 6000,
            'base_minor' => 500, 'per_km_minor' => 200, 'per_minute_minor' => 30, 'minimum_minor' => 1000,
        ]);
    }

    private function payload(int $officeId, array $override = []): array
    {
        return array_merge([
            'office_id' => $officeId,
            'route' => [
                'pickup' => ['lat' => 25.2854, 'lng' => 51.531, 'title' => 'Home'],
                'dropoff' => ['lat' => 25.27, 'lng' => 51.60, 'title' => 'Airport'],
                'service' => 'travel', 'serviceClass' => 'standard',
            ],
            'scheduledFor' => now()->addDay()->toIso8601String(),
            'passengers' => 2, 'luggage' => 1, 'flightNo' => 'QR1234',
        ], $override);
    }

    public function test_schedule_a_ride(): void
    {
        $office = $this->office();
        $this->seedTariff($office->id);

        $res = $this->asUser()->postJson('user/scheduled', $this->payload($office->id))
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'scheduled')
            ->assertJsonPath('data.service', 'travel')
            ->assertJsonPath('data.passengers', 2)
            ->assertJsonPath('data.flight_no', 'QR1234')
            ->assertJsonPath('data.steps.0.key', 'scheduled')
            ->assertJsonPath('data.steps.0.status', 'done');

        $this->assertNotNull($res->json('data.scheduled_at'));
    }

    public function test_show_scheduled_owner_only(): void
    {
        $office = $this->office();
        $this->seedTariff($office->id);
        $id = $this->asUser()->postJson('user/scheduled', $this->payload($office->id))->json('data.id');

        $this->asUser()->getJson("user/scheduled/{$id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $id)
            ->assertJsonPath('data.steps.1.key', 'matching');

        $this->asUser(8)->getJson("user/scheduled/{$id}")->assertStatus(404);
    }

    public function test_update_bumps_change_revision(): void
    {
        $office = $this->office();
        $this->seedTariff($office->id);
        $id = $this->asUser()->postJson('user/scheduled', $this->payload($office->id))->json('data.id');

        $this->asUser()->patchJson("user/scheduled/{$id}", ['passengers' => 4, 'scheduledFor' => now()->addDays(2)->toIso8601String()])
            ->assertStatus(200)
            ->assertJsonPath('data.passengers', 4)
            ->assertJsonPath('data.change_revision', 1);
    }

    public function test_cancel_scheduled(): void
    {
        $office = $this->office();
        $this->seedTariff($office->id);
        $id = $this->asUser()->postJson('user/scheduled', $this->payload($office->id))->json('data.id');

        $this->asUser()->deleteJson("user/scheduled/{$id}")->assertStatus(204);
        $this->assertSame('cancelled', RideBooking::query()->find($id)->status);
    }
}
