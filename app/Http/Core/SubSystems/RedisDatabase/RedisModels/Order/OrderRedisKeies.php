<?php
namespace App\Http\Core\SubSystems\RedisDatabase\RedisModels\Order;

enum OrderRedisKeies : string
{

//----------------------<< fleet >>---------------------------------------------
    case ORDER = 'order.{orderId}-card';     
    case ORDER_STATUS = 'orders:status:{status}';

    case ORDER_ONGOING_COUNT    = 'order-ongoing-count_office.{officeId}';
    case ORDER_COMPLETED_COUNT  = 'order-completed-count_office.{officeId}';
    case ORDER_PENDING_COUNT    = 'order-Pending-count_office.{officeId}';


// ----------------------<< office >>--------------------------------------------




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
