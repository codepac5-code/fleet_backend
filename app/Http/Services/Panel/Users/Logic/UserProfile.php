<?php

namespace App\Http\Services\Panel\Users\Logic;

use App\Http\Core\Const\Ride\BookingStatus;
use App\Http\Core\Classes\Ledger\LedgerService;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Complaint;
use App\Models\LostItem;
use App\Models\Office;
use App\Models\RideBooking;
use App\Models\RideRating;
use App\Models\User;
use Carbon\Carbon;
use Throwable;

/**
 * Everything the rider profile shows, read from the ACTIVE country only: the
 * user row is global but their rides, ratings and wallet are per-shard, so the
 * page describes "this rider in this country" — never a cross-country blend.
 * Every section degrades to empty rather than breaking the page.
 */
class UserProfile
{
    public function __construct(private LedgerService $ledger)
    {
    }

    public function overview(User $user): array
    {
        $cancelled = [BookingStatus::CANCELLED, BookingStatus::REJECTED, BookingStatus::DECLINED, BookingStatus::NO_DRIVER_EXPIRED];

        try {
            $bookings = RideBooking::on(TenantConnection::current())
                ->where('user_id', $user->id)
                ->get(['id', 'status', 'total_minor', 'currency_code', 'created_at', 'scheduled_at']);
        } catch (Throwable $e) {
            $bookings = collect();
        }

        $completed = $bookings->where('status', BookingStatus::COMPLETED);
        $month = Carbon::now()->startOfMonth();

        return [
            'total' => $bookings->count(),
            'completed' => $completed->count(),
            'cancelled' => $bookings->whereIn('status', $cancelled)->count(),
            'scheduled' => $bookings->filter(fn ($b) => $b->scheduled_at !== null)->count(),
            'spentMinor' => (int) $completed->sum('total_minor'),
            'spentThisMonthMinor' => (int) $completed->filter(fn ($b) => $b->created_at !== null && $b->created_at >= $month)->sum('total_minor'),
            'currency' => $completed->first()->currency_code ?? $bookings->first()->currency_code ?? null,
            'firstRideAt' => $bookings->min('created_at'),
            'lastRideAt' => $bookings->max('created_at'),
        ];
    }

    /** The rider's most recent rides in this country. */
    public function recentRides(User $user, int $limit = 10)
    {
        try {
            $rides = RideBooking::on(TenantConnection::current())
                ->where('user_id', $user->id)
                ->orderByDesc('id')
                ->limit($limit)
                ->get();
        } catch (Throwable $e) {
            return collect();
        }

        $offices = Office::on(TenantConnection::current())
            ->whereIn('id', $rides->pluck('office_id')->filter()->unique()->all())
            ->get(['id', 'officeName'])
            ->keyBy('id');

        return $rides->map(fn (RideBooking $r) => (object) [
            'id' => $r->id,
            'status' => $r->status,
            'office' => $offices->get($r->office_id)->officeName ?? null,
            'from' => $r->pickup_title,
            'to' => $r->dropoff_title,
            'totalMinor' => (int) $r->total_minor,
            'currency' => $r->currency_code,
            'createdAt' => $r->created_at,
        ]);
    }

    /** Ratings the rider GAVE and the ratings drivers gave THEM. */
    public function ratings(User $user): array
    {
        try {
            $given = RideRating::on(TenantConnection::current())
                ->where('rater_type', 'user')->where('rater_id', $user->id)->get(['stars']);

            $received = RideRating::on(TenantConnection::current())
                ->whereIn('ratee_type', ['rider', 'user'])->where('ratee_id', $user->id)->get(['stars']);
        } catch (Throwable $e) {
            $given = $received = collect();
        }

        return [
            'givenCount' => $given->count(),
            'givenAverage' => $given->count() ? round((float) $given->avg('stars'), 2) : null,
            'receivedCount' => $received->count(),
            'receivedAverage' => $received->count() ? round((float) $received->avg('stars'), 2) : null,
        ];
    }

    /** Wallet balance in the shard currency, or null when there is no account. */
    public function walletMinor(User $user, ?string $currency): ?int
    {
        if ($currency === null || $currency === '') {
            return null;
        }

        try {
            return $this->ledger->ownerBalanceMinor('user', $user->id, 'wallet', $currency);
        } catch (Throwable $e) {
            return null;
        }
    }

    /** Support footprint: complaints and lost-item reports in this country. */
    public function support(User $user): array
    {
        $country = Complaint::activeCountryCode();
        $inCountry = fn ($query) => $query->when($country !== null, fn ($q) => $q->where('country_code', $country));

        try {
            $complaints = $inCountry(Complaint::query()->where('user_id', $user->id))->orderByDesc('id')->limit(5)->get();
            $openComplaints = $inCountry(Complaint::query()->where('user_id', $user->id))->whereNotIn('status', ['resolved', 'dismissed'])->count();
        } catch (Throwable $e) {
            $complaints = collect();
            $openComplaints = 0;
        }

        try {
            $lostItems = $inCountry(LostItem::query()->where('user_id', $user->id))->orderByDesc('id')->limit(5)->get();
        } catch (Throwable $e) {
            $lostItems = collect();
        }

        return [
            'complaints' => $complaints,
            'openComplaints' => $openComplaints,
            'lostItems' => $lostItems,
        ];
    }
}
