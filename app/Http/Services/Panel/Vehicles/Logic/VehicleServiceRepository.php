<?php

namespace App\Http\Services\Panel\Vehicles\Logic;

use App\Http\Services\Panel\Services\Logic\PricingRepository;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use Illuminate\Support\Facades\DB;

class VehicleServiceRepository
{
    public function __construct(private PricingRepository $pricing) {}

    private function connection(): ?string
    {
        return TenantConnection::current();
    }

    public function catalog(): array
    {
        return $this->pricing->catalog(true);
    }

    public function assignedIds(int $vehicleId): array
    {
        return DB::connection($this->connection())
            ->table('vehicle_sub_services')
            ->where('vehicleId', $vehicleId)
            ->pluck('subServiceId')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function validIds(): array
    {
        $ids = [];
        foreach ($this->catalog() as $service) {
            foreach ($service['subServices'] as $sub) {
                $ids[] = (int) $sub['id'];
            }
        }

        return $ids;
    }

    public function sync(int $vehicleId, array $subServiceIds): void
    {
        $conn = $this->connection();
        $target = array_values(array_unique(array_map('intval', array_intersect($this->validIds(), array_map('intval', $subServiceIds)))));
        $existing = $this->assignedIds($vehicleId);

        $toRemove = array_diff($existing, $target);
        $toAdd = array_diff($target, $existing);

        if (! empty($toRemove)) {
            DB::connection($conn)->table('vehicle_sub_services')
                ->where('vehicleId', $vehicleId)
                ->whereIn('subServiceId', $toRemove)
                ->delete();
        }

        if (! empty($toAdd)) {
            $now = now();
            $rows = array_map(fn ($id) => [
                'vehicleId'    => $vehicleId,
                'subServiceId' => $id,
                'created_at'   => $now,
                'updated_at'   => $now,
            ], array_values($toAdd));

            DB::connection($conn)->table('vehicle_sub_services')->insert($rows);
        }
    }
}
