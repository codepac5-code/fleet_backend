<?php
namespace App\Http\Core\Classes;

enum RedisKeyTemplate: string
{
    case ORDER = 'order.{orderId}-info'; //order.500-info        
    case ORDER_NOT_ACCEPT = 'order-not-accept.{orderId}'; //order.500-not-accept
    case DRIVER_LOCATION = 'driver-location.{driverId}';  //driver.500-area
    case DRIVER_LONGITUDE_LATITUDE = 'driver-long-lat.{driverId}';  
    case AREA_DRIVERS = 'drivers-in-area:{area}';    
    case Online_Driver = 'online-driver-info.{driverId}';    



    //==============<<< SATISTICS  >>==========




    //-------------------- Fleet Satistic -------------------------------




    //-------------------- Daily System Rides Satistic -----
    case System_Daily_Completed_Rides     = 'system_daily_completed_rides';    
    case System_Daily_Ongoing_Rides       = 'system_daily_ongoing_rides';  
    case System_Daily_Pending_Rides       = 'system_daily_pending_rides';    


    //-------------------- Daily Office Rides Satistic -----
    case Office_Daily_Completed_Rides     = 'daily_completed_rides_office.{officeId}';    
    case Office_Daily_Ongoing_Rides       = 'daily_ongoing_rides_office.{officeId}';  
    case Office_Daily_Pending_Rides       = 'daily_pending_rides_office.{officeId}';   

    // ------------  Wallet System Transction ----------

    case System_Pending_Amount = 'system_pending_amount';
    case Office_Pending_Amount = 'office_pending_amount.{officeId}';

    /**
     * 
     *
     * @param array 
     * @return string
     */
    public function generateKey(array $variables): string
    {
        $template = $this->value;

        foreach ($variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }

        return $template;
    }



}
