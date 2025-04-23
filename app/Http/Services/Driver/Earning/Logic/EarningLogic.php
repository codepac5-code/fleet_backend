<?php
namespace App\Http\Services\Driver\Earning\Logic;

use App\Http\Core\Const\Messages\SuccessMessages;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Illuminate\Support\Facades\DB;

class EarningLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private EarningInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }

    public function execute (): ResponseModel {


        $driver = getAuthUser();
    
        $report = $this->repository->BookingRepository()->readRepository()->getEarning(
            [$this->input->startDate, $this->input->endDate],
            [
                'id', 
                'amount', 
                'distance', 
                'driverCommissionValue      as myEarning', 
                // 'driverCommissionPercentage as CommissionPercentage',
                'fleetCommissionValue       as fleetCommission',
                'officeCommissionValue      as officeCommoission',
                // DB::raw('fleetCommissionValue + officeCommissionValue AS officeCommission'),
                // 'fleetCommissionValue + officeCommissionValue AS officeCommission'
            ],
            conditions: [
                'driverId' => $driver->id,
                'status' => OrderStatus::$Completed
            ]
        );
    
        $response = new EarningOutput([
            'orders'                => $report['records'],  
            'totalCount'            => $report['summary']->total_orders ?? 0,
            'totalKm'               => $report['summary']->total_km ?? 0,
            'totalEranging'         => (int) $report['summary']->total_earning ?? 0,
            'officeDues'            => $driver->officeDues ?? 0,
        ], SuccessMessages::getKey(SuccessMessages::$AccountCreated));
    
        return $response->send_as_object();

        $driver = $this->repository->DriverRepository()->readRepository()->find($this->input->getDrivereId());

        $report = $this->repository->BookingRepository()->readRepository()->getEarnaing(
            [
                $this->input->startDate,
                $this->input->endDate
            ],
            conditions:[
                'driverId' => $driver->id,
                'status' => OrderStatus::$Completed
            ],
            selected:[
                'id' , 'amount', 'distance' , 'driverCommissionPercentage as ride-commission' , 'driverCommissionValue'
            ]
        );


        // $totalEranging = 0;
        // $totalKm = 0;
        // $totalCount = 0;
        // foreach ($report as  $value) {
        //     $eraning = $value['driverCommissionValue'];
        //     $totalEranging += $eraning;
        //     $totalKm+=$value['distance'];
        //     $totalCount++;
        //     $officCommisson = $value['fleetCommissionValue'] + $value['officeCommissionValue'];
        //     $value['myEarning'] = $eraning;
        // }

        // $response  = new EarningOutput([
        //     'orders' => $report,
        //     'totalEranging' => $totalEranging,
        //     'totalKm' => $totalKm,
        //     'totalCount' => $totalCount,
        //     'officeCommission'=>$officCommisson,
        // ] , SuccessMessages::getKey(SuccessMessages::$AccountCreated));
        
        // return $response->send_as_object();
   }
}




// 'id' , 'amount', 'distance' , 'driverCommissionPercentage as ride-commission' ,
                
// 'driverCommissionValue' ,
//  'officeCommissionValue',
//  'officeCommissionPercentage',
//  'totalKm',
//  'totalCount',








// public function getEarning(array $dateRange, array $conditions) {
//     $summary = \DB::table('bookings')
//         ->selectRaw("
//             COUNT(id) AS total_orders, 
//             SUM(distance) AS total_km, 
//             SUM(driverCommissionValue) AS total_earning, 
//             SUM(fleetCommissionValue + officeCommissionValue) AS total_office_commission
//         ")
//         ->where('driverId', $conditions['driverId'])
//         ->where('status', $conditions['status'])
//         ->whereBetween('created_at', $dateRange)
//         ->first();

//     $records = \DB::table('bookings')
//         ->select([
//             'id', 
//             'amount', 
//             'distance', 
//             'driverCommissionPercentage as ride_commission', 
//             'driverCommissionValue',
//             'fleetCommissionValue',
//             'officeCommissionValue'
//         ])
//         ->where('driverId', $conditions['driverId'])
//         ->where('status', $conditions['status'])
//         ->whereBetween('created_at', $dateRange)
//         ->get();

//     return [
//         'summary' => $summary,
//         'records' => $records
//     ];
// }
