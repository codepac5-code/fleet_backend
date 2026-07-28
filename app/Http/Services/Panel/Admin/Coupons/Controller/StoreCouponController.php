<?php

namespace App\Http\Services\Panel\Admin\Coupons\Controller;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreCouponController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:60'],
            'discount' => ['required', 'numeric', 'min:0'],
            'is_percentage' => ['nullable', 'boolean'],
            'limit' => ['nullable', 'integer', 'min:0'],
            'expire_date' => ['nullable', 'date'],
        ]);

        $isPercentage = (bool) ($data['is_percentage'] ?? false);

        // Created on the active shard (per-country) via the model's tenant trait.
        Coupon::query()->create([
            'code' => strtoupper(trim($data['code'])),
            'discount' => (float) $data['discount'],
            'isPercentage' => $isPercentage,
            'discountType' => $isPercentage ? 'percentage' : 'fixed',
            'limit' => (int) ($data['limit'] ?? 0),
            'expireDate' => $data['expire_date'] ?? null,
            'isActive' => true,
            'status' => 1,
        ]);

        return redirect()
            ->route('panel.admin.coupons.index')
            ->with('status', textByLanguage('تمّت إضافة الكوبون', 'Coupon added'));
    }
}
