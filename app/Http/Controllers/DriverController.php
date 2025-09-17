<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\OfficeDriverCustomCommission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DriverController extends Controller
{
    public function updateCommission(\Illuminate\Http\Request $request)
    {
        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|exists:drivers,id',
            'office_commission' => 'required|numeric|min:0|max:100',
            'driver_commission' => 'required|numeric|min:0|max:100',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }
    

        $total = $request->office_commission + $request->driver_commission;
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

        if (Auth::guard('admin')->check()) {
            return $this->saveFleetCommission($request->driver_id ,$request->driver_commission ,
            $request->office_commission);

        }

        else if (Auth::guard('office')->check()) {
            $office = Auth::guard('office')->user();
            return $this->saveOfficeCommission($office->id , $request->driver_id , 
            $office->id , $request->driver_commission ,
            $request->office_commission);
        }

        else if (Auth::guard('employee')->check()) {
            $employee = Auth::guard('employee')->user();
            if ($employee->officeId) {
                $officeId = $employee->officeId;
                return $this->saveOfficeCommission($officeId , $request->driver_id , 
                $officeId , $request->driver_commission ,
                $request->office_commission);
            } else {
                return $this->saveFleetCommission($request->driver_id  ,$request->driver_commission ,
                $request->office_commission);
            }
        }

    
        return response()->json([
            'success' => false,
            'message' => __('messages.something_wrong')
        ]);
    }



    public function saveOfficeCommission($officeId , $driverId , $driverCommission , $officeCommission){
    


        $customCommission = OfficeDriverCustomCommission::updateOrCreate(
            [
                'driverId' => $driverId,
                'officeId' => $officeId,
            ],
            [
                'driverCommission' =>$driverCommission ,
                'officeCommission'=>$officeCommission,
            ]
        );

         Driver::update(['id'=>$driverId ],
         ['isOfficeCommissionCustom'=>true]);


        return response()->json([
            'success' => true,
            'message' => 'تم حفظ العمولة المخصصة بنجاح',
            'data' => $driverCommission
        ]);
    }

    public function saveFleetCommission( $driverId ,$driverCommission , $officeCommission){
        $customCommission = Driver::updateOrCreate(
            [
                'id' => $driverId,
            ],
            [
                'fleetCommissionCustomValue' =>$officeCommission ,
                'driverCommissionCustomValue'=>$driverCommission,
                'isFleetCommissionCustom'=> true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ العمولة المخصصة بنجاح',
            'data' => $driverCommission
        ]);
    }
}
