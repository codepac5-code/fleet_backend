<?php

namespace App\Http\Services\Panel\Admin\VehicleCatalog\Controller;

use App\Http\Controllers\Controller;
use App\Models\VehicleColor;
use App\Models\VehicleModel;
use Illuminate\Http\RedirectResponse;

/**
 * Deactivate-only, never delete: the catalogs are platform-wide while the
 * vehicles that reference them live per shard, so an in-use check here can't see
 * the other countries. Same reasoning as the brand catalog.
 */
class ToggleVehicleCatalogEntryController extends Controller
{
    public function __invoke(string $type, int $id): RedirectResponse
    {
        $record = $type === 'color'
            ? VehicleColor::query()->find($id)
            : VehicleModel::query()->find($id);

        if ($record !== null) {
            $record->status = ! $record->status;
            $record->save();
        }

        return back()->with('status', textByLanguage('تم تحديث الحالة', 'Status updated'));
    }
}
