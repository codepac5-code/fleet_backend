<?php

namespace App\Http\Services\Panel\Bookings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Dispatch\DispatchService;
use App\Http\Core\Classes\Event\DomainEvent;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventType;
use App\Http\Core\Const\Ride\BookingSource;
use App\Http\Core\Const\Ride\BookingStatus;
use App\Http\Services\Panel\Bookings\Logic\BookingRepository;
use App\Http\Services\Panel\Bookings\Logic\ScheduledTripPresenter;
use App\Http\Services\Panel\Bookings\Request\AssignDriverRequest;
use App\Http\Services\Panel\Drivers\Logic\DriverRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\RideBooking;
use Illuminate\Http\JsonResponse;

class AssignRideDriverController extends Controller
{
    private const ASSIGNABLE = [
        BookingStatus::SCHEDULED,
        BookingStatus::PENDING_ACCEPTANCE,
        BookingStatus::CONFIRMED,
        BookingStatus::MATCHING,
        BookingStatus::ASSIGNED,
        BookingStatus::ARRIVING,
    ];

    public function __invoke(
        AssignDriverRequest $request,
        int $ride,
        BookingRepository $bookings,
        DriverRepository $drivers,
        EntityScope $scope,
        EventBus $events,
        DispatchService $dispatch
    ): JsonResponse {
        $booking = RideBooking::on(TenantConnection::current())->findOrFail($ride);

        $officeId = $scope->officeId();
        if ($officeId !== null && (int) $booking->office_id !== $officeId) {
            abort(403);
        }

        if (! in_array((string) $booking->status, self::ASSIGNABLE, true)) {
            return response()->json([
                'ok'      => false,
                'message' => textByLanguage('لا يمكن إسناد سائق لهذه الرحلة في حالتها الحالية', 'This trip cannot be assigned in its current state'),
            ], 422);
        }

        $driver = $drivers->findOrFail((int) $request->validated('driver_id'));

        $reassigning = $booking->driver_id !== null && (int) $booking->driver_id !== (int) $driver->id;

        // Wire the assignment through dispatch so the DRIVER app actually gets the
        // trip: the driver's trip endpoints authorise via the DispatchJob
        // (`assignmentForDriver`), not `ride_bookings.driver_id`. forceAssign
        // creates the job when the scheduled ride has none yet, marks it ASSIGNED,
        // sets the driver busy, and fans `dispatch.ride_assigned` to the driver.
        $dispatch->forceAssign(
            (int) $booking->id,
            (int) $driver->id,
            (int) $booking->office_id,
            $booking->service_class,
            (float) $booking->pickup_lat,
            (float) $booking->pickup_lng
        );

        $booking->status = BookingStatus::ASSIGNED;
        $booking->save();

        $events->emit(new DomainEvent(
            EventType::BOOKING_STATUS_CHANGED,
            [
                Channel::office((int) $booking->office_id),
                Channel::user((int) $booking->user_id),
                Channel::booking((int) $booking->id),
                Channel::driver((int) $driver->id),
            ],
            [
                'booking_id' => (int) $booking->id,
                'status'     => BookingStatus::ASSIGNED,
                'source'     => BookingSource::OFFICE,
                'office_id'  => (int) $booking->office_id,
            ]
        ));

        $message = $reassigning
            ? textByLanguage('تم تغيير السائق بنجاح', 'Driver changed successfully')
            : textByLanguage('تم إسناد السائق بنجاح', 'Driver assigned successfully');

        $row = $bookings->findScheduledRow($booking->id);

        return response()->json([
            'ok'      => true,
            'message' => $message,
            'trip'    => $row ? ScheduledTripPresenter::toArray($row, $scope->guard()) : null,
        ]);
    }
}
