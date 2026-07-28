<?php

namespace App\Http\Services\Panel\Admin\VehicleBrands\Controller;

use App\Http\Controllers\Controller;
use App\Models\VehicleBrand;
use Illuminate\Http\RedirectResponse;

class ToggleVehicleBrandController extends Controller
{
    public function __invoke(int $brand): RedirectResponse
    {
        $model = VehicleBrand::query()->find($brand);

        if ($model !== null) {
            $model->status = ! $model->status;
            $model->save();
        }

        return back()->with('status', textByLanguage('تم تحديث حالة الماركة', 'Brand status updated'));
    }
}
