<?php

namespace App\Http\Services\User\Trips\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Ride\RideBookingService;
use App\Http\Services\User\Booking\Logic\BookingService;
use App\Http\Services\User\Support\Reply;
use App\Http\Services\User\Trips\Logic\TripService;
use App\Http\Services\User\Trips\Requests\LostItemRequest;
use App\Http\Services\User\Trips\Requests\RateTripRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripsController extends Controller
{
    public function __construct(
        private TripService $trips,
        private BookingService $bookings,
        private RideBookingService $rides
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return Reply::ok($this->trips->history(
            (int) $request->user()->id,
            $request->query('status') !== null ? (string) $request->query('status') : null,
            $request->query('cursor') !== null ? (string) $request->query('cursor') : null,
            $request->query('limit')
        ));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        return Reply::ok($this->bookings->detail((int) $request->user()->id, $id));
    }

    /**
     * A public tracking link for a live trip.
     *
     * The signed URL and the page it opens both already existed
     * ({@see \App\Http\Core\Classes\Ride\RideBookingService::share()} and the
     * `public.shared-trip` route) — nothing exposed them to the rider, so the
     * app's "Copy trip link" sat disabled on top of a finished feature.
     */
    public function share(Request $request, int $id): JsonResponse
    {
        return Reply::ok($this->rides->share((int) $request->user()->id, $id));
    }

    public function rate(RateTripRequest $request, int $id): JsonResponse
    {
        return Reply::ok($this->trips->rate((int) $request->user()->id, $id, $request->validated()));
    }

    public function lostItem(LostItemRequest $request, int $id): JsonResponse
    {
        return Reply::ok($this->trips->lostItem((int) $request->user()->id, $id, $request->validated()), 201);
    }

    /** The rider's own lost-item reports with their governed status. */
    public function lostItems(Request $request): JsonResponse
    {
        return Reply::ok(['items' => $this->trips->lostItems((int) $request->user()->id)]);
    }

    /** The rider withdraws a lost-item report (only while it is still early). */
    public function cancelLostItem(Request $request, int $item): JsonResponse
    {
        return Reply::ok($this->trips->cancelLostItem((int) $request->user()->id, $item));
    }
}
