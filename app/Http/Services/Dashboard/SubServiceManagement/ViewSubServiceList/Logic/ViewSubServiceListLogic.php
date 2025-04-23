<?php
namespace App\Http\Services\Dashboard\SubServiceManagement\ViewSubServiceList\Logic;
use Yajra\DataTables\DataTables;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class ViewSubServiceListLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ViewSubServiceListInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){

        $this->repository = new RepositoryCaller();
    }


    public function execute (): ResponseModel {

        $read_repo = $this->repository
        ->SubServiceRepository()->readRepository();
        $sub_services = $read_repo->get_sub_services_list( $this->input->getFilter()); 

         //auth()->user()->hasAnyRole(['admin'])); 


        $dataTable =  DataTables::of($sub_services)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" data-type="subcategory" onclick="dataTableRowCheck('.$row->id.', this)">';
            })

            ->editColumn('name', function($query){                
                // if (auth()->user()->can('subcategory edit')) {
                    $link = '<a class="btn-link btn-link-hover" href='.route('sub-service.create', ['id' => $query->id]).'>'.$query->name.'</a>';
                // } else {
                //     $link = $query->name; 
                // }
                return $link;
            })
            ->editColumn('serviceId' , function ($query){
                $link = '<a class="btn-link btn-link-hover" href='.route('service.create', ['id' => $query->id]).'>'.( optional($query->service)->title ?? '-').'</a>';

                return   $link ;
            })
            // ->filterColumn('serviceId',function($query,$keyword){
            //     $query->whereHas('service',function ($q) use($keyword){
            //         $q->where('name','like','%'.$keyword.'%');
            //     });
            // })

            ->addColumn('kmPrice', function ($query) {
                return $query->kmPrice;
            })
            
            ->addColumn('minutePrice', function ($query) {
                return $query->minutePrice;
            })
            ->addColumn('openPrice', function ($query) {
                return $query->openPrice;
            })
            ->editColumn('status' , function ($query){
                $disabled = $query->trashed() ? 'disabled': '';
                return renderStatusSwitch($query->id , $query->status , 'subcategory_status',$disabled);
            })
            ->addColumn('action', function ($subservice) {
                return view('sub-service.action', compact('subservice'));
            })
            ->addColumn('image', function ($subservice) {
                return view('sub-service.datatable-card', compact('subservice'));
            })

            ->rawColumns(['action', 'status', 'check','name' , 'serviceId','kmPrice','minutePrice','openPrice', 'image'])
            ->toJson();

        $response  = new ViewSubServiceListOutput( $dataTable , 'get sub-service list' );
        return $response->send_as_object();
   }
}