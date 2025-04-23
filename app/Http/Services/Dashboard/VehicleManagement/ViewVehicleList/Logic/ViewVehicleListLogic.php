<?php
namespace App\Http\Services\Dashboard\VehicleManagement\ViewVehicleList\Logic;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Models\Vehicle;

class ViewVehicleListLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ViewVehicleListInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel |  JsonResponse{



        // $query = Vehicle::query()->orderBy('updated_at','desc')->get();

        $query = $this->repository->VehicleRepository()->readRepository()
        ->dataTableVehicle( ); 


        
        //$this->repository->VehicleRepository()
        // ->readRepository()->getAllRecords(); 

        //$query = Booking::all();

        // if ($this->input->getFilter() != null) {
        //     $filter = $this->input->getFilter();
        //     if (isset($filter['column_status'])) {
        //         $query = $this->repository->VehicleRepository()
        //         ->readRepository()->getByConditions(['status'=>$filter['column_status']]);
        //      }
        // }

        // return response()->json(['mm'=>'dd']);
        // else{
        //     $query = $this->repository->BookingRepository()
        //     ->readRepository()->getAllRecords(); 
        // }

        // if (auth()->user()->hasAnyRole(['admin'])) {
        //     $query->withTrashed();
        // }


        // 'officeId', 
        // 'vehicleBrand', 
        // 'plate', 
        // 'modelYear', 
        // 'licenseNumber', 
        // 'model', 
        // 'color', 
        // 'driverId', 
        // 'city',
 
        return DataTables::of($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" data-type="booking" onclick="dataTableRowCheck('.$row->id.',this)">';
            })
            ->editColumn('plate' , function ($query){
                return $query->plate;
            })
            ->editColumn('model' , function ($query){
                return $query->model;
            })
            ->editColumn('brand' , function ($query){
                return $query->vehicleBrand;
            })
            ->editColumn('seats' , function ($query){
                return $query->seatsCount;
            })
            ->editColumn('color' , function ($query){
                return $query->color;
            })
            ->editColumn('city' , function ($query){
                return $query->city;
            })
            ->editColumn('model_year' , function ($query){
                return $query->modelYear;
            })
            ->editColumn('licenseNumber' , function ($query){
                return $query->licenseNumber;
            })
            ->editColumn('driver' , function ($query){
                $driver = $query->driver;
                if($driver != null) { return view('driver.driver', compact('driver'));}
                return '--';

            })
            ->addColumn('image', function ($vehicle) {
                $image = $vehicle->photo ;
                return view('vehicle.datatable-card', compact('image'));
            })
            // ->editColumn('status' , function ($query){
            //     return '---';//bookingstatus($query->status);
            // })
            // ->editColumn('id' , function ($query){
            //     return "<a class='btn-link btn-link-hover' href=" .route('booking.show', $query->id).">#".$query->id ."</a>";
            // })
            // ->editColumn('user' , function ($query){
            //     $user = $query->user;
            //     // return $user->id;
            //     return view('booking.customer', compact('user'));
            // })
            // ->filterColumn('userId',function($query,$keyword){
            //     $query->whereHas('userId',function ($q) use($keyword){
            //         $q->where('firstName','like','%'.$keyword.'%');
            //     });
            // })
            // ->editColumn('serviceId' , function ($query){
            //     $service_name = ($query->service_id != null && isset($query->service)) ? $query->service->name : "";
            //     return "<a class='btn-link btn-link-hover' href=" .route('booking.show', $query->id).">".$service_name ."</a>";
            // })
            // ->editColumn('details' , function ($query){
            //     return "<a class='btn-link btn-link-hover' href=" .route('booking.show', $query->id).">".__('messages.details') ."</a>";
            // })
            // ->filterColumn('service_id',function($query,$keyword){
            //     $query->whereHas('service',function ($q) use($keyword){
            //         $q->where('name','like','%'.$keyword.'%');
            //     });
            // })
            // ->editColumn('date' , function ($query){
            //     // $this->repository->
            //     $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
            //     $datetime = $sitesetup ? json_decode($sitesetup->value) : null;

            //     $date = optional($datetime)->date_format && optional($datetime)->time_format
            //     ? date(optional($datetime)->date_format, strtotime($query->date)) . ' / ' . date(optional($datetime)->time_format, strtotime($query->date))
            //     : $query->date;

            //     return $date;
            // })

            // ->filterColumn('provider_id',function($query,$keyword){
            //     $query->whereHas('provider',function ($q) use($keyword){
            //         $q->where('display_name','like','%'.$keyword.'%');
            //     });
            // })

            // ->editColumn('payment_id' , function ($query){
            //     $payment_status = optional($query->payment)->payment_status;
            //     if($payment_status !== null){
            //         $status = '<span class="text-center badge badge-primary1">'.str_replace('_'," ",ucfirst($payment_status)).'</span>';
            //     }else{
            //         $status = '<span class="badge badge-pay-pending">'.__('messages.pending').'</span>';
            //     }
            //     return  $status;
            // })
            // ->filterColumn('payment_id',function($query,$keyword){
            //     $query->whereHas('payment',function ($q) use($keyword){
            //         $q->where('payment_status','like',$keyword.'%');
            //     });
            // })


            ->addColumn('action', function($data){
                return view('vehicle.action',compact('data'))->render();
            })

            ->editColumn('updated_at', function ($query) {
                $diff = Carbon::now()->diffInHours($query->updated_at);
                if ($diff < 25) {
                    return $query->updated_at->diffForHumans();
                } else {
                    return $query->updated_at->isoFormat('llll');
                }
            })
            ->addIndexColumn()
            ->rawColumns(['check' ,'id' ,'office','driver' ,'status','action'])
            ->toJson();
     
    }

   
}