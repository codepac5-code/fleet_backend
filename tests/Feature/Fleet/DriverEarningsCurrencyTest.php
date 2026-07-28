<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Repositories\Ledger\EloquentDriverStatementRepository;
use App\Models\CommissionSnapshot;
use App\Models\RideBooking;

/**
 * A driver's earnings report must never hide money the ledger holds.
 *
 * `earnings()` is asked for a currency resolved from the driver's COUNTRY
 * (`DriverCurrency::resolve` → QAR for a QA driver), while every commission
 * snapshot carries the currency of the OFFICE's tariff. Those disagree the
 * moment an office prices in something else — office 1 prices in SAR — and the
 * old query filtered snapshots to the requested currency, matched nothing, and
 * returned `trips: 0` with every figure at zero. `driver/home` counted the very
 * same completed trip as 1, so the two screens contradicted each other and the
 * driver was shown no earnings for a cash trip they had actually driven.
 */
class DriverEarningsCurrencyTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_24_000004_create_commission_snapshots_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
    ];

    private function seedCompletedTrip(int $bookingId, string $currency, string $payment = 'cash'): void
    {
        $b = new RideBooking();
        $b->id = $bookingId;
        $b->forceFill([
            'user_id' => 7, 'office_id' => 1, 'service' => 'ride', 'service_class' => 'standard',
            'pricing_style' => 'meter', 'status' => 'completed',
            'pickup_lat' => 25.1, 'pickup_lng' => 51.2, 'dropoff_lat' => 25.2, 'dropoff_lng' => 51.3,
            'currency_code' => $currency, 'fare_minor' => 2450, 'total_minor' => 2450,
            'held_minor' => 0, 'payment_method' => $payment, 'driver_id' => 5,
        ]);
        $b->save();

        CommissionSnapshot::query()->create([
            'booking_id' => $bookingId,
            'driver_id' => 5,
            'currency_code' => $currency,
            'total_minor' => 2450,
            'fare_minor' => 2450,
            'discount_minor' => 0,
            'driver_minor' => 2009,
            'fleet_minor' => 441,
            'office_minor' => 0,
            'pricing_style' => 'meter',
        ]);
    }

    private function repo(): EloquentDriverStatementRepository
    {
        return new EloquentDriverStatementRepository();
    }

    /** The ordinary case still behaves exactly as before. */
    public function test_reports_the_requested_currency_when_it_has_rows(): void
    {
        $this->seedCompletedTrip(600, 'QAR');

        $e = $this->repo()->earnings(5, 'QAR', null);

        $this->assertSame('QAR', $e['currency_code']);
        $this->assertSame(1, $e['trips']);
        $this->assertSame(2450, $e['gross_minor']);
    }

    /**
     * The regression: everything the driver earned was in SAR while the report
     * asked for QAR.
     */
    public function test_falls_back_to_the_currency_the_driver_was_actually_paid_in(): void
    {
        $this->seedCompletedTrip(601, 'SAR');

        $e = $this->repo()->earnings(5, 'QAR', null);

        $this->assertSame('SAR', $e['currency_code'], 'must report the currency that actually holds the money');
        $this->assertSame(1, $e['trips'], 'a completed trip must never report as zero trips');
        $this->assertSame(2450, $e['gross_minor']);
        $this->assertSame(2450, $e['cash_collected_minor'], 'cash the driver collected must be visible');
        $this->assertSame(441, $e['fees_minor']);
    }

    /** No snapshots at all is a genuine zero, not a currency problem. */
    public function test_no_snapshots_reports_zero_in_the_requested_currency(): void
    {
        $e = $this->repo()->earnings(5, 'QAR', null);

        $this->assertSame('QAR', $e['currency_code']);
        $this->assertSame(0, $e['trips']);
        $this->assertSame(0, $e['gross_minor']);
    }
}
