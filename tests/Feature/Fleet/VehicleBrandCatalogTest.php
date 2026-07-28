<?php

namespace Tests\Feature\Fleet;

use App\Http\Services\Panel\Admin\VehicleBrands\Controller\SaveVehicleBrandController;
use App\Http\Services\Panel\Admin\VehicleBrands\Controller\ToggleVehicleBrandController;
use App\Models\VehicleBrand;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VehicleBrandCatalogTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2025_01_06_170455_create_vehicle_brands_table.php',
    ];

    private function save(array $input, ?int $brand = null): void
    {
        (new SaveVehicleBrandController())(Request::create('/', 'POST', $input), $brand);
    }

    public function test_store_adds_an_active_brand(): void
    {
        $this->save(['name' => 'تويوتا', 'name_en' => 'Toyota']);

        $brand = VehicleBrand::query()->where('name_en', 'Toyota')->first();
        $this->assertNotNull($brand);
        $this->assertSame('تويوتا', $brand->name);
        $this->assertTrue((bool) $brand->status);
        $this->assertSame('', $brand->image, 'a missing image defaults to empty, never null');
    }

    public function test_missing_name_fails_validation(): void
    {
        $this->expectException(ValidationException::class);
        $this->save(['name_en' => 'Kia']);
    }

    public function test_update_preserves_the_existing_image_when_omitted(): void
    {
        $this->save(['name' => 'كيا', 'name_en' => 'Kia', 'image' => 'kia.png']);
        $brand = VehicleBrand::query()->where('name_en', 'Kia')->first();

        $this->save(['name' => 'كيا', 'name_en' => 'Kia Motors'], (int) $brand->id);

        $brand->refresh();
        $this->assertSame('Kia Motors', $brand->name_en);
        $this->assertSame('kia.png', $brand->image, 'omitting image on edit keeps the old one');
    }

    public function test_toggle_flips_status_without_deleting(): void
    {
        $this->save(['name' => 'هوندا', 'name_en' => 'Honda']);
        $brand = VehicleBrand::query()->where('name_en', 'Honda')->first();

        (new ToggleVehicleBrandController())((int) $brand->id);

        $brand->refresh();
        $this->assertFalse((bool) $brand->status);
        $this->assertNotNull(VehicleBrand::query()->find($brand->id), 'deactivating never deletes the row');
    }
}
