<?php

namespace App\Http\Services\Panel\OfficeBookings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Ride\OfficeBookingService;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreOfficeBookingController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, OfficeBookingService $bookings): RedirectResponse
    {
        $data = $request->validate([
            'office_id' => ['nullable', 'integer'],
            'phone' => ['required', 'string', 'max:40'],
            'name' => ['nullable', 'string', 'max:120'],
            'service' => ['required', 'string', 'max:16'],
            'service_class' => ['required', 'string', 'max:32'],
            'pickup_lat' => ['required', 'numeric', 'between:-90,90'],
            'pickup_lng' => ['required', 'numeric', 'between:-180,180'],
            'pickup_title' => ['nullable', 'string', 'max:190'],
            'dropoff_lat' => ['required', 'numeric', 'between:-90,90'],
            'dropoff_lng' => ['required', 'numeric', 'between:-180,180'],
            'dropoff_title' => ['nullable', 'string', 'max:190'],
            'fare' => ['nullable', 'numeric', 'min:0'],
            'passengers' => ['nullable', 'integer', 'min:1', 'max:20'],
            'payment_method' => ['nullable', 'in:cash,office_wallet'],
            'scheduled_at' => ['nullable', 'date'],
            'assign_mode' => ['required', 'in:driver,broadcast'],
            'driver_id' => ['nullable', 'integer', 'required_if:assign_mode,driver'],
        ]);

        $officeId = $scope->isAdmin() ? (int) ($data['office_id'] ?? 0) : (int) $scope->officeId();

        if ($officeId <= 0) {
            return back()->with('error', textByLanguage('اختر المكتب أولاً.', 'Select an office first.'))->withInput();
        }

        $fareMinor = ($data['fare'] ?? null) !== null && $data['fare'] !== ''
            ? (int) round(((float) $data['fare']) * 100)
            : null;

        $createdBy = $scope->guard() . ':' . (optional($scope->user())->id ?? $officeId);

        try {
            $result = $bookings->create([
                'office_id' => $officeId,
                'phone' => $data['phone'],
                'name' => $data['name'] ?? null,
                'service' => $data['service'],
                'service_class' => $data['service_class'],
                'pickup' => ['lat' => $data['pickup_lat'], 'lng' => $data['pickup_lng'], 'title' => $data['pickup_title'] ?? null],
                'dropoff' => ['lat' => $data['dropoff_lat'], 'lng' => $data['dropoff_lng'], 'title' => $data['dropoff_title'] ?? null],
                'fare_minor' => $fareMinor,
                'passengers' => $data['passengers'] ?? null,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'assign' => ['mode' => $data['assign_mode'], 'driver_id' => $data['driver_id'] ?? null],
            ], $createdBy);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()
            ->route('panel.' . $scope->guard() . '.office-bookings.index')
            ->with('status', textByLanguage('تم إنشاء الحجز المكتبي رقم ', 'Office booking #') . $result['booking_id'] . textByLanguage(' بنجاح.', ' created.'));
    }
}
