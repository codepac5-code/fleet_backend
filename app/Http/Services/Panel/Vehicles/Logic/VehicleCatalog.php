<?php

namespace App\Http\Services\Panel\Vehicles\Logic;

use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\City;
use App\Models\VehicleBrand;
use App\Models\VehicleColor;
use App\Models\VehicleModel;
use Throwable;

/**
 * Active brand / model / colour names for the vehicle form's suggestion lists.
 * Reads are best-effort: the fields stay free text, so a missing catalog table
 * (a shard provisioned before the migration) must never break the form.
 */
class VehicleCatalog
{
    public function suggestions(): array
    {
        return [
            'brands' => $this->names(VehicleBrand::class),
            'models' => $this->names(VehicleModel::class),
            'colors' => $this->names(VehicleColor::class),
            // A model year is never in the future beyond next year's models and
            // never older than the fleet would accept — typing it by hand only
            // invites "20204".
            'years' => $this->years(),
            // Cities are a managed per-country list; typing them by hand is how
            // "Damascus" / "damascus" / "دمشق" end up as three cities.
            'cities' => $this->cities(),
        ];
    }

    private function cities(): array
    {
        try {
            // City is not tenant-routed, so the connection has to be explicit.
            return City::on(TenantConnection::current())
                ->orderBy('name')
                ->pluck('name')
                ->filter(fn ($name) => is_string($name) && $name !== '')
                ->unique()
                ->values()
                ->all();
        } catch (Throwable $e) {
            return [];
        }
    }

    private function years(): array
    {
        $newest = (int) date('Y') + 1;

        return array_map('strval', range($newest, $newest - 30));
    }

    private function names(string $model): array
    {
        try {
            return $model::query()
                ->where('status', true)
                ->orderBy('name_en')
                ->get(['name', 'name_en'])
                ->flatMap(fn ($row) => [$row->name_en, $row->name])
                ->filter(fn ($name) => is_string($name) && $name !== '')
                ->unique()
                ->values()
                ->all();
        } catch (Throwable $e) {
            return [];
        }
    }
}
