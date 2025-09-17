<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use App\Models\Booking;
use App\Models\FleetOffice;
use Carbon\Carbon;


class FleetDashboardController extends Controller
{
    public function getDashboardStats(Request $request)
    {
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        $bookingQuery = Booking::query();
        $walletQuery = WalletTransaction::query();

        if ($start && $end) {
            $bookingQuery->whereBetween('created_at', [$start, $end]);
            $walletQuery->whereBetween('created_at', [$start, $end]);
        }

        $fleetData = FleetOffice::first();

        $cashPayments =  (clone $bookingQuery)->where('paymentStatus', 'paid')->where('paymentId', null);
        $electronicPayments = (clone $bookingQuery)->where('paymentStatus', 'paid')->whereNotNull('paymentId'); 

        return response()->json([
            'totalIncome'       => number_format ($fleetData->total_income, 2),
            'availableAmount'   => number_format ($fleetData->available_amount, 2),
            'withdrawnAmount'   => number_format ($fleetData->withdrawn_amount, 2),
            'officesDebt'       => number_format ($fleetData->offices_debt, 2),
            'driversDebt'       => number_format ($fleetData->drivers_debt, 2),

            'electronicPaymentCount' => 777, //$electronicPayments->count(),
            'electronicPaymentValue' => 777,// number_format($electronicPayments->sum('totalAmount'), 2),

            'cashPaymentCount' => 777, // $cashPayments->count(),
            'cashPaymentValue' => 777, // number_format($cashPayments->sum('totalAmount'), 2),
        ]);
    }
}
