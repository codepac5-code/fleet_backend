<?php
namespace App\Http\Repositories\BookingRepositories;

use App\Http\Core\Const\Selected\SelectByLanguage;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;

class BookingReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Booking();
    }

    public function getOrdersByStatusAfterId( $status , $last_id){

                $bookings = $this->model->scopeForCurrentUser()
                ->where('id','>',$last_id)
                ->where(['status' => $status])
                ->with([
                    'driver:id,firstName,lastName,photo,phoneNumber,vehicleId',
                    'driver.vehicle:id,plate,vehicleBrand',
                    'user:id,firstName,lastName,photo,phoneNumber',
                    'subService:id,is_travel,' . (app()->getLocale() === 'en' ? 'name_en as name' : 'name'),
                    'office:id,officeName',
                ])
                ->select([
                    'id',
                    'startAt',
                    'endAt',
                    'amount',
                    'discount',
                    'time',
                    'totalAmount',
                    'rating',
                    'reason',
                    'couponId',
                    'status',
                    'startAddress',
                    'endAddress',
                    'startLatitude',
                    'startLongitude',
                    'endLatitude',
                    'endLongitude',
                    'distance',
                    'paymentId',
                    'durationDiff',
                    'userId',
                    'subServiceId',
                    'multiDestnationArray',
                    'officeCommissionValue',
                    'driverCommissionValue',
                    'fleetCommissionValue',
                    'driverCommissionPercentage',
                    'officeCommissionPercentage',
                    'fleetCommissionPercentage',
                    'paymentStatus',
                    'PaymentDatetime',
                    'otherPaymentTransactionDetail',
                    'officeId',
                    'driverId',
                    'created_at',
                ])
                ->orderByDesc('id')
                ->paginate(7);

            $bookings->getCollection()->transform(function ($booking) {
                $booking->multiDestnationArray = $booking->multiDestnationArray
                    ? json_decode($booking->multiDestnationArray)
                    : null;

                $booking->withOffice = $booking->officeId ? true : false;
                $booking->officeName = $booking->office?->officeName;

                $booking->driver = $booking->driver ?? null;
                if ($booking->driver) {
                    $booking->driver->vehicle = $booking->driver->vehicle ?? null;
                }

                return $booking;
            });

            return $bookings;
    }

    public function getOrdersByStatusForCards($status)
        {
            $bookings = $this->model->scopeForCurrentUser()
                ->where(['status' => $status])
                ->with([
                    'driver:id,firstName,lastName,photo,phoneNumber,vehicleId',
                    'driver.vehicle:id,plate,vehicleBrand',
                    'user:id,firstName,lastName,photo,phoneNumber',
                    'subService:id,is_travel,' . (app()->getLocale() === 'en' ? 'name_en as name' : 'name'),
                    'office:id,officeName',
                ])
                ->select([
                    'id',
                    'startAt',
                    'endAt',
                    'amount',
                    'discount',
                    'time',
                    'totalAmount',
                    'rating',
                    'reason',
                    'couponId',
                    'status',
                    'startAddress',
                    'endAddress',
                    'startLatitude',
                    'startLongitude',
                    'endLatitude',
                    'endLongitude',
                    'distance',
                    'paymentId',
                    'durationDiff',
                    'userId',
                    'subServiceId',
                    'multiDestnationArray',
                    'officeCommissionValue',
                    'driverCommissionValue',
                    'fleetCommissionValue',
                    'driverCommissionPercentage',
                    'officeCommissionPercentage',
                    'fleetCommissionPercentage',
                    'paymentStatus',
                    'PaymentDatetime',
                    'otherPaymentTransactionDetail',
                    'officeId',
                    'driverId',
                    'created_at',
                ])
                ->orderByDesc('id')
                ->paginate(7);

            $bookings->getCollection()->transform(function ($booking) {
                $booking->multiDestnationArray = $booking->multiDestnationArray
                    ? json_decode($booking->multiDestnationArray)
                    : null;

                $booking->withOffice = $booking->officeId ? true : false;
                $booking->officeName = $booking->office?->officeName;

                $booking->driver = $booking->driver ?? null;
                if ($booking->driver) {
                    $booking->driver->vehicle = $booking->driver->vehicle ?? null;
                }

                return $booking;
            });

            return $bookings;
        }





    public function getScheduledBookingsForAuthDriver(){

            //   'subService' => function ($query) {
            //      $query->select(SelectByLanguage::$subService);
            //     }
            //      ,'driver.vehicle',
            //      'payment'=> function ($query) {
            //       $query->select(SelectByLanguage::$paymentMethod);
            //     }
        return $this->model::with([
            'subService','user','payment'])
        ->where('driverId', operator: auth()->id())
        ->where('status', operator: OrderStatus::$Scheduled)
        ->where('is_scheduled', true)
        ->where('scheduled_time', '>=', now())
        ->orderBy('scheduled_time', 'asc')
        ->get();

        }


    public function getScheduledBookingsForAuthUser(){

        return $this->model::with([
             'subService' => function ($query) {
                 $query->select(SelectByLanguage::subService());
                }
                 ,'driver.vehicle',
                 'payment'=> function ($query) {
                  $query->select(SelectByLanguage::paymentMethod());
                }

            ])
        ->where('userId', auth()->id())
        ->where('status', operator: OrderStatus::$Scheduled)
        ->where('is_scheduled', true)
        ->where('scheduled_time', '>=', now())
        ->orderBy('scheduled_time', 'asc')
        ->get();
    }



    public function getAuthUserCurrentOrder(){
        $baseQuery =  $this->model::with([
             'subService' => function ($query) {
                 $query->select(SelectByLanguage::subService());
                }
                 ,'driver.vehicle',
                 'payment'=> function ($query) {
                  $query->select(SelectByLanguage::paymentMethod());
                }

            ])
        ->where('userId',  Auth::id())
            ->where('status', OrderStatus::$InProgress);

            // // ->orderBy(column: 'created_at', 'desc')
            // ->first();

            // return response()->json($baseQuery);

        $nonScheduled = (clone $baseQuery)
            ->where('is_scheduled', false)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($nonScheduled) {
            $order = $nonScheduled;
        }
            $order =  (clone $baseQuery)
            ->where('is_scheduled', true)
            ->whereNotNull('scheduled_time')
            ->Where('scheduled_time', '>=', now()->subMinutes(30))
            // ->orderBy(column: 'scheduled_time', 'asc')
            // ->orderBy(column: 'created_at', 'desc')
            ->first();

            return $order;

    }

        public function getAuthDriverCurrentOrder($onlyScheduled = false){

  $baseQuery =  $this->model::with([
             'subService' => function ($query) {
                 $query->select(SelectByLanguage::subService());
                }
                 ,'user',
                 'payment'=> function ($query) {
                  $query->select(SelectByLanguage::paymentMethod());
                }

            ])
            ->where('driverId', Auth::id())
            ->where('status', OrderStatus::$InProgress);

            // // ->orderBy(column: 'created_at', 'desc')
            // ->first();

            // return response()->json($baseQuery);

        $nonScheduled = (clone $baseQuery)
            ->where('is_scheduled', false)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($nonScheduled) {
            $order = $nonScheduled;
        }
            $order =  (clone $baseQuery)
            ->where('is_scheduled', true)
            ->whereNotNull('scheduled_time')
            ->Where('scheduled_time', '>=', now()->subMinutes(30))
            // ->orderBy(column: 'scheduled_time', 'asc')
            // ->orderBy(column: 'created_at', 'desc')
            ->first();

            return $order;
    }


    public function getScheduledCountForAuthDriver(): int {
            return $this->model->query()->where('driverId', operator: Auth::id())
                ->where('status', OrderStatus::$Scheduled)
                ->where('is_scheduled', true)
                ->where('scheduled_time', '>=', now())
                // ->orderBy(column: 'created_at', 'desc')
                ->count();
    }

    public function getOrderWithAllRelationsSkipUser($orderId){
        return $this->model::with(['subService','driver.vehicle','payment','user'])
        ->where('id', $orderId)
        ->first();
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
