<?php

namespace App\Jobs\FollowOrder;

use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\Order\OrderRedisModel;
use App\Models\Booking;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class MakePendingOrderCardJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private $orderId)
    {
        
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = Booking::first(['id'=>$this->orderId]);
        $order->status = OrderStatus::$Pending;
        OrderRedisModel::storeWithPagenationService($order);
        
    }
}
