<?php

namespace App\Http\Services\Panel\OfficeBookings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Const\Ride\BookingSource;
use App\Http\Core\Const\Ride\BookingStatus;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\CommissionSnapshot;
use App\Models\DispatchJob;
use App\Models\RideBooking;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OfficeBookingsListController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope): View
    {
        $officeId = $scope->isAdmin() ? (int) ($request->query('office') ?: 0) : (int) $scope->officeId();

        $bookings = RideBooking::query()
            ->where('source', BookingSource::OFFICE)
            ->when($officeId > 0, fn ($q) => $q->where('office_id', $officeId))
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        $bookingIds = $bookings->pluck('id')->all();

        $customers = User::query()->whereIn('id', $bookings->pluck('user_id')->unique()->all())
            ->get(['id', 'firstName', 'lastName', 'phoneNumber'])->keyBy('id');

        $jobs = DispatchJob::query()->whereIn('booking_id', $bookingIds)->get()->keyBy('booking_id');
        $settled = CommissionSnapshot::query()->whereIn('booking_id', $bookingIds)->pluck('booking_id')->flip();

        $rows = $bookings->map(function ($b) use ($customers, $jobs, $settled) {
            $job = $jobs[$b->id] ?? null;
            $customer = $customers[$b->user_id] ?? null;

            return [
                'id' => (int) $b->id,
                'customer' => $customer ? trim(($customer->firstName ?? '') . ' ' . ($customer->lastName ?? '')) : '—',
                'phone' => $customer->phoneNumber ?? '—',
                'total_minor' => (int) $b->total_minor,
                'waiting_minor' => (int) ($b->waiting_minor ?? 0),
                'tip_minor' => (int) ($b->tip_minor ?? 0),
                'currency' => $b->currency_code,
                'payment_method' => $b->payment_method,
                'driver_id' => $job && $job->assigned_driver_id ? (int) $job->assigned_driver_id : null,
                'status' => $this->displayStatus($b, $job, $settled->has($b->id)),
                'created_at' => $b->created_at,
            ];
        })->all();

        return view('panel.office-bookings.index', [
            'entity' => $scope->guard(),
            'isAdmin' => $scope->isAdmin(),
            'rows' => $rows,
            'counts' => [
                'total' => count($rows),
                'matching' => collect($rows)->where('status', BookingStatus::MATCHING)->count(),
                'assigned' => collect($rows)->whereIn('status', [BookingStatus::ASSIGNED, 'arriving', 'arrived', 'on_trip'])->count(),
                'completed' => collect($rows)->where('status', BookingStatus::COMPLETED)->count(),
            ],
        ]);
    }

    private function displayStatus($booking, $job, bool $isSettled): string
    {
        if ($isSettled) {
            return BookingStatus::COMPLETED;
        }

        if (in_array($booking->status, BookingStatus::LIVE_SUB, true)) {
            return $booking->status;
        }

        if ($job && $job->assigned_driver_id && $booking->status === BookingStatus::MATCHING) {
            return BookingStatus::ASSIGNED;
        }

        return $booking->status;
    }
}
