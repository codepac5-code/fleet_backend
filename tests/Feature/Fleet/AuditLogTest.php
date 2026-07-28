<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Audit\AuditLogService;

class AuditLogTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_25_000013_create_audit_logs_table.php',
    ];

    private AuditLogService $audit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->audit = new AuditLogService();
    }

    public function test_records_and_reads_by_subject(): void
    {
        $this->audit->record('ride.assigned', 'driver', 9, 'booking', 5001, ['office_id' => 3]);
        $this->audit->record('ride.completed', 'driver', 9, 'booking', 5001, []);

        $logs = $this->audit->forSubject('booking', 5001);

        $this->assertCount(2, $logs);
        $this->assertSame('ride.completed', $logs[0]->action);
        $this->assertSame(3, $logs[1]->metadata['office_id']);
    }

    public function test_reads_by_actor_and_is_append_only(): void
    {
        $this->audit->record('login', 'user', 7);
        $this->audit->record('wallet.topup', 'user', 7, null, null, ['amount_minor' => 10000]);

        $logs = $this->audit->forActor('user', 7);

        $this->assertCount(2, $logs);
        $this->assertNull($logs[0]->updated_at ?? null);
        $this->assertSame(10000, $logs[0]->metadata['amount_minor']);
    }
}
