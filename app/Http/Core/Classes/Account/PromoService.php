<?php

namespace App\Http\Core\Classes\Account;

use App\Models\Coupon;
use App\Models\CouponService;
use App\Models\CouponUser;
use Throwable;

/**
 * Promo/coupon engine backed by the admin-maintained `coupons` table (global
 * connection): validates a code, computes the discount, lists live offers, and
 * records redemptions to enforce usage limits.
 *
 * The rider benefit: an admin creates a coupon (percentage or fixed, with an
 * expiry, a total-use limit, and optional per-service scope); the rider sees it
 * in the app or types the code; at booking the discount is subtracted from the
 * fare (`total = fare - discount`) and honoured through to driver settlement.
 */
class PromoService
{
    /**
     * Validate a code with no fare in hand (the redeem screen). Returns the
     * discount RULE so the app can show "10% off" / "50 off" and apply it later.
     */
    public function apply(string $code): array
    {
        $coupon = $this->lookup($code);

        if ($coupon === null) {
            return ['code' => $this->normalize($code), 'valid' => false, 'discount_label' => null];
        }

        return [
            'code' => (string) $coupon->code,
            'valid' => true,
            'isPercentage' => $this->isPercentage($coupon),
            'discount' => (float) $coupon->discount,
            'discount_label' => $this->label($coupon),
        ];
    }

    /**
     * Evaluate a code against a concrete fare and return the discount in minor
     * units, capped at the fare. `$decimals` is the booking currency's fraction
     * digits (so a FIXED coupon's major-unit value converts correctly).
     */
    public function evaluate(string $code, int $fareMinor, ?int $serviceId = null, int $decimals = 2): array
    {
        $coupon = $this->lookup($code);

        if ($coupon === null) {
            return ['valid' => false, 'code' => $this->normalize($code), 'couponId' => null, 'discountMinor' => 0, 'message' => 'Invalid or expired code.'];
        }

        if (! $this->appliesToService($coupon, $serviceId)) {
            return ['valid' => false, 'code' => (string) $coupon->code, 'couponId' => (int) $coupon->id, 'discountMinor' => 0, 'message' => 'This code does not apply to the selected service.'];
        }

        $discountMinor = $this->isPercentage($coupon)
            ? (int) round($fareMinor * ((float) $coupon->discount) / 100)
            : (int) round(((float) $coupon->discount) * (10 ** $decimals));

        $discountMinor = max(0, min($discountMinor, $fareMinor));

        return [
            'valid' => true,
            'code' => (string) $coupon->code,
            'couponId' => (int) $coupon->id,
            'discountMinor' => $discountMinor,
            'message' => $this->label($coupon),
        ];
    }

    /** Currently-valid coupons, optionally scoped to a service. */
    public function available(?int $serviceId = null): array
    {
        try {
            return Coupon::query()
                ->where('isActive', true)
                ->where(fn ($q) => $q->whereNull('expireDate')->orWhere('expireDate', '>', now()))
                ->get()
                ->filter(fn (Coupon $c) => $this->withinLimit($c) && $this->appliesToService($c, $serviceId))
                ->map(fn (Coupon $c) => [
                    'code' => (string) $c->code,
                    'discountType' => $this->isPercentage($c) ? 'percentage' : 'fixed',
                    'discount' => (float) $c->discount,
                    'isPercentage' => $this->isPercentage($c),
                    'isActive' => true,
                    'limit' => (int) $c->limit ?: null,
                    'expireDate' => $c->expireDate,
                    'label' => $this->label($c),
                    'userCount' => null,
                ])
                ->values()
                ->all();
        } catch (Throwable $e) {
            return [];
        }
    }

    /** Record one redemption of a coupon by a user (enforces limits over time). */
    public function recordUse(int $couponId, int $userId): void
    {
        try {
            $row = CouponUser::query()->firstOrNew(['couponId' => $couponId, 'userId' => $userId]);
            $row->count = (int) ($row->count ?? 0) + 1;
            $row->save();
        } catch (Throwable $e) {
            // never let usage bookkeeping fail a booking
        }
    }

    private function lookup(string $code): ?Coupon
    {
        try {
            $coupon = Coupon::query()
                ->whereRaw('UPPER(code) = ?', [$this->normalize($code)])
                ->where('isActive', true)
                ->where(fn ($q) => $q->whereNull('expireDate')->orWhere('expireDate', '>', now()))
                ->first();
        } catch (Throwable $e) {
            return null;
        }

        if ($coupon === null || ! $this->withinLimit($coupon)) {
            return null;
        }

        return $coupon;
    }

    private function withinLimit(Coupon $coupon): bool
    {
        $limit = (int) $coupon->limit;

        if ($limit <= 0) {
            return true; // 0/unset = unlimited
        }

        try {
            $used = (int) CouponUser::query()->where('couponId', $coupon->id)->sum('count');
        } catch (Throwable $e) {
            return true; // usage table unavailable — don't wrongly reject a valid code
        }

        return $used < $limit;
    }

    private function appliesToService(Coupon $coupon, ?int $serviceId): bool
    {
        try {
            $scoped = CouponService::query()->where('couponId', $coupon->id)->exists();

            if (! $scoped) {
                return true; // no scope rows = applies to every service
            }

            if ($serviceId === null) {
                return true; // no service context (e.g. preview) — don't block here
            }

            return CouponService::query()->where('couponId', $coupon->id)->where('serviceId', $serviceId)->exists();
        } catch (Throwable $e) {
            return true; // scope table unavailable — treat as unscoped
        }
    }

    private function isPercentage(Coupon $coupon): bool
    {
        return (bool) $coupon->isPercentage || strtolower((string) $coupon->discountType) === 'percentage';
    }

    private function label(Coupon $coupon): string
    {
        $v = (float) $coupon->discount;
        $vs = $v == (int) $v ? (string) (int) $v : (string) $v;

        return $this->isPercentage($coupon) ? $vs . '% off' : $vs . ' off';
    }

    private function normalize(string $code): string
    {
        return strtoupper(trim($code));
    }
}
