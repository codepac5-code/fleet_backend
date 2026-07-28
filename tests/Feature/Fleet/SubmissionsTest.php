<?php

namespace Tests\Feature\Fleet;

use App\Models\Admin;
use App\Models\DriverJobApplication;

class SubmissionsTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_05_29_220120_create_infrastructure_nodes_table.php',
    ];

    protected array $tenantMigrations = [
        '2025_06_18_120930_create_driver_job_applications_table.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $admin = new Admin();
        $admin->id = 1;
        $this->actingAs($admin, 'admin');
    }

    private function makeApplication(string $phone = '+974111'): DriverJobApplication
    {
        return DriverJobApplication::query()->create([
            'name' => 'Ali', 'phoneNumber' => $phone, 'password' => 'x',
            'brand' => 'Toyota', 'model' => 'Camry', 'year' => '2022', 'color' => 'White', 'plateNumber' => 'ABC123',
            'status' => 'pending',
        ]);
    }

    public function test_approve_updates_status(): void
    {
        $app = $this->makeApplication();

        $this->patch('/admin/submissions/drivers/' . $app->id . '/status', ['status' => 'approved'])->assertRedirect();

        $this->assertSame('approved', $app->fresh()->status);
    }

    public function test_reject_updates_status(): void
    {
        $app = $this->makeApplication('+974222');

        $this->patch('/admin/submissions/drivers/' . $app->id . '/status', ['status' => 'rejected'])->assertRedirect();

        $this->assertSame('rejected', $app->fresh()->status);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $app = $this->makeApplication('+974333');

        $this->patch('/admin/submissions/drivers/' . $app->id . '/status', ['status' => 'bogus'])->assertSessionHasErrors('status');

        $this->assertSame('pending', $app->fresh()->status);
    }
}
