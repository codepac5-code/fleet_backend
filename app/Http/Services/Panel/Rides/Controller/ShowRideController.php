<?php

namespace App\Http\Services\Panel\Rides\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Authorization\PanelPermission;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\CommissionSnapshot;
use App\Models\Driver;
use App\Models\Office;
use App\Models\RideBooking;
use App\Models\RideRating;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\View\View;

class ShowRideController extends Controller
{
    public function __invoke(int $ride, EntityScope $scope): View
    {
        $conn = TenantConnection::current();

        $booking = RideBooking::on($conn)->find($ride);

        if ($booking === null || (! $scope->isAdmin() && (int) $booking->office_id !== (int) $scope->officeId())) {
            abort(404);
        }

        $customer = User::query()->find($booking->user_id);
        $driver   = $booking->driver_id !== null ? Driver::on($conn)->find($booking->driver_id) : null;
        $vehicle  = $booking->vehicle_id !== null ? Vehicle::on($conn)->find($booking->vehicle_id)
                    : ($driver && $driver->vehicleId ? Vehicle::on($conn)->find($driver->vehicleId) : null);
        $office   = $booking->office_id !== null ? Office::on($conn)->find($booking->office_id) : null;

        $commission = CommissionSnapshot::on($conn)->where('booking_id', $booking->id)->first();

        $ratings = RideRating::on($conn)->where('booking_id', $booking->id)->get();
        $riderToDriver = $ratings->firstWhere('rater_type', 'user') ?? $ratings->firstWhere('rater_type', 'rider');
        $driverToRider = $ratings->firstWhere('rater_type', 'driver');

        // Ordered coordinate path for the map: pickup → stops → dropoff.
        $path = [];
        if ($booking->pickup_lat && $booking->pickup_lng) {
            $path[] = ['lat' => (float) $booking->pickup_lat, 'lng' => (float) $booking->pickup_lng, 'kind' => 'pickup', 'title' => $booking->pickup_title];
        }
        foreach ((array) ($booking->stops ?? []) as $i => $stop) {
            $lat = $stop['lat'] ?? $stop['latitude'] ?? null;
            $lng = $stop['lng'] ?? $stop['longitude'] ?? null;
            if ($lat && $lng) {
                $path[] = ['lat' => (float) $lat, 'lng' => (float) $lng, 'kind' => 'stop', 'title' => $stop['title'] ?? ('Stop ' . ($i + 1))];
            }
        }
        if ($booking->dropoff_lat && $booking->dropoff_lng) {
            $path[] = ['lat' => (float) $booking->dropoff_lat, 'lng' => (float) $booking->dropoff_lng, 'kind' => 'dropoff', 'title' => $booking->dropoff_title];
        }

        return view('panel.rides.show', [
            'entity'        => $scope->guard(),
            'booking'       => $booking,
            'customerName'  => $customer ? trim(($customer->firstName ?? '') . ' ' . ($customer->lastName ?? '')) : null,
            'customerPhone' => $customer->phoneNumber ?? null,
            'driverName'    => $driver ? trim(($driver->firstName ?? '') . ' ' . ($driver->lastName ?? '')) : null,
            'driverPhone'   => $driver->phoneNumber ?? null,
            'vehicle'       => $vehicle,
            'officeName'    => $office->officeName ?? null,
            'commission'    => $commission,
            'riderRating'   => $riderToDriver,
            'driverRating'  => $driverToRider,
            'path'          => $path,
            'mapKey'        => config('services.google_maps.key'),
            'canRefund'     => $scope->user()?->can(PanelPermission::EDIT_COMMISSION) ?? false,
        ]);
    }
}
