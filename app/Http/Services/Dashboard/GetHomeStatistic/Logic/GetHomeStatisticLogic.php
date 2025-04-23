<?php
namespace App\Http\Services\Dashboard\GetHomeStatistic\Logic;

use App\Http\Core\Classes\RedisManagerData;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\Order\OrderRedisModel;

class GetHomeStatisticLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private GetHomeStatisticInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $data['count_total_user'] = $this->repository->UserRepository()->readRepository()->countRecords([]);
        $data['count_total_driver'] = $this->repository->UserRepository()->readRepository()->countRecords([]);
        $data['count_total_service']= $this->repository->ServiceRepository()->readRepository()->countRecords([]);
        $data['count_total_office'] = $this->repository->OfficeRepository()->readRepository()->countRecords([]);
 
        $data['system_completed_rides']     = OrderRedisModel::get_status_count(OrderStatus::$Completed);
        $data['system_ongoing_rides']       = OrderRedisModel::get_status_count(OrderStatus::$OnGoing);
        $data['system_pending_rides']       = OrderRedisModel::get_status_count(OrderStatus::$Pending);
 
        // $data['revenueData']            = $this->getMonthlyRevenue();
 
 
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
 
        return response()->json($data);
   }
}