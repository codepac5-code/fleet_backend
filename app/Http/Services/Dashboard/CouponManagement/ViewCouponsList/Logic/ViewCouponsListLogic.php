<?php
namespace App\Http\Services\Dashboard\CouponManagement\ViewCouponsList\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Models\Coupon;
use App\Models\Setting;
use Dflydev\DotAccessData\Data;
use Yajra\DataTables\Contracts\DataTable;
use Yajra\DataTables\DataTables;

class ViewCouponsListLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ViewCouponsListInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {



        // 'code', 'discountType', 'discount', 'expireDate', 'status',
        // 'limit','isActive','isPercentage'

      $query = Coupon::query()->list();
        // $filter = $request->filter;

        // if (isset($filter)) {
        //     if (isset($filter['column_status'])) {
        //         $query->where('status', $filter['column_status']);
        //     }
        // }
        // if (auth()->user()->hasAnyRole(['admin'])) {
        //     $query->withTrashed();
        // }
        
        return DataTables::of($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" data-type="coupon" onclick="dataTableRowCheck('.$row->id.',this)">';
            })
          

            ->editColumn('code', function($query){                
                // if (auth()->user()->can('coupon edit')) {
                    //   $link = '<a class="btn-link btn-link-hover" href='.route('coupon.create', ['id' => $query->id]).'>'.$query->code.'</a>';
                // }  else {
                   $link = $query->code; 
                // }
                return $link;
            })
           
            ->editColumn('status' , function ($query){
                $disabled = $query->trashed() ? 'disabled': '';
                return '<div class="custom-control custom-switch custom-switch-text custom-switch-color custom-control-inline">
                    <div class="custom-switch-inner">
                        <input type="checkbox" class="custom-control-input bg-primary change_status" '.$disabled.' data-type="coupon_status" '.($query->isActive ? "checked" : "").' value="'.$query->id.'" id="'.$query->id.'" data-id="'.$query->id.'">
                        <label class="custom-control-label" for="'.$query->id.'" data-on-label="" data-off-label=""></label>
                    </div>
                </div>';
            })
            ->editColumn('discount' , function ($query){
                $discount = getPriceFormat($query->discount);
                if($query->discountType == 'percentage'){
                    $discount = $query->discount .'%';
                }
                return $discount;
            })
            ->editColumn('discount_type' , function ($query){
                return $query->discountType;
            })
            ->editColumn('limit' , function ($query){
                return $query->limit;
            })            
            ->editColumn('expire_date' , function ($query){
                $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
                $datetime = json_decode($sitesetup->value);
                $date = date("$datetime->date_format / $datetime->time_format", strtotime($query->expireDate));
                return $date;
            })
            ->addColumn('action', function($coupon){
                return view('coupon.action',compact('coupon'))->render();
            })
            ->addIndexColumn()
            ->rawColumns(['check','code','action','status','expire_date'])
            ->make(true);
   }
}