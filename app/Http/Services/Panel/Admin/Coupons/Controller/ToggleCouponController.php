<?php

namespace App\Http\Services\Panel\Admin\Coupons\Controller;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;

class ToggleCouponController extends Controller
{
    public function __invoke(int $coupon): RedirectResponse
    {
        $model = Coupon::query()->find($coupon);

        if ($model !== null) {
            $model->isActive = ! $model->isActive;
            $model->save();
        }

        return back()->with('status', textByLanguage('تم تحديث الكوبون', 'Coupon updated'));
    }
}
