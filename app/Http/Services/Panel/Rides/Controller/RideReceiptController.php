<?php

namespace App\Http\Services\Panel\Rides\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Driver;
use App\Models\Office;
use App\Models\RideBooking;
use App\Models\User;
use Illuminate\View\View;

class RideReceiptController extends Controller
{
    public function __invoke(int $ride, EntityScope $scope): View
    {
        $conn = TenantConnection::current();

        $booking = RideBooking::on($conn)->find($ride);

        // Per-country + office-scoped: an office can only print its own rides.
        if ($booking === null || (! $scope->isAdmin() && (int) $booking->office_id !== (int) $scope->officeId())) {
            abort(404);
        }

        $customer = User::query()->find($booking->user_id);
        $driver = $booking->driver_id !== null ? Driver::on($conn)->find($booking->driver_id) : null;
        $office = $booking->office_id !== null ? Office::on($conn)->find($booking->office_id) : null;

        return view('panel.rides.receipt', [
            'booking' => $booking,
            'customerName' => $customer ? trim(($customer->firstName ?? '') . ' ' . ($customer->lastName ?? '')) : null,
            'customerPhone' => $customer->phoneNumber ?? null,
            'driverName' => $driver ? trim(($driver->firstName ?? '') . ' ' . ($driver->lastName ?? '')) : null,
            'officeName' => $office->officeName ?? null,
        ]);
    }
}
