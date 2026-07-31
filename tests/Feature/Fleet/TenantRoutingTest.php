<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\GeoServices\ShardContext;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\City;
use App\Models\Driver;
use App\Models\FixedTripMeta;
use App\Models\InfrastructureNode;
use App\Models\Office;
use App\Models\OfficeSubServicePrice;
use App\Models\RideBooking;
use App\Models\Service;
use App\Models\SubService;
use App\Models\TravelRoutes;
use App\Models\Vehicle;
use App\Models\VehicleBrand;
use App\Models\VehicleColor;
use App\Models\VehicleModel;
use App\Models\ContactMessage;
use App\Models\FleetStatistic;
use App\Models\HelpSuggestion;
use App\Models\Issue;
use App\Models\IssueLog;
use App\Models\OfficeStatistic;
use App\Models\Reply;
use App\Models\WalletBalance;
use App\Models\WalletTransaction;
use App\Models\WalletTransactionGroup;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Country-owned tables must follow the active country.
 *
 * Every model here was found reading the PLATFORM database while the panel
 * wrote to the shard: an office edited its prices in Syria and the rider search
 * never saw them, and the fixed-trip engine searched 424 platform corridors
 * instead of Syria's 5. It went unnoticed because two of the three countries
 * ARE the platform database. Losing the trait again would be silent, so it is
 * pinned here.
 */
class TenantRoutingTest extends TestCase
{
    public static function countryOwnedModels(): array
    {
        return [
            'offices' => [Office::class],
            'drivers' => [Driver::class],
            'vehicles' => [Vehicle::class],
            'services' => [Service::class],
            'sub-services' => [SubService::class],
            'office prices' => [OfficeSubServicePrice::class],
            'corridors' => [TravelRoutes::class],
            'cities' => [City::class],
            'bookings' => [RideBooking::class],
            'fixed trip meta' => [FixedTripMeta::class],
            'vehicle brands' => [VehicleBrand::class],
            'vehicle models' => [VehicleModel::class],
            'vehicle colours' => [VehicleColor::class],
            'wallet transactions' => [WalletTransaction::class],
            'wallet transaction groups' => [WalletTransactionGroup::class],
            'wallet balances' => [WalletBalance::class],
            'support issues' => [Issue::class],
            'issue logs' => [IssueLog::class],
            'issue replies' => [Reply::class],
            'help suggestions' => [HelpSuggestion::class],
            'office statistics' => [OfficeStatistic::class],
            'fleet statistics' => [FleetStatistic::class],
            'contact messages' => [ContactMessage::class],
        ];
    }

    #[DataProvider('countryOwnedModels')]
    public function test_a_country_owned_model_follows_the_active_shard(string $model): void
    {
        ShardContext::clear();

        $default = (new $model())->getConnectionName();
        $this->assertNotSame(TenantConnection::NAME, $default, 'with no country active the model must fall back, not pin the tenant connection');

        ShardContext::set((new InfrastructureNode())->forceFill([
            'id' => 1, 'country_code' => 'SY', 'db_name' => 'fleet_sy', 'is_active' => true,
        ]));

        $this->assertSame(TenantConnection::NAME, (new $model())->getConnectionName());

        ShardContext::clear();

        $this->assertSame($default, (new $model())->getConnectionName());
    }
}
