<?php
namespace App\Http\Services\Dashboard\RatingManagement\DriverRattingIndexData\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Models\Setting;
use Yajra\DataTables\DataTables;

class DriverRattingIndexDataLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private DriverRattingIndexDataInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        // $this->input->getFilter()
        $query = $this->repository->RatingRepository()->readRepository()
        ->dataTableDriverRatings(); 
        //  response()->json(RatingUser::all()) ;

        return DataTables::of($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" data-type="user" onclick="dataTableRowCheck('.$row->id.',this)">';
            })
            ->editColumn('user', function ($rating) {
                $driver = $rating->rater;
                return view('driver.driver', compact('driver'));
            })
            ->editColumn('driver', function ($rating) {

                $query = $rating->ratedPerson;
                return view('office.office', compact('query'));

                return view('driver.driver', compact('driver'));
            })
            ->editColumn('description', function($query) {
                return $query->description;//($query->address != null && isset($query->address)) ? $query->address : '-';
            })
            ->editColumn('rating', function($query) {
                return $query->rating;//($query->address != null && isset($query->address)) ? $query->address : '-';
            })
            ->editColumn('created_at', function($query) {
                $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
                $datetime = $sitesetup ? json_decode($sitesetup->value) : null;
               
                $formattedDate =  optional($datetime)->date_format && optional($datetime)->time_format
                ? date(optional($datetime)->date_format, strtotime($query->created_at)) . ' / ' . date(optional($datetime)->time_format, strtotime($query->created_at))
                : $query->created_at;
                return $formattedDate;
            })
            ->addColumn('action', function($driver){
                $auth_user= authSession();
                return view('driver.action',compact('driver','auth_user'))->render();
            })
             ->addIndexColumn()
            ->rawColumns(['check','action','created_at','user'])
            ->make(true); 
        }

}
