<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Audit\AuditLogService;
use App\Http\Core\GeoServices\ShardContext;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Leads\Controller\ApproveOfficeRequestController;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\InfrastructureNode;
use App\Models\Office;
use App\Models\OfficeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Mockery;

class OfficeRequestApprovalTest extends FleetTestCase
{
    // office_requests is a website lead on the platform (default) connection.
    protected array $tenantMigrations = [
        '2026_03_29_111045_create_office_requests_table.php',
        '2026_07_28_000005_extend_office_request_status.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // Offices live on the COUNTRY shard, so the test needs a real `dynamic`
        // connection — that is the whole point of the approval path.
        config(['database.connections.' . TenantConnection::NAME => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);
        DB::purge(TenantConnection::NAME);

        $previous = DB::getDefaultConnection();
        DB::setDefaultConnection(TenantConnection::NAME);
        $this->runMigration('2024_10_29_211028_create_offices_table.php');
        $this->runMigration('2026_07_28_000006_add_display_name_to_offices.php');
        DB::setDefaultConnection($previous);
    }

    protected function tearDown(): void
    {
        ShardContext::clear();
        parent::tearDown();
    }

    private function activateCountry(): void
    {
        ShardContext::set(new InfrastructureNode(['country_code' => 'SY', 'name' => 'Syria']));
    }

    private function request(array $override = []): OfficeRequest
    {
        return OfficeRequest::query()->create(array_merge([
            'office_name' => 'Damascus Rides',
            'contact_name' => 'Sami',
            'email' => 'ops@damascus.example',
            'phone' => '+963900000',
            'city' => 'Damascus',
            'country' => 'Syria',
            'business_category' => 'taxi',
            'fleet_size' => 12,
            'service_type' => 'ride',
            'license_status' => 'licensed',
            'timeline' => 'asap',
            'status' => 'new',
        ], $override));
    }

    private function decide(OfficeRequest $record, string $decision)
    {
        $scope = Mockery::mock(EntityScope::class);
        $scope->shouldReceive('user')->andReturn(null);

        return (new ApproveOfficeRequestController())(
            Request::create('/', 'POST', ['decision' => $decision]),
            $scope,
            new OfficeRepository(),
            app(AuditLogService::class),
            (int) $record->id
        );
    }

    public function test_approving_creates_the_office_account(): void
    {
        $this->activateCountry();
        $record = $this->request();

        $response = $this->decide($record, 'approve');

        $office = Office::on(TenantConnection::NAME)->where('email', 'ops@damascus.example')->first();
        $this->assertNotNull($office, 'approval provisions the office, it no longer just flips a label');
        $this->assertSame('Damascus Rides', $office->officeName);
        $this->assertSame('sy', strtolower((string) $office->country));
        $this->assertSame(1, (int) $office->status);
        $this->assertSame('approved', $record->refresh()->status);

        $credentials = $response->getSession()->get('office_credentials');
        $this->assertNotEmpty($credentials['password']);
        $this->assertTrue(Hash::check($credentials['password'], $office->password), 'the shown password is the one that was stored, hashed');
    }

    public function test_approving_twice_does_not_create_a_second_office(): void
    {
        $this->activateCountry();
        $record = $this->request();

        $this->decide($record, 'approve');
        $this->decide($record, 'approve');

        $this->assertSame(1, Office::on(TenantConnection::NAME)->where('email', 'ops@damascus.example')->count());
    }

    public function test_rejecting_creates_nothing(): void
    {
        $this->activateCountry();
        $record = $this->request();

        $this->decide($record, 'reject');

        $this->assertSame('rejected', $record->refresh()->status);
        $this->assertSame(0, Office::on(TenantConnection::NAME)->count());
    }

    public function test_approval_without_an_active_country_is_refused(): void
    {
        $record = $this->request();

        $response = $this->decide($record, 'approve');

        $this->assertSame(0, Office::on(TenantConnection::NAME)->count(), 'no country means no database to create the office in');
        $this->assertSame('new', $record->refresh()->status);
        $this->assertNotEmpty($response->getSession()->get('error'));
    }
}
