<?php
namespace App\Http\Core\Classes;

use App\Events\AdminEvents\A_New_Order_Event;
use App\Events\AdminEvents\Office_New_Order_Event;

enum DashboardEventsName : string {

//--------- follow orders

    case New_Order_Ongoing      = 'new-order-ongoing';
    case New_Order_Completed    = 'new-order-completed';
    case New_Order_Pending       = 'new-order-pending';

 
    public function send_event_to_admin_follow_orders( $orders_count){
        $table_status_name = $this->value;
        broadcast(new A_New_Order_Event($table_status_name , $orders_count ));
    }

    public function sen_event_to_office_follow_orders( $orders_count ){
        $table_status_name = $this->value;
        broadcast(new Office_New_Order_Event($table_status_name , $orders_count ));
    }



}