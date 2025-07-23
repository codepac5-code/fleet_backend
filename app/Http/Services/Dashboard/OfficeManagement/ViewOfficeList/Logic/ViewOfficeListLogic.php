<?php
namespace App\Http\Services\Dashboard\OfficeManagement\ViewOfficeList\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

class ViewOfficeListLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct (
    //---------------------------------------------------------------------------------------
    private ViewOfficeListInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse {


         $query = $this->repository->OfficeRepository()
         ->readRepository()->officeDataTable();

         $filter = $this->input->getFilter();

         if (isset($filter)) {
             if (isset($filter['column_status'])) {
                 $query->where('status', $filter['column_status']);
             }
         }


        //  if (auth()->user()->hasAnyRole(['admin'])) {
         //    $query->withTrashed();
        //  }
         // old

        //  if($request->list_status == 'pending'){
        //      $query = $query->where('status',0);
        //  }else{
        //      $query = $query->where('status',1);
        //  }


        //  if($request->list_status == 'subscribe'){
        //      $query = $query->where('status',1)->where('is_subscribe',1);
        //  }

         //jabu
        //  if(auth()->user()->hasAnyRole(['Neopreneur'])){
        //      $query = $query->where('user_type','provider')->where('sp_neo_id', auth()->user()->id);

        //  }
         // else{
         //     if($request->list_status == 'pending'){
         //         $query = $query->where('status',0);
         //     }else{
         //         $query = $query->where('status',1);
         //     }
         //     if($request->list_status == 'subscribe'){
         //         $query = $query->where('status',1)->where('is_subscribe',1);
         //     }
         // }
         //end jabu
         
         return DataTables::of($query)
             ->addColumn('check', function ($row) {
                 return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" data-type="user" onclick="dataTableRowCheck('.$row->id.',this)">';
             })

             ->editColumn('display_name', function ($query) {
                 return view('office.office', compact('query'));
             })
             ->editColumn('wallet', function ($query){
                $wallet = $query->walletBalance;
                 return view('office.wallet', compact('wallet'));
             })
             ->editColumn('status', function($query) {
                 if($query->status == '0'){
                     $status = '<a class="btn-sm text-white btn-success"  href='.route('office.approve',$query->id).'><i class="fa fa-check"></i>Approve</a>';
                 }else{
                     $status = '<span class="badge badge-active">'.__('messages.active').'</span>';
                 }
                 return $status;
             })
            //  ->editColumn('providertype_id', function($query) {
            //      return ($query->id != null && isset($query->providertype)) ? $query->providertype->name : '-';
            //  })
            //  ->editColumn('address', function($query) {
            //      return ($query->address != null && isset($query->address)) ? $query->address : '-';
            //  })
             ->editColumn('created_at', function($query) {
                 $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
                 $datetime = $sitesetup ? json_decode($sitesetup->value) : null;

                 $formattedDate =  optional($datetime)->date_format && optional($datetime)->time_format
                 ? date(optional($datetime)->date_format, strtotime($query->created_at)) . ' / ' . date(optional($datetime)->time_format, strtotime($query->created_at))
                 : $query->created_at;
                 return $formattedDate;
             })

            //  ->filterColumn('providertype_id',function($query,$keyword){
            //      $query->whereHas('providertype',function ($q) use($keyword){
            //          $q->where('name','like','%'.$keyword.'%');
            //      });
            //  })
             ->addColumn('action', function($office){
                 return view('office.action',compact('office'))->render();
             })
             ->editColumn('contactNumber', function($office){
                return $office->contactNumber;
            })
           //  ->addIndexColumn()
             ->rawColumns(['check','display_name','contactNumber','wallet','action','status'])
             ->toJson();

      
   }
}