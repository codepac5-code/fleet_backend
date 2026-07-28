<?php

namespace App\Http\Services\Panel\Support\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Const\LostItemStatus as St;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\LostItem;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LostItemsPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope): View
    {
        $status = $request->query('status');
        $status = $status !== null && $status !== '' ? (string) $status : null;

        // An office only ever sees its own reports; an admin sees the shard.
        $officeId = $scope->isOffice() ? $scope->officeId() : null;

        // lost_items is a GLOBAL table whose office_id repeats across countries, so
        // scope every read to the active country too — without this, office #3 in
        // one country would see office #3's reports in another. Null country =
        // aggregate "all countries" view (super-admin only) → no country filter.
        $country = LostItem::activeCountryCode();

        $base = fn () => LostItem::query()
            ->when($country !== null, fn ($q) => $q->where('country_code', $country))
            ->when($officeId !== null, fn ($q) => $q->where('office_id', $officeId));

        $items = $base()
            ->when($status !== null, fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->limit(300)
            ->get();

        $users = User::query()
            ->whereIn('id', $items->pluck('user_id')->unique()->all())
            ->get(['id', 'firstName', 'lastName', 'phoneNumber'])
            ->keyBy('id');

        // Auto-SUGGESTED matches: for every open, unmatched report on the page,
        // the OPPOSITE-side open reports on the SAME booking (fetched across the
        // whole office so a lost/found pair split across pages still meets).
        $openBookingIds = $items
            ->filter(fn ($i) => in_array($i->status, St::OPEN, true) && $i->matched_item_id === null)
            ->pluck('booking_id')->unique()->values();

        $candidates = $openBookingIds->isEmpty()
            ? collect()
            : $base()->whereIn('booking_id', $openBookingIds)
                ->whereNull('matched_item_id')
                ->whereIn('status', St::OPEN)
                ->get()
                ->groupBy('booking_id');

        $suggestions = [];
        foreach ($items as $it) {
            if (! in_array($it->status, St::OPEN, true) || $it->matched_item_id !== null) {
                continue;
            }
            $opposite = $it->reporter_type === St::REPORTER_RIDER ? St::REPORTER_DRIVER : St::REPORTER_RIDER;
            $suggestions[$it->id] = ($candidates[$it->booking_id] ?? collect())
                ->filter(fn ($x) => $x->reporter_type === $opposite && (int) $x->id !== (int) $it->id)
                ->values();
        }

        $countBy = fn (string $s) => $base()->where('status', $s)->count();

        return view('panel.support.lost-items', [
            'entity' => $scope->guard(),
            'items' => $items,
            'users' => $users,
            'statusFilter' => $status,
            'suggestions' => $suggestions,
            // Which lifecycle moves the office may make from each status.
            'transitions' => St::OFFICE_TRANSITIONS,
            'counts' => [
                'reported' => $countBy(St::REPORTED),
                'matched' => $countBy(St::MATCHED),
                'ready' => $countBy(St::READY),
                'returned' => $countBy(St::RETURNED),
                'total' => $base()->count(),
            ],
        ]);
    }
}
