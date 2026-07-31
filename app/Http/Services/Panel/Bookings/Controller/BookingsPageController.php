<?php

namespace App\Http\Services\Panel\Bookings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Bookings\Logic\BookingRepository;
use App\Http\Services\Panel\Bookings\Logic\BookingStatus;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\RideBooking;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Throwable;

class BookingsPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, BookingRepository $bookings, OfficeRepository $offices): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '') ?: null;
        $officeId = $scope->isAdmin() ? (int) $request->query('office') : null;

        return view('panel.bookings.index', [
            'entity'        => $scope->guard(),
            'user'          => $scope->user(),
            'isAdmin'       => $scope->isAdmin(),
            'search'        => $search,
            'statusFilter'  => $status,
            'officeFilter'  => $officeId ?: null,
            'statusOptions' => BookingStatus::settable(),
            'officeOptions' => $scope->isAdmin() ? $offices->options() : [],
            'bookings'      => $bookings->paginate($search ?: null, $status, $officeId ?: null),
            // This screen reads the OLD dashboard's `bookings` table. Rides
            // created by the apps land in `ride_bookings` and never appear here,
            // so the page says how many it is not showing instead of looking
            // like a quiet day.
            'appRideCount'  => $this->appRideCount($scope),
        ]);
    }

    private function appRideCount(EntityScope $scope): int
    {
        try {
            $query = RideBooking::on(TenantConnection::current());

            if (! $scope->isAdmin()) {
                $query->where('office_id', (int) $scope->officeId());
            }

            return (int) $query->count();
        } catch (Throwable $e) {
            return 0;
        }
    }
}
