<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerService;
use App\Http\Core\Classes\Ride\ScheduledActivationService;
use App\Models\DispatchJob;
use App\Models\RideBooking;

class ScheduledActivationTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_06_25_000004_create_driver_presence_table.php',
        '2026_07_13_000003_add_busy_reason_to_driver_presence_table.php',
        '2026_06_25_000005_create_dispatch_jobs_table.php',
        '2026_06_25_000006_create_dispatch_offers_table.php',
        '2026_06_25_000007_create_event_outbox_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_11_000004_add_titles_to_ride_bookings_table.php',
        '2026_07_11_000008_add_schedule_to_ride_bookings_table.php',
    ];

    private function wallet(): FleetWalletService
    {
        return new FleetWalletService(new LedgerService());
    }

    private function seedScheduled(int $id, string $scheduledAt, int $total = 5000, string $payment = 'wallet'): void
    {
        $b = new RideBooking();
        $b->id = $id;
        $b->forceFill([
            'user_id' => 7, 'office_id' => 3, 'service' => 'travel', 'service_class' => 'standard',
            'pricing_style' => 'fixed', 'status' => 'scheduled', 'scheduled_at' => $scheduledAt,
            'pickup_lat' => 25.1, 'pickup_lng' => 51.2, 'dropoff_lat' => 25.2, 'dropoff_lng' => 51.3,
            'currency_code' => 'USD', 'fare_minor' => $total, 'total_minor' => $total,
            'held_minor' => 0, 'payment_method' => $payment,
        ]);
        $b->save();
    }

    public function test_due_scheduled_is_activated_held_and_dispatched(): void
    {
        $this->wallet()->topUp(7, 5000, 'USD', 'fund:950', 'test', 1);
        $this->seedScheduled(950, now()->addMinutes(30)->toDateTimeString());   // within 2h lead → due
        $this->seedScheduled(951, now()->addDays(3)->toDateTimeString());       // far future → not due

        $activated = app(ScheduledActivationService::class)->activateDue(7200);

        $this->assertSame(1, $activated);
        $this->assertSame('matching', RideBooking::query()->find(950)->status);
        $this->assertSame(5000, (int) RideBooking::query()->find(950)->held_minor);
        $this->assertSame(5000, $this->wallet()->escrowBalanceMinor(950, 'USD'));
        $this->assertTrue(DispatchJob::query()->where('booking_id', 950)->exists());

        $this->assertSame('scheduled', RideBooking::query()->find(951)->status);
    }
}
