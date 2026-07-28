<?php
namespace App\Http\Services\Dashboard\DriverManagement\ViewDriversList\Logic;
use App\Models\Driver;
use App\Models\Setting;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Models\OfficeDriverCustomCommission;
use Illuminate\Support\Facades\Auth;

class ViewDriversListLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ViewDriversListInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse {

        // $query = $this->repository->DriverRepository()->readRepository()->getAllRecords();
        // $filter = $request->filter;

        // if(auth()->user)
        $query = $this->repository->DriverRepository()->readRepository()
        ->dataTableDriver( $this->input->getFilter());



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
            ->editColumn('firstName', function ($driver) {
                return view('driver.driver', compact('driver'));
            })
            ->editColumn('address', function($query) {
                return 'adddd ';//($query->address != null && isset($query->address)) ? $query->address : '-';
            })


            ->editColumn('created_at', function($query) {
                $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
                $datetime = $sitesetup ? json_decode($sitesetup->value) : null;

                $formattedDate =  optional($datetime)->date_format && optional($datetime)->time_format
                ? date(optional($datetime)->date_format, strtotime($query->created_at)) . ' / ' . date(optional($datetime)->time_format, strtotime($query->created_at))
                : $query->created_at;
                return $formattedDate;
            })

            ->editColumn('is_online', function($query) {
                if($query->is_online){
                    $status = '<span class="badge badge-active">'.__('messages.connected').'</span>';
                    // $status = '<a class="btn-sm text-white btn-success"  href='.route('handyman.approve',$query->id).'>Accept</a>';
                }
            else{
                    $status = '<span class="badge badge-inactive">'.__('messages.disconnected').'</span>';
                }
                return $status;
            })

            ->editColumn('office', function($driver) {
           $office =  null;//$driver->office;
            return ($office == null ) ?  '--' :   view('driver.office', compact('office'));
            })
            // ->editColumn('address', function($query) {
            //     return ($query->address != null && isset($query->address)) ? $query->address : '-';
            // })

            // ->filterColumn('office',function($qry,$keyword){
            //     $qry->whereHas('office',function ($q) use($keyword){
            //         $q->where('officeName','like','%'.$keyword.'%');
            //     });
            // })
            ->addColumn('phoneNumber',function($qry){
                   return  $qry->phoneNumber;
            })

            ->editColumn('walletBalance', function ($qry){
                $walletBalance = $qry->walletBalance;
                return view('customer.walletBalance', compact('walletBalance'));
            })
            ->editColumn('dues', function ($qry){
                $dues = $qry->fleetDues + $qry->officeDues;
                return view('driver.dues', compact('dues'));
            })

            ->addColumn('action', function($driver){
                $auth_user= authSession();
                $isOffice = false;
                $driver->isFleetCommissionCustom ? $isCustom= 'yes':$isCustom = 'no';
                $officeCommission = 0;
                $driverCommission = 0;

                if( (Auth::guard('office')->check())){
                    $isOffice = true;
                    $driver->isOfficeCommissionCustom  ? $isCustom= 'yes':$isCustom = 'no';
                    if($isCustom == 'yes'){
                        $commission = OfficeDriverCustomCommission::where(['officeId'=>Auth::user()->id , 'driverId'=>$driver->id])->first();
                        $officeCommission = $commission->officeCommission;
                        $driverCommission = $commission->driverCommission;
                     }
                }
                else if (Auth::guard('employee')->check()) {
                    $employee = Auth::guard('employee')->user();
                    if ($employee->officeId) {
                        $isOffice = true;
                        $driver->isOfficeCommissionCustom? $isCustom= 'yes':$isCustom = 'no';
                        if($isCustom == 'yes'){
                           $commission = OfficeDriverCustomCommission::where(['officeId'=>$employee->officeId , 'driverId'=>$driver->id])->first();
                           $officeCommission = $commission->officeCommission;
                           $driverCommission = $commission->driverCommission;
                        }
                    }
                }
                return view('driver.action',compact('driver','auth_user' ,'isOffice','isCustom' ,'officeCommission','driverCommission'))->render();
            })
            ->addIndexColumn()
            ->rawColumns(['check','display_name','action','is_online','created_at','contact_number','office','walletBalance' ])
            ->make(true);
        }
}
