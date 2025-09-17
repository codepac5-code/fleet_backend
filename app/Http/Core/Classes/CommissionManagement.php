<?php
namespace App\Http\Core\Classes;

use App\Models\Booking;
use App\Models\FleetOffice;
use App\Models\Office;
use Illuminate\Support\Facades\Log;

abstract class CommissionManagement {

    public static function OrderCommissionCalculation(Booking $order , $driver = null) : Booking{

     
        if($driver == null && $order->driver != null){
            $driver = $order->driver;
        }

        if( $driver->isOfficeCommissionCustom){
            $order->fleetCommissionValue  = ($driver->fleetCommissionCustomValue / 100) * $order->amount;
            $order->driverCommissionValue = ($driver->driverCommissionCustomValue / 100) * $order->amount;
            $order->save();
            info('save commissions of order #'.$order->id.'in database');
            return $order;
        }
  
        
        $fleet = FleetOffice::first();

//------- fleet commissions 

        // 'fleet_commission_value_with_driver',
        // 'fleet_commission_value_with_office',
        // 'office_commission_value',
        // 'driver_commission_value',

    

//------- office commissions

        // 'commission_with_office_car',
        // 'commission_with_driver_car',
        // 'driver_commission_precentage',
        // 'driver_car_commission_precentage',

        if($driver->free_driver){
            info('calculat free driver commission:');
            $order->fleetCommissionValue  = ($fleet->fleet_commission_value_with_driver / 100) * $order->amount;
            info('fleet commission:'. $order->fleetCommissionValue);
            $order->officeCommissionValue = 0;
            $order->driverCommissionValue = ($fleet->driver_commission_value / 100) * $order->amount;
            info('driver commission:'. $order->driverCommissionValue);

            $order->fleetCommissionPercentage  = $fleet->fleet_commission_value_with_driver;
            $order->officeCommissionPercentage = 0;
            $order->driverCommissionPercentage = $fleet->driver_commission_value;
        } 
        
        elseif($driver->car_owner && $driver->officeId != null){
            $office = Office::find($driver->officeId);
            if($office == null){
                Log::error("order commission Calculation : office not fuond !");
                make_exception("order commission Calculation : office not fuond !");
            }
            // calculate fleet commission 
            $order->fleetCommission  = ($fleet->fleet_commission_value_with_office / 100) * $order->amount;
            // calculate office commission  
            $office_totalAmount      = ($fleet->office_commission_value / 100)  * $order->amount;
            $order->officeCommissionValue = ($office->commission_with_driver_car / 100) * $office_totalAmount;
            
            // calculate driver car commission from office commission 
            $order->driverCommission = ($office->driver_car_commission_precentage / 100) * $office_totalAmount;

            $order->fleetCommissionPercentage  = $fleet->fleet_commission_value_with_office;
            $order->officeCommissionPercentage = $office->commission_with_driver_car;
            $order->driverCommissionPercentage = $office->driver_car_commission_precentage;
            } 

        elseif(!($driver->car_owner) && $driver->officeId != null ){
            $office = Office::find($driver->officeId);
            if($office == null){
                Log::error("order commission Calculation : office not fuond !");
                make_exception("order commission Calculation : office not fuond !");
            }
            // calculate fleet commission 
            $order->fleetCommissionValue  = ($fleet->fleet_commission_value_with_office / 100) * $order->amount;
            // calculate office commission  
            $office_totalAmount      = ($fleet->office_commission_value / 100)  * $order->amount;
            $order->officeCommissionValue = ($office->commission_with_office_car / 100) * $office_totalAmount;
            // calculate driver car commission from office commission 
            $order->driverCommissionValue = ($office->driver_commission_precentage / 100) * $office_totalAmount;


            
            $order->fleetCommissionPercentage  = $fleet->fleet_commission_value_with_office;
            $order->officeCommissionPercentage = $office->commission_with_office_car;
            $order->driverCommissionPercentage = $office->driver_commission_precentage;
        }


        if($order->couponId != null){
            $coupon = $order->coupon;
            if($coupon->isPercentage){
                $discountAmount = $coupon->discount * $order->amount;
            }
            else {
                $discountAmount = max( $order->amount - $coupon->discount, 0);
            }
            $order->fleetCommissionValue = max($order->fleetCommissionValue - $discountAmount , 0); 
            $order->totalAmount = $order->amount - $discountAmount;
            
        }

        $order->save();
        info('save commissions of order #'.$order->id.'in database');
        return $order;
    }


    public static function get_driver_commission_by_office($driver){
        if($driver->car_owner){
            $office_commission = CommissionManagement::get_office_car_driver_commission($driver->officeId);
        }
        else {
            $office_commission = CommissionManagement::get_office_driver_commission($driver->officeId);
        }
            return $office_commission / 100 ;
        }
 

    public static function get_fleet_commission_by_driver($driver){
        if($driver->free_driver){
            $fleet_commission = CommissionManagement::get_fleet_freeDriver_commission();
        }
        else {
            $fleet_commission = CommissionManagement::get_fleet_office_commission();
        }
        return $fleet_commission / 100  ;
    }

    public static function get_fleet_freeDriver_commission() {
        $fleetOffice = FleetOffice::first();
        return $fleetOffice->driver_commission_value / 100;
    }

    public static function get_fleet_office_commission() {  
        $fleetOffice = FleetOffice::first();
        return $fleetOffice->office_commission_value/100;
    }


    public static function get_fleet_commission() {  
        $office = FleetOffice::first();
        return $office->commission_value/100;
    }

    public static function get_office_car_driver_commission($officeId) {  
        $office = Office::find($officeId);
        return $office->car_driver_commission_precentage /100;
    }


    public static function get_office_driver_commission($officeId) {  
        $office = Office::find($officeId);
        return $office->driver_commission_precentage/100;
    }







}