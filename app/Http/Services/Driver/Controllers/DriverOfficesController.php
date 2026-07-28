<?php

namespace App\Http\Services\Driver\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\Support\Presenters\OfficePresenter;
use App\Http\Services\User\Support\Reply;
use App\Models\Office;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Offices a driver can ask to join (`GET /driver/offices`).
 *
 * The link-office screen used to collect the office as FREE TEXT and send it as
 * `office_query`, so `driver_applications.office_id` stayed NULL and a human had
 * to guess which office the driver meant from whatever they typed. The link
 * endpoint already accepted `office_id`; there was simply no way for the app to
 * learn one.
 *
 * PUBLIC on purpose: a driver links an office during onboarding, before any
 * account exists. Scoping is by SHARD, not by an auth guard — `ResolveTenantShard`
 * has already pointed the tenant connection at the country the driver picked in
 * the sign-in country picker, so this only ever returns that country's offices.
 */
class DriverOfficesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));

        $offices = Office::query()
            // `offices.status` is a tinyInteger defaulting to 1, NOT the string
            // 'active' and NOT an `isActive` column — both of which return zero
            // rows silently, which is how this first shipped.
            ->where('status', 1)
            ->when($search !== '', fn ($q) => $q->where(
                fn ($w) => $w->where('officeName', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
            ))
            ->orderBy('officeName')
            ->limit(200)
            ->get();

        return Reply::ok([
            'items' => $offices->map(fn (Office $o) => OfficePresenter::card($o))->values()->all(),
        ]);
    }
}
