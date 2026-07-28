<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OfficeController extends Controller
{

       public function resetCommission(Request $request)
        {
        $office = Office::find($request->office_id);
        if (!$office) {
            return response()->json([
                'success' => false,
                'message' => __('messages.something_wrong')
            ]);
        }

        $defaultOfficeCommission = 17;
        $office->isFleetCommissionCustom = false;

        // $office->office_commission = $defaultofficeCommission;
        // $office->office_commission = 100 - $defaultofficeCommission;
        $office->save();

        return response()->json([
            'success' => true,
            'office_commission' => $defaultOfficeCommission,
            'message' => __('messages.commission_reset_success')
        ]);
    }

    public function customersBookings($officeId, $userId)
    {
        $office = Office::findOrFail($officeId);

        $user = User::findOrFail($userId);

        $bookings = Booking::where('userId', $user->id)
                            ->where('officeId', $office->id)
                            ->get();

        $user->totalBookings = $bookings->count();
        $user->totalAmount = $bookings->sum('totalAmount');
        $user->totalDistance = $bookings->sum('distance');
        $user->lastBookingAt = $bookings->sortByDesc('startAt')->first()?->startAt;
        $user->averageRating = $bookings->avg('rating') ?? 0;
        $user->lastPaymentStatus = $bookings->sortByDesc('PaymentDatetime')->first()?->paymentStatus;

        return view('office.customer.show', compact('office','user'));
    }


    public function updateCommission(Request $request){
        // dd($request->all());

        $validator = Validator::make($request->all(), [
            'office_id' => 'required|exists:offices,id',
            'office_commission' => 'required|numeric|min:0|max:100',
            // 'fleet_commission' => 'required|numeric|min:0|max:100',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $fleet_commission = 100 - $request->office_commission;
        $total = $request->office_commission + $fleet_commission;
        if ($total > 100) {
            return response()->json([
                'success' => false,
                'message' => 'مجموع العمولتين يجب أن لا يتجاوز 100%'
            ]);
        }

         if ($total != 100) {
            return response()->json([
                'success' => false,
                'message' => 'مجموع العمولتين يجب أن يطابق الـ 100%'
            ]);
        }

        $office = Office::find($request->office_id);
        $office->update([
            'isFleetCommissionCustom'    => true,
            'commissionCustomValue'      => $request->office_commission,
        ]);




    return response()->json([
        'success' => true,
        'message' => 'تم حفظ العمولة المخصصة بنجاح',
        'office_commission'           => $request->office_commission,
    ]);

    }
}
