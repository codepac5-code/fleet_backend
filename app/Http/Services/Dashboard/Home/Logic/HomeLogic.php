<?php
namespace App\Http\Services\Dashboard\Home\Logic;
use App\Http\Core\Classes\RedisManagerData;
use Illuminate\Contracts\View\View;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Models\Booking;
use App\Models\Office;
use Carbon\Carbon;
use App\Models\User;

class HomeLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private HomeInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | View {

       $data['dashboard']['count_total_user'] = $this->repository->UserRepository()->readRepository()->countRecords([]);
       $data['dashboard']['count_total_driver'] = $this->repository->UserRepository()->readRepository()->countRecords([]);
       $data['dashboard']['count_total_service']= $this->repository->ServiceRepository()->readRepository()->countRecords([]);
       $data['dashboard']['count_total_office'] = $this->repository->OfficeRepository()->readRepository()->countRecords([]);

       $data['system_completed_rides'] = RedisManagerData::get_system_daily_completed_rides();
       $data['system_ongoing_rides']   = RedisManagerData::get_system_daily_ongoing_rides();
       $data['system_pending_rides']   = RedisManagerData::get_system_daily_pending_rides();

       $data['revenueData']            = $this->getMonthlyRevenue();


       //----------------------

       $system_statistic = $this->repository->FleetStatisticRepository()->readRepository()->getFirstByConditions([]);


       $data['withdrawn-amount']  =  $system_statistic->withdrawn_amount;
       $data['available-amount']  =  $system_statistic->available_amount;
       //---------------------------------<<    >>-------------------------------||
       $data['pending-amount']    =  RedisManagerData::get_system_pending_amount();
       $data['total-amount']      =  $system_statistic->total_income;
       //-----


       $data['offices-due-amount']=  $system_statistic->offices_debt;
       $data['drivers-due-amount']=  $system_statistic->drivers_debt;

       return $this->adminDashboard($data);
    }

    public function adminDashboard($data) : View {


        $offices = Office::where('created_at', '>=', Carbon::now()->subDays(2))->limit(7)->get();

        $users = User::where('created_at', '>=', Carbon::now()->subDays(2))->limit(7)->get();
        // $orders = Booking::where('created_at', '>=', Carbon::now()->subDays(2))->get();

        $orders = Booking::where('created_at', '>=', Carbon::now()->subDays(2))->limit(7)->get();

        $auth_user = authSession();
        $isOffice  = Office::find(1);

    //  $auth_user->assignRole('office');
        // dd(auth()->user()->hasAnyRole(['office']));

    
        return view('dashboard.dashboard', compact(
            'data',
            'offices',  
            'users',
            'orders',
            'auth_user',
            'isOffice'
            ));
    }

    public function getMonthlyRevenue()
    {
        $user = auth()->user();
        $query = $monthlyRevenue = Booking::selectRaw('MONTH(created_at) as month, SUM(totalAmount) as total');
        if($user->hasAnyRole(['office'])){
            $query->where(['officeId'=>$user->id]);
        }
   
    $query->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();
        


       $formattedData = array_fill(1, 12, 0);
       foreach ($monthlyRevenue as $month => $revenue) {
           $formattedData[$month] = $revenue;
       }
       return  array_values($formattedData);     
    }
}
