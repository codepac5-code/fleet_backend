<?php
namespace App\Http\Services\Dashboard\Transactions\ViewPayments\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Models\Booking;
use App\Models\Payment;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class ViewPaymentsLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ViewPaymentsInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

    // $query = $this->repository->DriverRepository()->readRepository()->getAllRecords();
        // $filter = $request->filter;
        $query = Booking::query()->orderBy('updated_at','desc');
        // if (isset($filter)) {
        //     if (isset($filter['column_status'])) {
        //         $query->where('status', $filter['column_status']);
        //     }
        // }
        // $query = $query->where('user_type','handyman');
        // if (auth()->user()->hasAnyRole(['admin'])) {
        //     $query->withTrashed();
        // }
        // if(auth()->user()->hasRole('office')) {
        //     $query->where('officeId', auth()->user()->id);
        // }
        
        // if($request->list_status == null){
        //     $query = $query->where('status',1)->whereNotNull('provider_id');
        // }
        // if($request->list_status == 'pending'){
        //     $query = $query->where('status',0);
        // }
        // if($request->list_status == 'unassigned'){
        //     $query = $query->where('status',1)->where('provider_id',NULL)->where('user_type','handyman');
        // }
        // if ($request->list_status == 'request') {
        //     $query = $query->where(function($query) {
        //         $query->where('status', 0)
        //               ->where(function($query) {
        //                   $query->whereNull('provider_id')
        //                         ->orWhereNotNull('provider_id');
        //               })
        //               ->where('user_type', 'handyman');
        //     });
        // }

        return DataTables::of($query)



            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" data-type="user" onclick="dataTableRowCheck('.$row->id.',this)">';
            })

            ->editColumn('bookingId', function ($query) {
                return $query->id ?? '--';
            })
            ->editColumn('payment_methode', function ($query) {
                return $query->payment->name?? '--';
            })
            ->editColumn('payment_status', function ($query) {

                $payment = $query->paymentStatus;
                if ($payment !== null) {
                    $formatted_payment = Str::title(str_replace('_', ' ', $payment)); 
                    $payment_status = '<span class="text-center badge badge-primary1">'.$formatted_payment.'</span>';
                } else {
                    $payment_status = '<span class="text-center d-block">-</span>';
                }
                return $payment_status;
                
            })
            ->editColumn('amount', function ($query) {
                return getPriceFormat($query->amount);

            })
            ->editColumn('discount', function ($query) {
                if($query->isPercentage){
                    return $query->discount.'%'?? '--';
                }
                return $query->discount ?? '--';
            })
            ->editColumn('totalAmount', function ($query) {
                return getPriceFormat($query->totalAmount);
            })
            ->editColumn('Payment_datetime', function ($query) {
              
              if($query->PaymentDatetime == null){
                    return 'not paid';
                }
                return dateTime_sitesetup($query->PaymentDatetime);
            })
            ->editColumn('commission', function ($query) {
                  return $query->rideCommission;
              })
  

                      //bookingId
        // payment_methode
        //payment_status
        //amount
        //discount
        //totalAmount
        //Payment_datetime
        //commission
        //action
        // created_at
            ->addIndexColumn()
            ->rawColumns(['check','Payment_datetime','action','payment_status','amount','commission','discount','bookingId' ])
            ->make(true); 
   }
}