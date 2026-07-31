<?php

namespace App\Http\Services\User\Fixed\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Catalog\LocalizedName;
use App\Http\Core\Classes\Ride\FixedTripService;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Rider surface for office-mediated FIXED / scheduled A-to-Z trips:
 * compare offers → select an office → cancel. Accept/decline/assign are the
 * office's side (panel). Thin wrappers over FixedTripService.
 */
class FixedTripController extends Controller
{
    public function __construct(private FixedTripService $fixed)
    {
    }

    /** Competing corridor offers for a departure → arrival city pair. */
    public function offers(Request $request): JsonResponse
    {
        $v = $request->validate([
            'sub_service_id' => ['required', 'integer', 'min:1'],
            'departure_city_id' => ['required', 'integer', 'min:1'],
            'arrival_city_id' => ['required', 'integer', 'min:1', 'different:departure_city_id'],
        ]);

        return Reply::ok($this->fixed->offers(
            (int) $v['sub_service_id'],
            (int) $v['departure_city_id'],
            (int) $v['arrival_city_id'],
        ));
    }

    /** The rider picks a corridor offer → holds the flat fare, awaits acceptance. */
    public function select(Request $request): JsonResponse
    {
        $v = $request->validate([
            'office_id' => ['required', 'integer'],
            'sub_service_id' => ['required', 'integer', 'min:1'],
            'departure_city_id' => ['required', 'integer', 'min:1'],
            'arrival_city_id' => ['required', 'integer', 'min:1', 'different:departure_city_id'],
            'scheduled_at' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'in:wallet,cash'],
            'context' => ['nullable', 'in:personal,corporate,family'],
            'company_id' => ['nullable', 'integer'],
            'on_behalf_of' => ['nullable', 'string', 'max:32'],
            'passengers' => ['nullable', 'integer', 'min:1'],
            'luggage' => ['nullable', 'integer', 'min:0'],
            'flight_no' => ['nullable', 'string', 'max:16'],
            // Optional within-city pickup/dropoff — a hint for the driver, never priced.
            'pickup.lat' => ['nullable', 'numeric', 'between:-90,90'],
            'pickup.lng' => ['nullable', 'numeric', 'between:-180,180'],
            'pickup.title' => ['nullable', 'string', 'max:255'],
            'dropoff.lat' => ['nullable', 'numeric', 'between:-90,90'],
            'dropoff.lng' => ['nullable', 'numeric', 'between:-180,180'],
            'dropoff.title' => ['nullable', 'string', 'max:255'],
        ]);

        return Reply::ok($this->fixed->select((int) $request->user()->id, $v), 201);
    }

    /** Cities available for the corridor pickers (id + names). */
    public function cities(Request $request): JsonResponse
    {
        // The cities table carries `name` (native) + `name_on_google_map`
        // (latin) — there is no `en_name` column, so use the Google-map name as
        // the English label, falling back to the native name.
        $cities = \App\Models\City::query()
            ->orderBy('name')
            ->get(['id', 'name', 'name_on_google_map'])
            ->map(fn ($c) => [
                'id' => (int) $c->id,
                'name' => $c->name,
                'en_name' => $c->name_on_google_map ?: $c->name,
                // Pre-resolved for clients that just want to print it — the
                // corridor must read in ONE language end to end, and older
                // callers picking `name` blindly showed an Arabic city inside
                // an English screen.
                'label' => LocalizedName::of($c),
            ]);

        return Reply::ok(['cities' => $cities]);
    }

    /**
     * Sub-services the rider can book. `?travel=1` → only travel (fixed-corridor)
     * services; `?travel=0` → only city ride types (meter); absent → all active.
     */
    public function subServices(Request $request): JsonResponse
    {
        $query = \App\Models\SubService::query()->where('status', true);

        if ($request->has('travel')) {
            // `sub_services.is_travel` is a dead column — nothing ever sets it,
            // and the authority is `services.travel_service`. Filtering on it
            // returned an empty list, so the app's Airport & Travel page said
            // "no service available" while three travel classes sat published.
            $request->boolean('travel') ? $query->travel() : $query->notTravel();
        }

        $subs = $query->get(['id', 'name', 'name_en'])
            ->map(fn ($s) => [
                'id' => (int) $s->id,
                'name' => $s->name,
                'name_en' => $s->name_en,
                'label' => LocalizedName::of($s),
            ]);

        return Reply::ok(['sub_services' => $subs]);
    }

    /** The rider's own fixed trip's live status (drives the status screen). */
    public function show(Request $request, int $id): JsonResponse
    {
        return Reply::ok($this->fixed->show((int) $request->user()->id, $id));
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        return Reply::ok($this->fixed->cancel((int) $request->user()->id, $id));
    }
}
