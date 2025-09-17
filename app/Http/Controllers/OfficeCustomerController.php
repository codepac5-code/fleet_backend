<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\officeUserStats;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class OfficeCustomerController extends Controller
{

public function index(Request $request){
    $officeId = $request->query('officeId', null); 
    $list_status = 'all'; 
    return view('office.customer.index', compact('officeId', 'list_status'));
}


    public function officeCustomersData(){
        // $officeId = $request->input('officeId');
    
        // if (!$officeId) {
        //     return response()->json(['error' => 'officeId is required'], 400);
        // }

        $query = OfficeUserStats::with(['user:id,firstName,lastName,gender,photo']);

        if (Auth::guard('admin')->check()) {
            $query =  $query->get();
            $officeId = null;
        }

        else if (Auth::guard('office')->check()) {
            $office = Auth::guard('office')->user();
            $officeId = $office->id;
            $query = $query->where('officeId', $officeId)->get();
        }

        else if (Auth::guard('employee')->check()) {
            $employee = Auth::guard('employee')->user();
            if ($employee->officeId) {
                $officeId = $employee->officeId;
                $query =  $query->where('officeId', $officeId)->get();
            } else {
                $officeId = null;
                $query =  $query->get();
            }
        }



    
        return DataTables::of($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-'.$row->id.'" name="datatable_ids[]" value="'.$row->userId.'" data-type="user" onclick="dataTableRowCheck('.$row->userId.',this)">';
            })
            ->addColumn('display_name', function ($row ) {
                $user = $row->user;
                $officeId = $row->officeId;
                if (!$user) return '-';
                return view('office.customer.customer-name', compact('user','officeId'))->render();
            })
            ->addColumn('gender', function ($row) {
                return $row->user->gender ?? '--';
            })
            ->addColumn('totalBookings', function ($row) {
                return $row->totalBookings ?? 0;
            })
            ->addColumn('totalAmount', function ($row) {
                return getPriceFormat($row->totalAmount) ?? 0;
            })
            ->addColumn('totalDistance', function ($row) {
                return $row->totalDistance ?? 0;
            })
            ->addColumn('lastBookingAt', function ($row) {
                return $row->lastBookingAt ? $row->lastBookingAt : '-';
            })
            ->addColumn('averageRating', function ($row) {
                return $row->averageRating ?? 0;
            })
            ->addColumn('lastPaymentStatus', function ($row) {
                return $row->lastPaymentStatus ?? '-';
            })
            ->addIndexColumn()
            ->rawColumns(['check','display_name'])
            ->make(true);
    
}

    
    public function show($officeId ,$customerId )
    {

        // $officeId = $request->input('officeId');
        // $customerId = $request->input('customerId');

        // if (!$officeId ) {
        //     return response()->json(['error' => 'officeId is required'], 400);
        // }
        // if (!$customerId) {
        //     return response()->json(['error' => 'customerId is required'], 400);
        // }

        $customer   = User::findOrFail($customerId);
        $office     = Office::findOrFail($officeId);
    
        $stats = DB::table('office_user_stats')
            ->where('officeId', $office->id)
            ->where('userId', $customer->id)
            ->first();
    
        $totalBookings = $stats->totalBookings ?? 0;
        $totalAmount = $stats->totalAmount ?? 0;
        $totalDistance = $stats->totalDistance ?? 0;
        $lastBookingAt = $stats->lastBookingAt ?? null;
        $averageRating = $stats->averageRating ?? 0;
        $lastPaymentStatus = $stats->lastPaymentStatus ?? 'لا يوجد';
    
        return view('office.customer.show', compact(
            'office',
            'customer',
            'totalBookings',
            'totalAmount',
            'totalDistance',
            'lastBookingAt',
            'averageRating',
            'lastPaymentStatus'
        ));
    }
    
}
