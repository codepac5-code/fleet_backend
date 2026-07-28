<?php

namespace App\Http\Services\Panel\Admin\VehicleBrands\Controller;

use App\Http\Controllers\Controller;
use App\Models\VehicleBrand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SaveVehicleBrandController extends Controller
{
    public function __invoke(Request $request, ?int $brand = null): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'name_en' => ['required', 'string', 'max:120'],
            'image' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'description_en' => ['nullable', 'string', 'max:500'],
        ]);

        $model = $brand !== null ? VehicleBrand::query()->findOrFail($brand) : new VehicleBrand();

        $model->name = $data['name'];
        $model->name_en = $data['name_en'];
        $model->image = $data['image'] ?? ($model->image ?? '');
        $model->description = $data['description'] ?? null;
        $model->description_en = $data['description_en'] ?? null;

        if ($brand === null) {
            $model->status = true;
        }

        $model->save();

        return back()->with('status', $brand !== null
            ? textByLanguage('تم تحديث الماركة', 'Brand updated')
            : textByLanguage('تمت إضافة الماركة', 'Brand added'));
    }
}
