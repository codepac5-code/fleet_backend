<?php

namespace App\Http\Services\Panel\Admin\Coupons\Controller;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;

class DeleteCouponController extends Controller
{
    public function __invoke(int $coupon): RedirectResponse
    {
        $model = Coupon::query()->find($coupon);

        if ($model !== null) {
            $model->delete();
        }

        return back()->with('status', textByLanguage('تم حذف الكوبون', 'Coupon deleted'));
    }
}
