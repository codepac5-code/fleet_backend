<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class UserBookingController extends Controller
{
    public function getUserBookings(Request $request)
    {
        $query = Booking::query()
            ->where('userId', $request->userId);

        if ($request->startDate) {
            $query->whereDate('startAt', '>=', $request->startDate);
        }
        if ($request->endDate) {
            $query->whereDate('startAt', '<=', $request->endDate);
        }

        if ($request->orderId) {
            $query->where('id', $request->orderId);
        }

        return DataTables::of($query)
            ->editColumn('startAt', function($row) {
                return $row->startAt ? $row->startAt->format('Y-m-d H:i') : '';
            })
            ->editColumn('totalAmount', function($row) {
                return number_format($row->totalAmount, 2) ;
            })
            ->editColumn('distance', function($row) {
                return $row->distance . ' كم';
            })
            ->editColumn('paymentStatus', function($row) {
                return $row->paymentStatus === 'paid'
                    ? '<span class="badge bg-success">مدفوع</span>'
                    : '<span class="badge bg-danger">غير مدفوع</span>';
            })
            ->editColumn('status', function($row) {
                $statusLabels = [
                    'pending' => 'قيد الانتظار',
                    'completed' => 'مكتمل',
                    'canceled' => 'ملغي'
                ];
                $class = [
                    'pending' => 'warning',
                    'completed' => 'success',
                    'canceled' => 'danger'
                ];
                return '<span class="badge bg-'.$class[$row->status].'">'.$statusLabels[$row->status].'</span>';
            })
            ->rawColumns(['paymentStatus', 'status'])
            ->make(true);
    }
}
