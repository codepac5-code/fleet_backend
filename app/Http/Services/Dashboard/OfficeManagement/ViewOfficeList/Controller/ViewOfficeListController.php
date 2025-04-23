<?php
namespace App\Http\Services\Dashboard\OfficeManagement\ViewOfficeList\Controller;

use App\Models\Office;
use App\Models\Setting;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\OfficeManagement\ViewOfficeList\Logic\ViewOfficeListInput;
use App\Http\Services\Dashboard\OfficeManagement\ViewOfficeList\Logic\ViewOfficeListLogic;
use App\Http\Services\Dashboard\OfficeManagement\ViewOfficeList\Request\ViewOfficeListRequest;

class ViewOfficeListController extends Controller
{
    public function __invoke(ViewOfficeListRequest $request)
    {


         // old
         $query = Office::query()->orderBy('updated_at','desc');

         //  $query = Office::paginate(10);
         // end
         // jabu depot neo provider list
            // $query = User::query()->orderBy('id', 'desc');

         // end jabu
         $filter = $request->filter;

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

        // validate input data and pass it to the service..
        $input = new ViewOfficeListInput($request->validated());

        $service = new ViewOfficeListLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
