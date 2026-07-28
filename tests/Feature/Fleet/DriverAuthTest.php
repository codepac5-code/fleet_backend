<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Auth\DriverTokenIssuer;
use App\Models\Driver;
use App\Models\DriverApplication;
use Illuminate\Support\Facades\Cache;

class DriverAuthTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2024_11_10_085532_create_drivers_table.php',
        '2026_07_13_000002_create_driver_applications_table.php',
        // DriverAuthService::apply writes license_path and the personal fields
        // (first_name/last_name/gender/country/region/address/car_owner), all
        // added to driver_applications after the create migration.
        '2026_07_18_000001_add_license_path_to_driver_applications.php',
        '2026_07_18_000002_add_personal_fields_to_driver_applications.php',
        // The driver profile resolves its office...
        '2024_10_29_211028_create_offices_table.php',
        // ...and its rating from the real ride_ratings average.
        '2026_06_25_000017_create_ride_ratings_table.php',
        '2024_11_6_212751_create_vehicles_table.php',
    ];

    private string $phone = '+97455123456';

    protected function setUp(): void
    {
        parent::setUp();

        config(['cache.default' => 'array']);
        Cache::flush();

        \Illuminate\Support\Facades\DB::connection((new Driver)->getConnectionName())->statement('PRAGMA foreign_keys = OFF');

        $this->app->bind(DriverTokenIssuer::class, fn () => new class implements DriverTokenIssuer {
            public function issue(Driver $driver, string $name): string
            {
                return 'driver-token-' . $driver->id;
            }

            public function revokeCurrent(Driver $driver): void
            {
            }
        });
    }

    private function createDriver(bool $active): Driver
    {
        $d = new Driver();
        $d->forceFill([
            'firstName' => 'John', 'lastName' => 'Smith', 'phoneNumber' => '55123456', 'dialCode' => '+974',
            'password' => 'x', 'address' => 'Doha', 'country' => 'QA', 'city' => 'Doha', 'region' => 'WB',
            'vehicleId' => 1, 'officeId' => 3, 'isActive' => $active ? 1 : 0,
        ]);
        $d->save();

        return $d;
    }

    private function code(): string
    {
        return (string) Cache::get('driver:otp:code:' . $this->phone);
    }

    public function test_request_otp_stores_code(): void
    {
        $this->postJson('driver/auth/otp/request', ['phone' => $this->phone])
            ->assertStatus(200)
            ->assertJsonPath('data.otp_sent', true);

        $this->assertNotNull($this->code());
    }

    public function test_verify_unregistered_phone(): void
    {
        $this->postJson('driver/auth/otp/request', ['phone' => $this->phone]);

        $this->postJson('driver/auth/otp/verify', ['phone' => $this->phone, 'code' => $this->code()])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'not_registered');
    }

    public function test_verify_pending_driver(): void
    {
        $this->createDriver(false);
        $this->postJson('driver/auth/otp/request', ['phone' => $this->phone]);

        $this->postJson('driver/auth/otp/verify', ['phone' => $this->phone, 'code' => $this->code()])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonMissingPath('data.access_token');
    }

    public function test_verify_active_driver_issues_token(): void
    {
        $driver = $this->createDriver(true);
        $this->postJson('driver/auth/otp/request', ['phone' => $this->phone]);

        $this->postJson('driver/auth/otp/verify', ['phone' => $this->phone, 'code' => $this->code()])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.access_token', 'driver-token-' . $driver->id);
    }

    /**
     * "Apply to drive" moved from the dead `driver/auth/apply` to the public
     * onboarding route `POST driver/applications`
     * (DriverApplicationsController::apply).
     *
     * The live request contract is much stricter than the old one: first_name,
     * last_name, country, city, region and address are all `required`, and the
     * response is {id, status} — there is no `application_id` key.
     *
     * DriverAuthService::apply derives `kind` from the payload: an application
     * carrying office_id or invite_code is a `link` request, anything else is
     * an `apply`. This one supplies neither, so it must land as `apply`.
     */
    public function test_apply_creates_application(): void
    {
        $this->postJson('driver/applications', [
            'phone' => $this->phone, 'first_name' => 'John', 'last_name' => 'Smith',
            'country' => 'Qatar', 'city' => 'Doha', 'region' => 'WB', 'address' => 'Al Sadd',
            'vehicle_type' => 'Standard sedan', 'license_number' => 'QA-DR-10284',
        ])->assertStatus(201)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.id', fn ($id) => is_int($id) && $id > 0);

        $this->assertSame(1, DriverApplication::query()->where('kind', 'apply')->count());

        $application = DriverApplication::query()->first();
        // full_name/name are optional; the controller composes one from the parts
        $this->assertSame('John Smith', $application->name);
        $this->assertSame('QA-DR-10284', $application->license_number);
    }

    /** The identity fields are required — an empty apply must not create a row. */
    public function test_apply_requires_identity_fields(): void
    {
        $this->postJson('driver/applications', ['phone' => $this->phone])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');

        $this->assertSame(0, DriverApplication::query()->count());
    }

    /** Supplying an office turns the same submission into a `link` request. */
    public function test_apply_with_office_is_a_link_request(): void
    {
        $this->postJson('driver/applications', [
            'phone' => $this->phone, 'first_name' => 'John', 'last_name' => 'Smith',
            'country' => 'Qatar', 'city' => 'Doha', 'region' => 'WB', 'address' => 'Al Sadd',
            'office_id' => 3,
        ])->assertStatus(201);

        $this->assertSame(1, DriverApplication::query()->where('kind', 'link')->count());
    }

    public function test_verify_wrong_code_is_invalid(): void
    {
        $this->postJson('driver/auth/otp/request', ['phone' => $this->phone]);

        $this->postJson('driver/auth/otp/verify', ['phone' => $this->phone, 'code' => '0000'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'invalid_code');
    }
}
