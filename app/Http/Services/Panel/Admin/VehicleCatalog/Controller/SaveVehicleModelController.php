<?php

namespace App\Http\Services\Panel\Admin\VehicleCatalog\Controller;

use App\Http\Controllers\Controller;
use App\Models\VehicleModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SaveVehicleModelController extends Controller
{
    public function __invoke(Request $request, ?int $model = null): RedirectResponse
    {
        $data = $request->validate([
            'brand_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:120'],
            'name_en' => ['required', 'string', 'max:120'],
        ]);

        $record = $model !== null ? VehicleModel::query()->findOrFail($model) : new VehicleModel();

        $record->brand_id = $data['brand_id'] ?? null;
        $record->name = $data['name'];
        $record->name_en = $data['name_en'];

        if ($model === null) {
            $record->status = true;
        }

        $record->save();

        return back()->with('status', $model !== null
            ? textByLanguage('تم تحديث الطراز', 'Model updated')
            : textByLanguage('تمت إضافة الطراز', 'Model added'));
    }
}
