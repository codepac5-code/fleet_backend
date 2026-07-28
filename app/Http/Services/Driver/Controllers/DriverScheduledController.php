<?php

namespace App\Http\Services\Driver\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Const\Dispatch\DispatchStatus;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Services\User\Support\Presenters\BookingPresenter;
use App\Http\Services\User\Support\Presenters\MoneyPresenter;
use App\Http\Services\User\Support\Reply;
use App\Models\DispatchJob;
use App\Models\RideBooking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Scheduled-ride marketplace for drivers: browse office offers, claim (atomic,
 * first-driver-wins), list committed, release, and set a reminder. Scheduled
 * bookings are `ride_bookings` with `status='scheduled'`; claiming sets
 * `driver_id` while the status stays 'scheduled' until the driver navigates.
 */
class DriverScheduledController extends Controller
{
    public function offers(Request $request): JsonResponse
    {
        $driver = $request->user();

        $items = RideBooking::query()
            ->where('status', 'scheduled')
            ->where('office_id', $driver->officeId)
            ->whereNull('driver_id')
            ->orderBy('scheduled_at')
            ->limit(50)
            ->get()
            ->map(fn (RideBooking $b) => BookingPresenter::row($b))
            ->all();

        return Reply::ok(['items' => $items, 'nextCursor' => null]);
    }

    public function claim(Request $request, int $id): JsonResponse
    {
        $driver = $request->user();

        // Atomic first-driver-wins: only claim if still scheduled + unclaimed + same office.
        $affected = RideBooking::query()
            ->where('id', $id)
            ->where('status', 'scheduled')
            ->where('office_id', $driver->officeId)
            ->whereNull('driver_id')
            ->update(['driver_id' => $driver->id]);

        if ($affected === 0) {
            throw DomainException::make('booking_not_assignable', 409);
        }

        $booking = RideBooking::query()->find($id);

        // Register the DISPATCH-JOB assignment, not just `bookings.driver_id`.
        // Every trip-stage endpoint (navigate-pickup / arrived / start / end /
        // payment) gates on `DispatchJobRepository::assignmentForDriver`, which
        // looks up a DispatchJob row with `assigned_driver_id` — it never reads
        // `bookings.driver_id`. A scheduled booking has no dispatch job (it never
        // went through instant dispatch), so claiming set the booking column but
        // left NO job, and the driver then got 403 "ride_not_assigned" on every
        // stage after a successful claim. Create the assignment here so the
        // claimed scheduled ride can actually be driven. `booking_id` is unique,
        // hence updateOrCreate (also heals any pre-existing pending job).
        DispatchJob::updateOrCreate(
            ['booking_id' => (int) $id],
            [
                'office_id' => (int) $booking->office_id,
                'service_class' => $booking->service_class,
                'lat' => $booking->pickup_lat,
                'lng' => $booking->pickup_lng,
                'status' => DispatchStatus::ASSIGNED,
                'assigned_driver_id' => (int) $driver->id,
                'assigned_at' => now(),
            ],
        );

        return Reply::ok(['booking' => BookingPresenter::row($booking)]);
    }

    public function committed(Request $request): JsonResponse
    {
        $driver = $request->user();

        $rows = RideBooking::query()
            ->where('status', 'scheduled')
            ->where('driver_id', $driver->id)
            ->orderBy('scheduled_at')
            ->limit(50)
            ->get();

        return Reply::ok([
            'items' => $rows->map(fn (RideBooking $b) => BookingPresenter::row($b))->all(),
            'summary' => [
                'booked' => $rows->count(),
                'estEarningsMinor' => (int) $rows->sum(fn (RideBooking $b) => (int) ($b->total_minor ?? $b->fare_minor ?? 0)),
                'currency_code' => MoneyPresenter::currency(null)['code'],
                'missed' => 0,
            ],
        ]);
    }

    public function release(Request $request, int $id): JsonResponse
    {
        $affected = RideBooking::query()
            ->where('id', $id)
            ->where('driver_id', $request->user()->id)
            ->where('status', 'scheduled')
            ->update(['driver_id' => null]);

        if ($affected === 0) {
            throw DomainException::notFound();
        }

        return Reply::ok(['ok' => true, 'scoreImpact' => true]);
    }

    public function reminder(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'leadMinutes' => ['nullable', 'integer', 'min:0', 'max:120'],
        ]);

        $owns = RideBooking::query()->where('id', $id)->where('driver_id', $request->user()->id)->exists();

        if (! $owns) {
            throw DomainException::notFound();
        }

        // Reminder scheduling is client-local (local notification); server just acks.
        return Reply::ok(['ok' => true]);
    }
}
