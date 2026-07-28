<?php

namespace App\Http\Services\Panel\Bookings\Controller;

use App\Events\HoldOrder;
use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Bookings\Logic\BookingRepository;
use App\Http\Services\Panel\Bookings\Logic\ScheduledTripPresenter;
use App\Http\Services\Panel\Bookings\Request\AssignDriverRequest;
use App\Http\Services\Panel\Drivers\Logic\DriverRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Jobs\SendNewOrderForDriversJob;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\SubService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class AssignDriverController extends Controller
{
    public function __invoke(
        AssignDriverRequest $request,
        int $booking,
        BookingRepository $bookings,
        DriverRepository $drivers,
        EntityScope $scope
    ): RedirectResponse|JsonResponse {
        $order  = $bookings->findOrFail($booking);
        $driver = $drivers->findOrFail((int) $request->validated('driver_id'));

        $reassigning = $order->driverId !== null && (int) $order->driverId !== (int) $driver->id;

        $order->driverId   = $driver->id;
        $order->assignedAt = now();
        $order->save();

        $this->notifyDriver($order, $driver, $reassigning);

        $message = $reassigning
            ? textByLanguage('تم تغيير السائق بنجاح', 'Driver changed successfully')
            : textByLanguage('تم إسناد السائق بنجاح', 'Driver assigned successfully');

        if ($request->wantsJson()) {
            $row = $bookings->findScheduledRow($order->id);

            return response()->json([
                'ok'      => true,
                'message' => $message,
                'trip'    => $row ? ScheduledTripPresenter::toArray($row, $scope->guard()) : null,
            ]);
        }

        return back()->with('status', $message);
    }

    private function notifyDriver(Booking $order, Driver $driver, bool $reassigning): void
    {
        try {
            if ($reassigning) {
                event(new HoldOrder($order->id));
            }

            $subService = SubService::on($order->getConnectionName())->find($order->subServiceId);
            $subName    = $subService->name ?? $subService->name_en ?? '';

            $waypoints = [];
            if (! empty($order->multiDestnationArray)) {
                $decoded   = json_decode($order->multiDestnationArray, true);
                $waypoints = is_array($decoded) ? $decoded : [];
            }

            $payload = [
                'startAddress'   => (string) $order->startAddress,
                'endAddress'     => (string) $order->endAddress,
                'time'           => (string) $order->time,
                'startLatitude'  => $order->startLatitude !== null ? (float) $order->startLatitude : null,
                'startLongitude' => $order->startLongitude !== null ? (float) $order->startLongitude : null,
                'endLatitude'    => $order->endLatitude !== null ? (float) $order->endLatitude : null,
                'endLongitude'   => $order->endLongitude !== null ? (float) $order->endLongitude : null,
                'distance'       => (float) $order->distance,
                'subService'     => $subName,
                'subServiceId'   => (int) $order->subServiceId,
                'userId'         => (int) $order->userId,
                'orderId'        => (int) $order->id,
                'paymentMethod'  => (string) $order->paymentType,
                'totalAmount'    => (float) $order->totalAmount,
                'amount'         => (float) $order->amount,
                'waypoints'      => $waypoints,
                'is_scheduled'   => (bool) $order->is_scheduled,
                'scheduled_time' => $order->scheduled_time,
            ];

            SendNewOrderForDriversJob::dispatch([$driver->id], $payload)->onQueue('jobs');
        } catch (\Throwable $e) {
            Log::warning('Panel driver assign notify failed: ' . $e->getMessage());
        }
    }
}
