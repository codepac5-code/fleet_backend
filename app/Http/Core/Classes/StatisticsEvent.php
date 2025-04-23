<?php
namespace App\Http\Core\Classes;

use App\Events\SatisticEvents\AdminSatisticEvent;
use App\Events\SatisticEvents\OfficeSatisticEvent;

enum StatisticsEvent : string {

    case Withdrawn_Card = 'withdrawn-amount';
    case Pending_Card   = 'pending-amount';
    case Total_Card     = 'total-amount';
    case Available_Card = 'available-amount';
    
//- - - - - - - - - - - - - - - - - - - - - - - - - - ------------------
    case Ongoing_Ride   = 'ongoing-ride';
    case Pending_Ride   = 'pending-ride';
    case Completed_Ride = 'completed-ride';

//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    case Users      = 'users';
    case Drivers    = 'drivers';
    case Offices    = 'offices';
    case Services   = 'services';


//-----------------

    case OfficeDue = 'office-due-amount';
    case DriverDue = 'driver-due-amount';




 
    public function send_event_to_admin($value){
        $card_name = $this->value;
        broadcast(new AdminSatisticEvent($card_name , $value ));
    }

    public function sen_event_to_office($officeId ,$value){
        $card_name = $this->value;
        broadcast(new OfficeSatisticEvent($officeId ,$card_name , $value ));
    }



}