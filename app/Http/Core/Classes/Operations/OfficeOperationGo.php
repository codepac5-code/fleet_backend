<?php
namespace App\Http\Core\Classes\Operations;

use App\Http\Core\Classes\HelperClasses\FromTo;
use App\Http\Core\Classes\RedisManagerData;
use App\Models\OfficeStatistic;

abstract class OfficeOperationGo {

    public static function add_moeny_to_pending_amount($officeId , $amount )  {
        $pending_amount = RedisManagerData::get_office_pending_amount($officeId , $amount);
        $new_pending_amount = $pending_amount - $amount;
        if( $new_pending_amount < 0 ){ $new_pending_amount = 0;}
        RedisManagerData::set_office_pending_amount( $officeId , $new_pending_amount );
        return $new_pending_amount;
    }

    public static function move_moeny_from_available_to_withdrawn($officeId , $amount ) : FromTo {
        //from
        $satistic = OfficeStatistic::find($officeId);
        $new_available_amount = $satistic->available_amount - $amount;
        $new_withdrawn_amount = $satistic->available_amount + $amount;
        OfficeStatistic::find($officeId)->update(['withdrawn_amount'=>$new_withdrawn_amount , 'available_amount' => $new_available_amount ]);
        return (new FromTo( $new_available_amount , $new_withdrawn_amount ));
    }

    public static function move_moeny_from_pinding_to_offices_debt($officeId , $amount ) : FromTo {
        //from
        $pending_amount = RedisManagerData::get_office_pending_amount($officeId);
        $new_pending_amount = $pending_amount - $amount;
        RedisManagerData::set_office_pending_amount($officeId , $new_pending_amount);
        //to
        $satistic =  OfficeStatistic::find($officeId);
        $new_offices_debt_amount = $satistic->offices_debt + $amount;
        OfficeStatistic::find($officeId)->update(['pending_amount'=>$new_pending_amount , 'offices_debt' => $new_offices_debt_amount ]);
        return (new FromTo( $new_offices_debt_amount , $new_pending_amount ));
    }

    public static function move_moeny_from_pinding_to_drivers_debt( $officeId , $amount ) : FromTo {
        //from
        $pending_amount = RedisManagerData::get_office_pending_amount($officeId);
        $new_pending_amount = $pending_amount - $amount;
        RedisManagerData::set_office_pending_amount( $officeId , $new_pending_amount );
        //to
        $satistic =  OfficeStatistic::find( $officeId );
        $new_drivers_debt_amount = $satistic->drivers_debt + $amount;
        OfficeStatistic::find( $officeId )->update([ 'pending_amount' => $new_pending_amount , 
        'drivers_debt' => $new_drivers_debt_amount ]);

        return (new FromTo( $new_drivers_debt_amount , $new_pending_amount ));
    }

    ///=---------------- <<<   XGo A     >>>>> -------------

    public static function move_moeny_from_pinding_to_available($officeId , $amount ) : FromTo {
        //from
        $pending_amount = RedisManagerData::get_system_pending_amount();
        $new_pending_amount = $pending_amount - $amount;
        RedisManagerData::set_system_pending_amount($new_pending_amount);
        //to
        $satistic =  OfficeStatistic::find($officeId);
        $new_available_amount = $satistic->available_amount + $amount;
        OfficeStatistic::find($officeId)->update(['pending_amount'=>$new_pending_amount , 'available_amount' => $new_available_amount ]);
    
        return (new FromTo( $new_available_amount , $new_pending_amount ));
    }


    //------------------------------------------------------

    public static function move_orders_from_pinding_to_ongoing($officeId , $orders) : FromTo {
        // from    
        $count_pending_rides = RedisManagerData::get_office_daily_pending_rides($officeId , $orders);
        $new_pending_count = $count_pending_rides - $orders;
        RedisManagerData::set_office_daily_pending_rides($officeId , $new_pending_count);
        // to 
        $count_ongoing_rides = RedisManagerData::get_office_daily_ongoing_rides($officeId);
        $new_ongoing_count = $count_ongoing_rides + $orders;
        RedisManagerData::set_office_daily_ongoing_rides($officeId , $new_ongoing_count);

        // return result
        return (new FromTo($new_pending_count , $new_ongoing_count));
    }


    public static function move_orders_from_ongoing_to_completed($officeId ,$orders):FromTo {

        $ongoing_rides = RedisManagerData::get_office_daily_ongoing_rides($officeId );
        $new_ongoing_count = $ongoing_rides - $orders;
        RedisManagerData::set_office_daily_ongoing_rides($officeId ,$new_ongoing_count);

        $completed_rides = RedisManagerData::get_office_daily_completed_rides($officeId);
        $new_completed_count = $completed_rides + $orders;
        RedisManagerData::set_office_daily_completed_rides($officeId , $completed_rides + $orders);

        return (new FromTo($new_ongoing_count , $new_completed_count));
    }
}