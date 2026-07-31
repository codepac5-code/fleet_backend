<?php

namespace App\Http\Services\Panel\Rides\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Const\Ride\BookingStatus;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\RideBooking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RidesPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope): View
    {
        $conn = TenantConnection::current();
        $status = trim((string) $request->query('status', ''));
        $search = trim((string) $request->query('q', ''));

        // RideBooking is per-country (ResolvesTenantConnection). Office guards are
        // further limited to their own office. This is the marketplace/rider ride
        // view the panel previously lacked (bookings/* = legacy, office-bookings/*
        // = office-created only).
        $query = RideBooking::on($conn)->orderByDesc('id');

        if (! $scope->isAdmin()) {
            $query->where('office_id', (int) $scope->officeId());
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        // This is now the panel's primary Orders screen, so it has to be
        // searchable: by ride id, or by either end of the route.
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('pickup_title', 'like', "%{$search}%")
                    ->orWhere('dropoff_title', 'like', "%{$search}%");
            });
        }

        $bookings = $query->limit(200)->get();

        $customers = User::query()
            ->whereIn('id', $bookings->pluck('user_id')->unique()->all())
            ->get(['id', 'firstName', 'lastName', 'phoneNumber'])
            ->keyBy('id');

        $rows = $bookings->map(function (RideBooking $b) use ($customers) {
            $customer = $customers->get($b->user_id);

            return [
                'id' => (int) $b->id,
                'customer' => $customer ? trim(($customer->firstName ?? '') . ' ' . ($customer->lastName ?? '')) : '—',
                'office_id' => $b->office_id !== null ? (int) $b->office_id : null,
                'driver_id' => $b->driver_id !== null ? (int) $b->driver_id : null,
                'source' => (string) $b->source,
                'service' => (string) $b->service,
                'total_minor' => (int) ($b->total_minor ?? 0),
                'currency' => (string) $b->currency_code,
                'payment_method' => (string) $b->payment_method,
                'status' => (string) $b->status,
                'created_at' => $b->created_at,
            ];
        });

        return view('panel.rides.index', [
            'entity' => $scope->guard(),
            'isAdmin' => $scope->isAdmin(),
            'rows' => $rows,
            'statusFilter' => $status,
            'search' => $search,
            'statuses' => ['scheduled', 'matching', 'assigned', 'arriving', 'arrived', 'on_trip', 'completed', 'cancelled'],
            'counts' => [
                'total' => $rows->count(),
                'scheduled' => $rows->where('status', BookingStatus::SCHEDULED)->count(),
                'live' => $rows->whereIn('status', ['matching', 'assigned', 'arriving', 'arrived', 'on_trip'])->count(),
                'completed' => $rows->where('status', BookingStatus::COMPLETED)->count(),
                'cancelled' => $rows->whereIn('status', ['cancelled', 'rejected'])->count(),
            ],
        ]);
    }
}
