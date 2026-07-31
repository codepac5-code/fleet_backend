<?php

namespace Tests\Feature\Fleet;

use App\Models\FavoriteOffice;
use App\Models\Office;
use App\Models\OfficeSubServicePrice;
use App\Models\SavedPlace;
use App\Models\Service;
use App\Models\SubService;
use App\Models\User;

class RiderV2MarketplaceTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_07_11_000003_create_saved_places_table.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
    ];

    protected array $tenantMigrations = [
        '2024_10_23_085910_create_users_table.php',
        '2024_10_29_211028_create_offices_table.php',
        '2024_10_26_104402_create_services_table.php',
        '2024_10_26_104427_create_sub_services_table.php',
        '2026_01_03_025343_create_office_sub_service_prices_table.php',
        '2026_07_29_000001_add_enabled_to_office_sub_service_prices.php',
        '2026_06_25_000012_create_favorite_offices_table.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
        // Office search quotes each office through the tariff engine.
        '2026_07_01_000002_create_service_tariffs_table.php',
        '2026_07_11_000002_add_service_to_service_tariffs_table.php',
        // Place suggestions resolve the caller's country to bias results.
        '2024_11_12_070712_create_countries_table.php',
    ];

    private function user(): User
    {
        return User::query()->create([
            'firstName' => 'Test', 'lastName' => 'Rider', 'phoneNumber' => '+97455123456',
            'dialCode' => '+974', 'password' => 'x', 'isActive' => 1,
        ]);
    }

    private function office(): Office
    {
        return Office::query()->create([
            'officeName' => 'Al Fleet', 'email' => 'o@x.qa', 'password' => 'x',
            'contactNumber' => '33001234', 'address' => 'West Bay, Doha', 'country' => 'QA',
            'city' => 'Doha', 'region' => 'Doha', 'status' => 1,
            'is_verified' => true, 'is_monitored' => true, 'rating' => 4.7, 'ratings_count' => 3,
            'on_time_percentage' => 95.0, 'avg_response_minutes' => 4, 'lat' => 25.2854, 'lng' => 51.531,
            'ratingExcellent' => 2, 'ratingGood' => 1, 'ratingAverage' => 0, 'ratingPoor' => 0,
        ]);
    }

    public function test_catalog_services_and_classes(): void
    {
        $service = Service::query()->create(['title' => 'Ride', 'title_en' => 'Ride', 'image' => 'svc.png', 'status' => 1, 'travel_service' => false]);
        SubService::query()->create(['name' => 'Standard', 'name_en' => 'Standard', 'serviceId' => $service->id, 'status' => 1, 'is_travel' => false, 'openPrice' => 5, 'kmPrice' => 2, 'minutePrice' => 1]);

        $user = $this->user();

        $this->actingAs($user, 'user')->getJson('user/catalog/services')
            ->assertStatus(200)
            ->assertJsonPath('data.services.0.title', 'Ride')
            ->assertJsonPath('data.services.0.travel_service', false);

        $this->actingAs($user, 'user')->getJson('user/catalog/classes?service=' . $service->id)
            ->assertStatus(200)
            ->assertJsonPath('data.classes.0.name', 'Standard')
            ->assertJsonPath('data.classes.0.serviceId', $service->id);
    }

    public function test_office_profile(): void
    {
        $office = $this->office();
        $service = Service::query()->create(['title' => 'Ride', 'title_en' => 'Ride', 'image' => 'svc.png', 'status' => 1, 'travel_service' => false]);
        $sub = SubService::query()->create(['name' => 'Standard', 'name_en' => 'Standard', 'serviceId' => $service->id, 'status' => 1, 'is_travel' => false, 'openPrice' => 5, 'kmPrice' => 2, 'minutePrice' => 1]);
        OfficeSubServicePrice::query()->create(['office_id' => $office->id, 'sub_service_id' => $sub->id, 'openPrice' => 5, 'kmPrice' => 2, 'minutePrice' => 1]);

        $this->actingAs($this->user(), 'user')->getJson('user/offices/' . $office->id)
            ->assertStatus(200)
            ->assertJsonPath('data.officeName', 'Al Fleet')
            ->assertJsonPath('data.is_verified', true)
            ->assertJsonPath('data.ratingExcellent', 2)
            ->assertJsonPath('data.services.0.title', 'Ride')
            ->assertJsonPath('data.classes.0.name', 'Standard');
    }

    public function test_office_profile_missing_is_404(): void
    {
        $this->actingAs($this->user(), 'user')->getJson('user/offices/999')
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'office_not_found');
    }

    public function test_favorites_flow(): void
    {
        $user = $this->user();
        $office = $this->office();

        $this->actingAs($user, 'user')->postJson('user/me/favorites/' . $office->id)->assertStatus(204);
        $this->assertTrue(FavoriteOffice::query()->where('user_id', $user->id)->where('office_id', $office->id)->exists());

        $this->actingAs($user, 'user')->getJson('user/me/favorites')
            ->assertStatus(200)
            ->assertJsonPath('data.0.id', $office->id)
            ->assertJsonPath('data.0.officeName', 'Al Fleet');

        $this->actingAs($user, 'user')->deleteJson('user/me/favorites/' . $office->id)->assertStatus(204);
        $this->assertFalse(FavoriteOffice::query()->where('user_id', $user->id)->exists());
    }

    public function test_places_suggest(): void
    {
        $user = $this->user();
        SavedPlace::query()->create(['user_id' => $user->id, 'label' => 'Home', 'title' => 'Home', 'address' => 'West Bay, Doha', 'lat' => 25.2854, 'lng' => 51.531]);

        $this->actingAs($user, 'user')->getJson('user/places/suggest?q=West')
            ->assertStatus(200)
            ->assertJsonPath('data.results.0.source', 'recent')
            ->assertJsonPath('data.results.0.address', 'West Bay, Doha');
    }

    public function test_offices_search_by_class(): void
    {
        $office = $this->office();
        $service = Service::query()->create(['title' => 'Ride', 'title_en' => 'Ride', 'image' => 'svc.png', 'status' => 1, 'travel_service' => false]);
        $sub = SubService::query()->create(['name' => 'Standard', 'name_en' => 'Standard', 'serviceId' => $service->id, 'status' => 1, 'is_travel' => false, 'openPrice' => 5, 'kmPrice' => 2, 'minutePrice' => 1]);
        OfficeSubServicePrice::query()->create(['office_id' => $office->id, 'sub_service_id' => $sub->id, 'openPrice' => 5, 'kmPrice' => 2, 'minutePrice' => 1]);

        $this->actingAs($this->user(), 'user')->postJson('user/offices/search', [
            'route' => [
                'pickup' => ['lat' => 25.2854, 'lng' => 51.531],
                'dropoff' => ['lat' => 25.3, 'lng' => 51.55],
                'service' => $service->id,
                'serviceClass' => $sub->id,
            ],
        ])->assertStatus(200)
            ->assertJsonPath('data.offices.0.id', $office->id)
            ->assertJsonPath('data.offices.0.officeName', 'Al Fleet');
    }

    public function test_route_estimate(): void
    {
        $service = Service::query()->create(['title' => 'Ride', 'title_en' => 'Ride', 'image' => 'svc.png', 'status' => 1, 'travel_service' => false]);
        SubService::query()->create(['name' => 'Standard', 'name_en' => 'Standard', 'serviceId' => $service->id, 'status' => 1, 'is_travel' => false, 'openPrice' => 5, 'kmPrice' => 2, 'minutePrice' => 1]);

        $this->actingAs($this->user(), 'user')->postJson('user/routes/estimate', [
            'pickup' => ['lat' => 25.2854, 'lng' => 51.531],
            'dropoff' => ['lat' => 25.35, 'lng' => 51.6],
        ])->assertStatus(200)
            ->assertJsonStructure(['data' => ['distance_m', 'duration_s', 'currency_code', 'polyline', 'classes' => [['id', 'name', 'base_fare', 'fare_minor', 'currency_code']]]])
            ->assertJsonPath('data.classes.0.base_fare', 5)
            ->assertJsonPath('data.classes.0.currency_code', 'USD');
    }
}
