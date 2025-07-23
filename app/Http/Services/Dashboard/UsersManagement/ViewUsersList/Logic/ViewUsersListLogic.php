<?php
namespace App\Http\Services\Dashboard\UsersManagement\ViewUsersList\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Models\Setting;
use App\Models\User;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ViewUsersListLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ViewUsersListInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View |RedirectResponse {



        $query = $this->repository->UserRepository()
        ->readRepository()
        ->userDataTable();
        
        
        // User::query()->orderBy('updated_at','desc')->get();
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
        ->editColumn('display_name', function ($user) {
            return view('customer.user', compact('user'));
        })
        // ->editColumn('address', function($query) {
        //     return 'adddd ';//($query->address != null && isset($query->address)) ? $query->address : '-';
        // })

        ->editColumn('block' , function ($query){
            $disabled = $query->trashed() ? 'disabled': '';
            return renderStatusSwitch($query->id , $query->status , 'user_status',$disabled);
        })
        
        // ->editColumn('created_at', function($query) {
        //     $sitesetup = $this->repository->SettingsRepository()->readRepository()
        //     ->getFirstByConditions([
        //         'type'=>'site-setup',
        //         'key'=>'site-setup'
        //     ]);
        //     $datetime = $sitesetup ? json_decode($sitesetup->value) : null;

        //     $formattedDate =  optional($datetime)->date_format && optional($datetime)->time_format
        //     ? date(optional($datetime)->date_format, strtotime($query->created_at)) . ' / ' . date(optional($datetime)->time_format, strtotime($query->created_at))
        //     : $query->created_at;
        //     return $formattedDate;
        // })

        // ->editColumn('status', function($query) {
        //     if($query->isActive){
        //         $status = '<span class="badge badge-active">'.__('messages.active').'</span>';
        //         // $status = '<a class="btn-sm text-white btn-success"  href='.route('handyman.approve',$query->id).'>Accept</a>';
        //     }
        // else{
        //         $status = '<span class="badge badge-inactive">'.__('messages.inactive').'</span>';
        //     }
        //     return $status;
        // })

        // ->editColumn('office', function($driver) {
        //     return '--';
        //     $office =$driver->office;
        // return ($office == null ) ?  '--' :   view('driver.office', compact('office'));
        // })
        // ->editColumn('address', function($query) {
        //     return ($query->address != null && isset($query->address)) ? $query->address : '-';
        // })

        // ->filterColumn('office',function($qry,$keyword){
        //     $qry->whereHas('office',function ($q) use($keyword){
        //         $q->where('officeName','like','%'.$keyword.'%');
        //     });
        // })
        ->addColumn('contact_number',function($qry){
               return  $qry->phoneNumber;
        })
        ->addColumn('action', function($user){
            $auth_user= authSession();
            return view('customer.action',compact('user','auth_user'))->render();
        })

        ->editColumn('walletBalance', function ($qry){
            $walletBalance = $qry->walletBalance;
            return view('customer.walletBalance', compact('walletBalance'));
        })
        ->editColumn('created_at', function($query) {
            $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
            $datetime = $sitesetup ? json_decode($sitesetup->value) : null;
           
            $formattedDate =  optional($datetime)->date_format && optional($datetime)->time_format
            ? date(optional($datetime)->date_format, strtotime($query->created_at)) . ' / ' . date(optional($datetime)->time_format, strtotime($query->created_at))
            : $query->created_at;
            return $formattedDate;
        })
        ->addIndexColumn()
        ->rawColumns(['check','action','status','block' ,'display_name','created_at','walletBalance'])
        // ->rawColumns(['check','action','status','created_at','contact_number'])
        ->make(true);
   }
}