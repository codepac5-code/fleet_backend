<?php
namespace App\Http\Services\Dashboard\DriverManagement\GetOrderHistory\Logic;

use App\Http\Core\Const\Messages\SuccessMessages;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Services\Driver\Earning\Logic\EarningOutput;
use Yajra\DataTables\Facades\DataTables;

class GetOrderHistoryLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private GetOrderHistoryInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

            $driverId = $this->input->getDrivereId();
        
            // جلب بيانات السائق (مطلوب لعرض officeDues)
            $driver = $this->repository->DriverRepository()->readRepository()->find($driverId);
        
            // جلب تقرير الأرباح مع الشروط والأعمدة المطلوبة
            $report = $this->repository->BookingRepository()->readRepository()->getEarning(
                [$this->input->startDate, $this->input->endDate],
                [
                    'id',
                    'amount',
                    'distance',
                    'driverCommissionValue as myEarning',
                    'fleetCommissionValue as fleetCommission',
                    'officeCommissionValue as officeCommoission',
                ],
                conditions: [
                    'driverId' => $driverId,
                    'status'   => OrderStatus::$Completed,
                ]
            );
        
            // بناء DataTable من الـ records (المعاملات)
            $dataTable = DataTables::of($report['records'])
                ->editColumn('amount', function ($row) {
                    return number_format($row->amount, 2) . ' ' . config('app.currency', '$');
                })
                ->editColumn('myEarning', function ($row) {
                    return number_format($row->myEarning, 2) . ' ' . config('app.currency', '$');
                })
                ->editColumn('fleetCommission', function ($row) {
                    return number_format($row->fleetCommission, 2) . ' ' . config('app.currency', '$');
                })
                ->editColumn('officeCommoission', function ($row) {
                    return number_format($row->officeCommoission, 2) . ' ' . config('app.currency', '$');
                })
                ->editColumn('distance', function ($row) {
                    return number_format($row->distance, 1) . ' km';
                })
                ->toArray();
        
            // إعداد الرد الكامل مع بيانات الجدول وباقي الملخصات
            $responseData = [
                'orders'        => $dataTable['data'], // بيانات الداتاتيبل فقط
                'totalCount'    => $report['summary']->total_orders ?? 0,
                'totalKm'       => $report['summary']->total_km ?? 0,
                'totalEarning'  => (int) ($report['summary']->total_earning ?? 0),
                'officeDues'    => $driver->officeDues ?? 0,
                'success'       => true,
                'message'       => __('messages.success'),
            ];
        
            // يمكنك إرجاع الرد كـ JSON عادي أو استخدام الـ EarningOutput لو تفضّل
            // return new EarningOutput($responseData, SuccessMessages::getKey(SuccessMessages::$AccountCreated));
            return response()->json($responseData);
        
   }
}