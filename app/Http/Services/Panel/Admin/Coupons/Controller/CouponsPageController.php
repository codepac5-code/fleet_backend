<?php

namespace App\Http\Services\Panel\Admin\Coupons\Controller;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\View\View;

class CouponsPageController extends Controller
{
    public function __invoke(): View
    {
        // Coupon uses ResolvesTenantConnection → scoped to the active country
        // shard. Each country manages its own coupons; none are shared.
        $coupons = Coupon::query()->list()->get();

        return view('panel.coupons.index', [
            'coupons' => $coupons,
        ]);
    }
}
