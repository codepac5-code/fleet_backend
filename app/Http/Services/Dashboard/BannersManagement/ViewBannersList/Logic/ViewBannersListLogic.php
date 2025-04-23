<?php
namespace App\Http\Services\Dashboard\BannersManagement\ViewBannersList\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Models\Slider;
use Yajra\DataTables\DataTables;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;


class ViewBannersListLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ViewBannersListInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View {

        $query = Slider::query()->orderBy('updated_at','desc')->get();
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
            ->editColumn('description', function ($banner) {
                return $banner->description;//view('driver.driver', compact('driver'));
            })
            // ->editColumn('address', function($query) {
            //     return 'adddd ';//($query->address != null && isset($query->address)) ? $query->address : '-';
            // })

            
//             ->editColumn('created_at', function($query) {
//                 $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
//                 $datetime = $sitesetup ? json_decode($sitesetup->value) : null;
               
//                 $formattedDate =  optional($datetime)->date_format && optional($datetime)->time_format
//                 ? date(optional($datetime)->date_format, strtotime($query->created_at)) . ' / ' . date(optional($datetime)->time_format, strtotime($query->created_at))
//                 : $query->created_at;
//                 return $formattedDate;
//             })



            ->editColumn('image', function($banner) {
               return view('banner.datatable-card', compact('banner'));
            })
            ->editColumn('title', function($query) {
                return ($query->title != null && isset($query->title)) ? $query->title : '-';
            })

            // ->filterColumn('office',function($qry,$keyword){
            //     $qry->whereHas('office',function ($q) use($keyword){
            //         $q->where('officeName','like','%'.$keyword.'%');
            //     });
            // })
            // ->addColumn('contact_number',function($qry){
            //        return  $qry->phoneNumber;
            // })
            ->editColumn('status', function ($query) {
                $disabled = $query->trashed() ? 'disabled' : '';
                return renderStatusSwitch($query->id, $query->isActive, 'banner_status', $disabled);

            })
            ->addColumn('action', function($banner){
                $auth_user= authSession();
                return view('banner.action', compact('banner','auth_user'))->render();
            })
            ->addIndexColumn()
            ->rawColumns(['check','action' , 'image','description','status' ,'title'])
            ->make(true); 

   }
}