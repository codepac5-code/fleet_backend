<?php

namespace Tests\Feature\Fleet;

use App\Http\Services\Panel\Admin\VehicleCatalog\Controller\SaveVehicleColorController;
use App\Http\Services\Panel\Admin\VehicleCatalog\Controller\SaveVehicleModelController;
use App\Http\Services\Panel\Admin\VehicleCatalog\Controller\ToggleVehicleCatalogEntryController;
use App\Http\Services\Panel\Vehicles\Logic\VehicleCatalog;
use App\Models\VehicleColor;
use App\Models\VehicleModel;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VehicleCatalogTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_07_28_000003_create_vehicle_model_and_color_catalogs.php',
    ];

    private function saveModel(array $input, ?int $id = null): void
    {
        (new SaveVehicleModelController())(Request::create('/', 'POST', $input), $id);
    }

    private function saveColor(array $input, ?int $id = null): void
    {
        (new SaveVehicleColorController())(Request::create('/', 'POST', $input), $id);
    }

    public function test_store_adds_an_active_model(): void
    {
        $this->saveModel(['name' => 'كورولا', 'name_en' => 'Corolla', 'brand_id' => 3]);

        $model = VehicleModel::query()->where('name_en', 'Corolla')->first();
        $this->assertNotNull($model);
        $this->assertTrue($model->status);
        $this->assertSame(3, $model->brand_id);
    }

    public function test_model_without_a_brand_is_allowed(): void
    {
        $this->saveModel(['name' => 'عام', 'name_en' => 'Generic']);

        $this->assertNull(VehicleModel::query()->where('name_en', 'Generic')->first()->brand_id);
    }

    public function test_color_rejects_a_malformed_hex(): void
    {
        $this->expectException(ValidationException::class);
        $this->saveColor(['name' => 'أبيض', 'name_en' => 'White', 'hex' => 'white']);
    }

    public function test_toggle_deactivates_without_deleting(): void
    {
        $this->saveColor(['name' => 'أسود', 'name_en' => 'Black', 'hex' => '#000000']);
        $color = VehicleColor::query()->where('name_en', 'Black')->first();

        (new ToggleVehicleCatalogEntryController())('color', (int) $color->id);

        $this->assertFalse($color->refresh()->status);
        $this->assertNotNull(VehicleColor::query()->find($color->id));
    }

    public function test_form_suggestions_list_active_entries_in_both_languages(): void
    {
        $this->saveModel(['name' => 'كورولا', 'name_en' => 'Corolla']);
        $this->saveModel(['name' => 'أكسنت', 'name_en' => 'Accent']);
        $this->saveColor(['name' => 'أبيض', 'name_en' => 'White']);

        $models = VehicleModel::query()->where('name_en', 'Accent')->first();
        (new ToggleVehicleCatalogEntryController())('model', (int) $models->id);

        $suggestions = (new VehicleCatalog())->suggestions();

        $this->assertContains('Corolla', $suggestions['models']);
        $this->assertContains('كورولا', $suggestions['models']);
        $this->assertNotContains('Accent', $suggestions['models'], 'deactivated entries are not suggested');
        $this->assertContains('White', $suggestions['colors']);
    }

    public function test_suggestions_survive_a_missing_catalog_table(): void
    {
        $this->app['db']->connection()->statement('DROP TABLE vehicle_models');

        $this->assertSame([], (new VehicleCatalog())->suggestions()['models']);
    }
}
