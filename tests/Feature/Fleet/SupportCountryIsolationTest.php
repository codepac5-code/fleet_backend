<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\GeoServices\ShardContext;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Support\Controller\UpdateComplaintStatusController;
use App\Models\Complaint;
use App\Models\InfrastructureNode;
use App\Models\LostItem;
use App\Models\RideBooking;
use Illuminate\Http\Request;
use Mockery;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SupportCountryIsolationTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_07_15_000001_add_rider_api_missing_columns.php',
        '2026_07_23_000001_add_governance_to_lost_items.php',
        '2026_07_25_000005_add_country_to_support_tables.php',
        '2026_07_28_000001_add_office_to_complaints.php',
    ];

    protected array $tenantMigrations = [
        '2026_07_11_000001_create_ride_bookings_table.php',
    ];

    protected function tearDown(): void
    {
        ShardContext::clear();
        parent::tearDown();
    }

    private function activateCountry(string $code): void
    {
        ShardContext::set(new InfrastructureNode(['country_code' => $code]));
    }

    public function test_lost_item_is_stamped_with_the_active_country(): void
    {
        $this->activateCountry('SY');

        $item = LostItem::query()->create([
            'user_id' => 1, 'booking_id' => 10, 'office_id' => 3, 'category' => 'Bag', 'status' => 'reported',
        ]);

        $this->assertSame('sy', $item->country_code);
    }

    public function test_complaint_is_stamped_with_the_active_country(): void
    {
        $this->activateCountry('QA');

        $complaint = Complaint::query()->create([
            'user_id' => 1, 'booking_id' => 10, 'about' => 'driver', 'description' => 'x', 'status' => 'open',
        ]);

        $this->assertSame('qa', $complaint->country_code);
    }

    public function test_no_active_country_leaves_the_stamp_null(): void
    {
        $item = LostItem::query()->create([
            'user_id' => 1, 'booking_id' => 10, 'office_id' => 3, 'category' => 'Bag', 'status' => 'reported',
        ]);

        $this->assertNull($item->country_code);
    }

    private function booking(int $officeId): RideBooking
    {
        return RideBooking::query()->create([
            'user_id' => 1, 'office_id' => $officeId, 'service' => 'ride', 'service_class' => 'economy',
            'pricing_style' => 'meter', 'status' => 'completed',
            'pickup_lat' => 33.5, 'pickup_lng' => 36.3, 'dropoff_lat' => 33.6, 'dropoff_lng' => 36.4,
            'currency_code' => 'SYP',
        ]);
    }

    private function updateStatus(Complaint $complaint, bool $isAdmin, ?int $officeId): void
    {
        $scope = Mockery::mock(EntityScope::class);
        $scope->shouldReceive('isAdmin')->andReturn($isAdmin);
        $scope->shouldReceive('officeId')->andReturn($officeId);

        (new UpdateComplaintStatusController())(
            Request::create('/', 'POST', ['status' => 'in_review']),
            $scope,
            (int) $complaint->id
        );
    }

    public function test_complaint_is_stamped_with_the_office_of_its_booking(): void
    {
        $booking = $this->booking(7);

        $complaint = Complaint::query()->create([
            'user_id' => 1, 'booking_id' => $booking->id, 'about' => 'driver', 'description' => 'x', 'status' => 'open',
        ]);

        $this->assertSame(7, $complaint->office_id);
    }

    public function test_complaint_without_a_booking_stays_office_less(): void
    {
        $complaint = Complaint::query()->create([
            'user_id' => 1, 'about' => 'other', 'description' => 'x', 'status' => 'open',
        ]);

        $this->assertNull($complaint->office_id);
    }

    public function test_office_cannot_change_the_status_of_another_offices_complaint(): void
    {
        $complaint = Complaint::query()->create([
            'user_id' => 1, 'booking_id' => $this->booking(7)->id, 'about' => 'driver', 'description' => 'x', 'status' => 'open',
        ]);

        $this->expectException(NotFoundHttpException::class);
        $this->updateStatus($complaint, false, 8);
    }

    public function test_office_can_change_the_status_of_its_own_complaint(): void
    {
        $complaint = Complaint::query()->create([
            'user_id' => 1, 'booking_id' => $this->booking(7)->id, 'about' => 'driver', 'description' => 'x', 'status' => 'open',
        ]);

        $this->updateStatus($complaint, false, 7);

        $this->assertSame('in_review', $complaint->refresh()->status);
    }

    public function test_status_change_across_countries_is_rejected(): void
    {
        $this->activateCountry('SY');
        $complaint = Complaint::query()->create([
            'user_id' => 1, 'about' => 'other', 'description' => 'x', 'status' => 'open',
        ]);

        $this->activateCountry('QA');

        $this->expectException(NotFoundHttpException::class);
        $this->updateStatus($complaint, true, null);
    }

    public function test_same_office_id_in_two_countries_stays_isolated(): void
    {
        // office #3 exists in BOTH countries; the country stamp is what keeps their
        // lost items apart on a global table.
        $this->activateCountry('SY');
        LostItem::query()->create(['user_id' => 1, 'booking_id' => 1, 'office_id' => 3, 'category' => 'Bag', 'status' => 'reported']);

        $this->activateCountry('QA');
        LostItem::query()->create(['user_id' => 2, 'booking_id' => 1, 'office_id' => 3, 'category' => 'Keys', 'status' => 'reported']);

        $syForOffice3 = LostItem::query()->where('country_code', 'sy')->where('office_id', 3)->get();
        $this->assertCount(1, $syForOffice3);
        $this->assertSame('Bag', $syForOffice3->first()->category);
    }
}
