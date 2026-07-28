<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Support\Facades\View;

class BookingController extends Controller
{
    public function view(){
        return View('booking.scheduled');
    }

    public function index(Request $request)
    {
        $query = Booking::query()->with(['driver', 'user']);

        $query = $this->applyFilters($query, $request);

        $perPage = $request->per_page ?? 10;

        $data = $query->latest()->paginate($perPage);

        return response()->json($data);
    }

    public function stats(Request $request)
    {
        $query = Booking::query();
        $query = $this->applyFilters($query, $request);

        $stats = $query->selectRaw('
            COUNT(*) as total_trips,
            COALESCE(SUM(totalAmount),0) as total_revenue,
            COALESCE(SUM(distance),0) as total_distance
        ')->first();

        return response()->json([
            'total_trips' => $stats->total_trips,
            'total_revenue' => getPriceFormat( number_format($stats->total_revenue, 2)),
            'total_distance' => number_format($stats->total_distance, 2) .'km',
        ]);
    }

    public function assignDriver(Request $request)
    {
        $data = $request->validate([
            'trip_id' => 'required|exists:bookings,id',
            'driver_id' => 'required|exists:drivers,id',
        ]);

        $booking = Booking::with(['driver','user'])->findOrFail($data['trip_id']);

        $booking->driverId = $data['driver_id'];
        $booking->status = 'In Progress';
        $booking->save();

        return response()->json([
            'statusCode' => 200,
            'message' => 'تم تعيين السائق بنجاح',
            'data' => $booking->fresh()->load(['driver','user']),
        ], 200);
    }

    public function cancel(Request $request)
    {
        $data = $request->validate([
            'trip_id' => 'required|exists:bookings,id',
        ]);

        $booking = Booking::findOrFail($data['trip_id']);

        $booking->status = 'Cancelled';
        $booking->save();

        return response()->json([
            'statusCode' => 200,
            'message' => 'تم إلغاء الرحلة',
            'data' => $booking,
        ], 200);
    }

    public function show($id)
    {
        $booking = Booking::with(['driver','user'])->findOrFail($id);

        return response()->json([
            'statusCode' => 200,
            'message' => 'تفاصيل الرحلة',
            'data' => $booking,
        ], 200);
    }

    public function drivers(Request $request)
    {
        $q = Driver::query();

        if ($request->search) {
            $q->whereRaw("CONCAT(firstName,' ',lastName) LIKE ?", ["%{$request->search}%"]);
        }

        $drivers = $q->limit(20)->get();

        return response()->json([
            'statusCode' => 200,
            'message' => 'drivers list',
            'data' => $drivers,
        ], 200);
    }

    /**
     * 🔥 Shared filters for both index + stats
     */
    private function applyFilters($query, Request $request)
    {
        $statusMap = [
            'scheduled'   => 'Pending',
            'in_progress' => 'In Progress',
            'completed'   => 'Completed',
            'cancelled'   => 'Cancelled',
        ];

        if ($request->status && isset($statusMap[$request->status])) {
            $query->where('status', $statusMap[$request->status]);
        }

        if ($request->driver) {
            $query->where('driverId', $request->driver);
        }

        if ($request->from) {
            $query->where('startAddress', $request->from);
        }

        if ($request->to) {
            $query->where('endAddress', $request->to);
        }

        if ($request->date) {
            $query->whereDate('startAt', $request->date);
        }

        if ($request->range === 'today') {
            $query->whereDate('startAt', now());
        }

        if ($request->range === 'tomorrow') {
            $query->whereDate('startAt', now()->addDay());
        }

        if ($request->from_date && $request->to_date) {
            $query->whereBetween('startAt', [
                $request->from_date,
                $request->to_date
            ]);
        }

        return $query;
    }
}
