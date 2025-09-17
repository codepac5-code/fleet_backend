<?php
namespace App\Http\Repositories\BookingRepositories;

use App\Http\Core\Const\Options\OrderStatus;
use App\Models\Booking;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use Illuminate\Support\Facades\DB;

class BookingReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Booking();
    }

    public function getScheduledBookingsForAuthDriver(){

        return $this->model::with(['subService','user','payment'])
        ->where('driverId', auth()->id())
        ->where('is_scheduled', true)
        ->where('scheduled_time', '>=', now())
        ->orderBy('scheduled_time', 'asc')
        ->get();
        }
    

    public function getScheduledBookingsForAuthUser(){

        return $this->model::with(['subService','driver','payment'])
        ->where('userId', auth()->id())
        ->where('is_scheduled', true)
        ->where('scheduled_time', '>=', now())
        ->orderBy('scheduled_time', 'asc')
        ->get();
    }

    public function getEarning(array $date ,array $selected = ["*"], array $conditions=[]){
        $summary = DB::table('bookings')
        ->select(
            DB::raw('COUNT(id) AS total_orders'),
            DB::raw('SUM(distance) AS total_km'),
            DB::raw('SUM(driverCommissionValue) AS total_earning'),
            // DB::raw('fleetCommissionValue + officeCommissionValue AS officeCommission'),
            )
        ->where($conditions)
        ->whereBetween('created_at', $date)
        ->first();

    $records = $this->model->query()->select($selected)
        ->where($conditions)
        ->whereBetween('created_at', $date)
        ->orderBy('updated_at','desc')
        ->get();

    return [
        'summary' => $summary,
        'records' => $records
    ];

    // return $model = $this->model->query()->select($selected)
    //     ->whereBetween('created_at',$date)
    //     ->where($conditions)
    //     ->orderBy('updated_at','desc')
    //     ->get();
    }

    public function getBookings(){
        return  $this->model->query()
        ->select(['*'])->orderBy('created_at','asc')->get();
    }

    public function getAllBookingWithOrderBy(array $selected = ["*"] , array $with=[] , array $conditions=[] ){
        return $this->model
        ->scopeForCurrentUser()
        ->select($selected)->with($with)->where($conditions)->orderBy('created_at','desc')->paginate(10);
    }

    public function getCompletedOrders($officeId){
        $model = $this->model
        ->scopeForCurrentUser()
        ->select(['*'])
        ->where(
            ['status'=>OrderStatus::$Completed],
            ['status'=>OrderStatus::$Cancelled],
            ['status'=>OrderStatus::$Hold]
        );

        return $model->orderBy('created_at','desc')->get();
    }

    public function getOngoingOrders($officeId){
         $model = $this->model
         ->scopeForCurrentUser()
         ->select(['*'])->where(['status'=>OrderStatus::$InProgress]);

        return $model->orderBy('created_at','desc')->get();
    }

    public function getPendingOrders(){
        return $this->model
        ->scopeForCurrentUser()
        ->select(['*'])
        ->where(['status'=>OrderStatus::$SearchOnDriver])->orderBy('created_at','desc')->get();
    }



}
