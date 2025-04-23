<?php
namespace App\Http\Core\Classes\Operations;

use App\Http\Core\Classes\HelperClasses\FromTo;
use App\Http\Core\Classes\RedisManagerData;
use App\Models\FleetStatistic;
use PhpParser\Node\Expr\Cast\Double;

abstract class FleetSystemOperationGo {

    public static function add_moeny_to_pending_amount($amount )  {
        $pending_amount = RedisManagerData::get_system_pending_amount();
        $new_pending_amount = $pending_amount + $amount;
        RedisManagerData::set_system_pending_amount($new_pending_amount);
        return $new_pending_amount;
    }

    public static function move_moeny_from_available_to_withdrawn($amount) : FromTo {
        //from
        $satistic = FleetStatistic::first();
        $new_available_amount = $satistic->available_amount - $amount;
        $new_withdrawn_amount = $satistic->available_amount + $amount;
        FleetStatistic::first()->update(['withdrawn_amount'=>$new_withdrawn_amount , 'available_amount' => $new_available_amount ]);
        return (new FromTo( $new_available_amount , $new_withdrawn_amount ));
    }

    public static function move_moeny_from_pinding_to_offices_debt($amount) : FromTo {
        //from
        $pending_amount = RedisManagerData::get_system_pending_amount();
        $new_pending_amount = $pending_amount - $amount;
        RedisManagerData::set_system_pending_amount($new_pending_amount);
        //to
        $satistic = FleetStatistic::first();
        $new_offices_debt_amount = $satistic->offices_debt + $amount;
        FleetStatistic::first()->update(['pending_amount'=>$new_pending_amount , 'offices_debt' => $new_offices_debt_amount ]);
    
        return (new FromTo( $new_offices_debt_amount , $new_pending_amount ));
    }

    public static function move_moeny_from_pinding_to_drivers_debt($amount) : FromTo {
        //from
        $pending_amount = RedisManagerData::get_system_pending_amount();
        $new_pending_amount = $pending_amount - $amount;
        RedisManagerData::set_system_pending_amount($new_pending_amount);
        //to
        $satistic = FleetStatistic::first();
        $new_drivers_debt_amount = $satistic->drivers_debt + $amount;
        FleetStatistic::first()->update(['pending_amount'=>$new_pending_amount , 'drivers_debt' => $new_drivers_debt_amount ]);
    
        return (new FromTo( $new_drivers_debt_amount , $new_pending_amount ));
    }

    public static function move_moeny_from_pinding_to_available($amount) : FromTo {
        //from
        $pending_amount = RedisManagerData::get_system_pending_amount();
        $new_pending_amount = $pending_amount - $amount;
        RedisManagerData::set_system_pending_amount($new_pending_amount);
        //to
        $satistic = FleetStatistic::first();
        $new_available_amount = $satistic->available_amount + $amount;
        FleetStatistic::first()->update(['pending_amount'=>$new_pending_amount , 'available_amount' => $new_available_amount ]);
    
        return (new FromTo( $new_available_amount , $new_pending_amount ));
    }
    //--------------------------//------------------------//-------------------//----------------//



    public static function add_orders_to_pinding_rides($orders) :int {
        $new_count = RedisManagerData::get_system_daily_pending_rides() + $orders;
        if($new_count < 0 ){$new_count = 0;}
        RedisManagerData::set_system_daily_pending_rides(
            $new_count );
        return $new_count;
    }

    

    public static function move_orders_from_pinding_to_ongoing($orders) : FromTo {
        //  from    
        $count_pending_rides = RedisManagerData::get_system_daily_pending_rides();
        $new_pending_count = $count_pending_rides - $orders;
        RedisManagerData::set_system_daily_pending_rides($new_pending_count);
    
        // to 
        $count_ongoing_rides = RedisManagerData::get_system_daily_ongoing_rides();
        $new_ongoing_count = $count_ongoing_rides + $orders;
        RedisManagerData::set_system_daily_ongoing_rides($new_ongoing_count);

        //return result
        return (new FromTo($new_pending_count , $new_ongoing_count));

    }


    public static function move_orders_from_ongoing_to_completed($orders):FromTo {

        $ongoing_rides = RedisManagerData::get_system_daily_ongoing_rides();
        $new_ongoing_count = $ongoing_rides - $orders;
        RedisManagerData::set_system_daily_ongoing_rides($new_ongoing_count);

        $completed_rides = RedisManagerData::get_system_daily_completed_rides();
        $new_completed_count = $completed_rides + $orders;
        RedisManagerData::set_system_daily_completed_rides($completed_rides + $orders);

        return (new FromTo($new_ongoing_count , $new_completed_count));

    }




}