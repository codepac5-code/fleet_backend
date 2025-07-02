<?php
namespace App\Http\Services\Dashboard\BrandManagement\ViewBrandList\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Models\VehicleBrand;
use Yajra\DataTables\Facades\DataTables;

class ViewBrandListLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ViewBrandListInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $query = VehicleBrand::query()->orderBy('updated_at','desc')->get();

        return DataTables::of($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" data-type="user" onclick="dataTableRowCheck('.$row->id.',this)">';
            })
            ->editColumn('description', function ($vbrand) {
                return $vbrand->description;//view('driver.driver', compact('driver'));
            })
    


            ->editColumn('image', function($vbrand) {
               return view('vbrand.datatable-card', compact('vbrand'));
            })
            ->editColumn('name', function($query) {
                return ($query->name != null && isset($query->name)) ? $query->name : '-';
            })

            // ->filterColumn('office',function($qry,$keyword){
            //     $qry->whereHas('office',function ($q) use($keyword){
            //         $q->where('officeName','like','%'.$keyword.'%');
            //     });
            // })
            // ->addColumn('contact_number',function($qry){
            //        return  $qry->phoneNumber;
            // })
            // ->editColumn('status', function ($query) {
            //     $disabled = $query->trashed() ? 'disabled' : '';
            //     return renderStatusSwitch($query->id, $query->isActive, 'vbrand_status', $disabled);

            // })
            ->addColumn('action', function($vbrand){
                $auth_user= authSession();
                return view('vbrand.action', compact('vbrand','auth_user'))->render();
            })
            ->addIndexColumn()
            ->rawColumns(['check','action' , 'image','description','name']) //'status' ,
            ->make(true); 
  
   }
}