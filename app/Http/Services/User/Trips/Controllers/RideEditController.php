<?php

namespace App\Http\Services\User\Trips\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Dispatch\Geo;
use App\Http\Core\Classes\Event\EventPublisher;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventType;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Services\User\Support\Presenters\BookingPresenter;
use App\Http\Services\User\Support\Reply;
use App\Models\RideBooking;
use App\Models\SubService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Mid-trip route edits by the rider: change the destination or add a stop. Both
 * re-price the ride (distance/duration/fare across pickup → stops → dropoff) and
 * emit `booking.status_changed` so the driver + rider see the update live.
 */
class RideEditController extends Controller
{
    private const EDITABLE = ['matching', 'assigned', 'arriving', 'arrived', 'on_trip'];

    public function __construct(private ?EventPublisher $realtime = null)
    {
    }

    public function changeRoute(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'dropoff' => ['required', 'array'],
            'dropoff.lat' => ['required', 'numeric', 'between:-90,90'],
            'dropoff.lng' => ['required', 'numeric', 'between:-180,180'],
            'dropoff.title' => ['nullable', 'string', 'max:255'],
        ]);

        $booking = $this->editableBooking($request, $id);
        $booking->dropoff_lat = (float) $data['dropoff']['lat'];
        $booking->dropoff_lng = (float) $data['dropoff']['lng'];
        if (array_key_exists('title', $data['dropoff'])) {
            $booking->dropoff_title = $data['dropoff']['title'];
        }

        $this->reprice($booking);
        $booking->save();
        $this->emit($booking);

        return Reply::ok(BookingPresenter::row($booking));
    }

    public function addStop(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $booking = $this->editableBooking($request, $id);

        $stops = $booking->stops ?? [];
        $stops[] = ['lat' => (float) $data['lat'], 'lng' => (float) $data['lng'], 'title' => $data['title'] ?? null];
        $booking->stops = $stops;

        $this->reprice($booking);
        $booking->save();
        $this->emit($booking);

        return Reply::ok([
            'booking' => BookingPresenter::row($booking),
            'stops' => $booking->stops,
        ], 201);
    }

    private function editableBooking(Request $request, int $id): RideBooking
    {
        $booking = RideBooking::query()->where('id', $id)->where('user_id', $request->user()->id)->first();

        if ($booking === null) {
            throw DomainException::notFound();
        }

        if (! in_array($booking->status, self::EDITABLE, true)) {
            throw DomainException::make('booking_not_editable', 409);
        }

        return $booking;
    }

    /** Recompute distance/duration/fare across pickup → stops → dropoff. */
    private function reprice(RideBooking $booking): void
    {
        $points = [[(float) $booking->pickup_lat, (float) $booking->pickup_lng]];
        foreach (($booking->stops ?? []) as $s) {
            $points[] = [(float) $s['lat'], (float) $s['lng']];
        }
        $points[] = [(float) $booking->dropoff_lat, (float) $booking->dropoff_lng];

        $distance = 0;
        for ($i = 1; $i < count($points); $i++) {
            $distance += Geo::haversineMeters($points[$i - 1][0], $points[$i - 1][1], $points[$i][0], $points[$i][1]);
        }
        $duration = (int) round($distance / 8);

        $booking->distance_m = $distance;
        $booking->duration_s = $duration;

        $tariff = SubService::query()
            ->where('name', $booking->service_class)
            ->orWhere('name_en', $booking->service_class)
            ->first();

        if ($tariff !== null) {
            $fare = (float) $tariff->openPrice
                + (float) $tariff->kmPrice * ($distance / 1000)
                + (float) $tariff->minutePrice * ($duration / 60);
            $booking->fare_minor = (int) round($fare * 100);
            $booking->total_minor = $booking->fare_minor
                + (int) ($booking->waiting_minor ?? 0)
                + (int) ($booking->tip_minor ?? 0)
                - (int) ($booking->discount_minor ?? 0);
        }
    }

    private function emit(RideBooking $booking): void
    {
        if ($this->realtime === null) {
            return;
        }

        try {
            $this->realtime->publish(
                Channel::booking((int) $booking->id),
                EventType::BOOKING_STATUS_CHANGED,
                BookingPresenter::row($booking),
            );
        } catch (\Throwable $e) {
            // realtime is best-effort; the REST response already reflects the change
        }
    }
}
