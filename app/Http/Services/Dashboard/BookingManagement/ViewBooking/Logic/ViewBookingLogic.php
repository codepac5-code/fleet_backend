<?php
namespace App\Http\Services\Dashboard\BookingManagement\ViewBooking\Logic;
use App\Models\Booking;
use App\Models\Setting;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class ViewBookingLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ViewBookingInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse {



        switch($this->input->getPageType()){

            case'completed':
                $query = $this->repository->BookingRepository()
                ->readRepository()->getCompletedOrders($this->input->getOfficeId());
                return $this->completed_dataTable($query);
                break;

            case'ongoing':
                $query = $this->repository->BookingRepository()
                ->readRepository()->getOngoingOrders($this->input->getOfficeId());
                return $this->ongoing_dataTable($query);
                break;

                // case'pending':
             default:
                $query = $this->repository->BookingRepository()
                ->readRepository()->getPendingOrders();
                return $this->pending_dataTable($query);
                break;

        }


        

        
        // $query = Booking::query();

        // if ($this->input->getFilter() != null) {
        //     $filter = $this->input->getFilter();
        //     if (isset($filter['column_status'])) {
        //         $query->where('status', $filter['column_status']);
        //     }
        // }

        // if (auth()->user()->hasAnyRole(['admin'])) {
        //     $query->withTrashed();
        // }



   }



//    'startAt',
//    'endAt',
//    'amount',
//    'discount',
//    'totalAmount',
//    'description',
//    'couponId',
//    'status',
//    'startAddress',
//    'startLatitude',
//    'startLongitude',
//    'endAddress',
//    'endLatitude',
//    'endLongitude',
//    'distance',
//    'paymentId',
//    'durationDiff',
//    'officeId',
//    'driverId',
//    'userId',
//    'subServiceId',
//    'multiDestnationArray',
//    'time',
//    'ride-commission'







   public function completed_dataTable ($query) : JsonResponse{
    return DataTables::of($query)
    ->addColumn('check', function ($row) {
        return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" data-type="booking" onclick="dataTableRowCheck('.$row->id.',this)">';
    })
    // ->editColumn('id' , function ($query){
    //     return "<a class='btn-link btn-link-hover' href=" .route('booking.show', $query->id).">#".$query->id ."</a>";
    // })
    ->editColumn('details' , function ($query){
        
        return "<a class='btn-link btn-link-hover' href=" .route('booking.show', ['id'=>$query->id]).">".__('messages.details') ."</a>";
    })
    ->editColumn('status' , function ($query){
        return bookingstatus($query->status);
    })
    ->editColumn('driver' , function ($query){
        $driver = $query->driver;
        if($driver != null) { return view('booking.driver', compact('driver'));}
        return 'search on driver..';
    })
    ->editColumn('subservice' , function ($query){
        return  $service_name = ($query->subServiceId != null && isset($query->subService)) ? $query->subService->name : "--";
      })
      ->editColumn('end_at' , function ($query){
        $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
        $datetime = $sitesetup ? json_decode($sitesetup->value) : null;
       
        $formattedDate =  optional($datetime)->date_format && optional($datetime)->time_format
        ? date(optional($datetime)->date_format, strtotime($query->endAt)) . ' / ' . date(optional($datetime)->time_format, strtotime($query->endAt))
        : $query->endAt;
        return $formattedDate;     
     })

      ->editColumn('paymentStatus' , function ($query){
          if($query->paymentStatus !== null) {return $query->paymentStatus ;}
        return '--' ;
      })
      ->editColumn('PaymentDatetime' , function ($query){
        $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
        $datetime = $sitesetup ? json_decode($sitesetup->value) : null;
       
        $formattedDate =  optional($datetime)->date_format && optional($datetime)->time_format
        ? date(optional($datetime)->date_format, strtotime($query->PaymentDatetime)) . ' / ' . date(optional($datetime)->time_format, strtotime($query->PaymentDatetime))
        : $query->PaymentDatetime;
        return $formattedDate;
      })
      ->editColumn('rideCommission' , function ($query){
        return  $query->time;
      })
      ->editColumn('startAddress' , function ($query){
          return  $query->startAddress;
        })
      ->editColumn('endAddress' , function ($query){
          return  $query->startAddress;
        })
      ->editColumn('distance' , function ($query){
          return  $query->distance;
        })
      ->editColumn('time' , function ($query){
          return  $query->time;
        })

      ->editColumn('start_at', function($query) {
          $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
          $datetime = $sitesetup ? json_decode($sitesetup->value) : null;
         
          $formattedDate =  optional($datetime)->date_format && optional($datetime)->time_format
          ? date(optional($datetime)->date_format, strtotime($query->startAt)) . ' / ' . date(optional($datetime)->time_format, strtotime($query->startAt))
          : $query->startAt;
          return $formattedDate;
      })
      ->editColumn('status' , function ($query){
          return bookingstatus($query->status);
      })
      ->editColumn('total_amount' , function ($query){
          return $query->totalAmount ? getPriceFormat($query->totalAmount) : '-';
      })

    // ->filterColumn('userId',function($query,$keyword){
    //     $query->whereHas('userId',function ($q) use($keyword){
    //         $q->where('firstName','like','%'.$keyword.'%');
    //     });
    // })
    // ->editColumn('service_id' , function ($query){
    //   return  $service_name = ($query->subServiceId != null && isset($query->subService)) ? $query->subService->name : "--";
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
    ->editColumn('office' , function ($query){
        $office = $query->office;
        if($office != null) { return  $office->officeName;
            //view('booking.office', compact('office'));
        }
        return 'search on office..';
    })


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
    ->editColumn('total_amount' , function ($query){
        return $query->totalAmount ? getPriceFormat($query->totalAmount) : '-';
    })

    // ->addColumn('action', function($booking){
    //     return view('booking.action',compact('booking'))->render();
    // })

    // ->editColumn('updated_at', function ($query) {
    //     $diff = Carbon::now()->diffInHours($query->updated_at);
    //     if ($diff < 25) {
    //         return $query->updated_at->diffForHumans();
    //     } else {
    //         return $query->updated_at->isoFormat('llll');
    //     }
    // })
    // ->addIndexColumn()
    ->rawColumns(['check' ,'id','user' , 'service_id', 'office','total_amount','status','details','date'])
    ->make(true);
   }




//    'startAt',
//    'amount',
//    'discount',
//    'total_amount',
//    'status',
//    'startAddress',
//    'endAddress',
//    'distance',
//    'subservice',
//    'multiDestnationArray',
//    'time',
//    'created_at'
//    'rideCommission'


   public function pending_dataTable ($query): JsonResponse{
    return DataTables::of($query)
    ->addColumn('check', function ($row) {
        return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" data-type="booking" onclick="dataTableRowCheck('.$row->id.',this)">';
    })
    ->editColumn('id' , function ($query){
        return "<a class='btn-link btn-link-hover' href=" .route('booking.show', $query->id).">#".$query->id ."</a>";
    })
    ->editColumn('subservice' , function ($query){
      return  $service_name = ($query->subServiceId != null && isset($query->subService)) ? $query->subService->name : "--";
    })
    ->editColumn('startAddress' , function ($query){
        return  $query->startAddress;
      })
    ->editColumn('endAddress' , function ($query){
        return  $query->startAddress;
      })
    ->editColumn('distance' , function ($query){
        return  $query->distance;
      })
    ->editColumn('time' , function ($query){
        return  $query->time;
      })
    ->editColumn('rideCommission' , function ($query){
        return  $query->time;
      })
    ->editColumn('created_at', function($query) {
        $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
        $datetime = $sitesetup ? json_decode($sitesetup->value) : null;
       
        $formattedDate =  optional($datetime)->date_format && optional($datetime)->time_format
        ? date(optional($datetime)->date_format, strtotime($query->created_at)) . ' / ' . date(optional($datetime)->time_format, strtotime($query->created_at))
        : $query->created_at;
        return $formattedDate;
    })
    ->editColumn('status' , function ($query){
        return bookingstatus($query->status);
    })
    ->editColumn('total_amount' , function ($query){
        return $query->totalAmount ? getPriceFormat($query->totalAmount) : '-';
    })
    // ->editColumn('office' , function ($query){
    //     $office = $query->office->officeName;
    //     if($office != null) { return  $office;
    //         //view('booking.office', compact('office'));
    //     }
    //     return 'search on office..';
    // })

    // ->addColumn('action', function($booking){
    //     return view('booking.action',compact('booking'))->render();
    // })

    // ->editColumn('updated_at', function ($query) {
    //     $diff = Carbon::now()->diffInHours($query->updated_at);
    //     if ($diff < 25) {
    //         return $query->updated_at->diffForHumans();
    //     } else {
    //         return $query->updated_at->isoFormat('llll');
    //     }
    // })
    // ->addIndexColumn()
    ->rawColumns(['check' ,'id','user' , 'service_id', 'office','total_amount','status','details','date'])
    ->make(true);
   }






   public function ongoing_dataTable ($query): JsonResponse{
    return DataTables::of($query)
    ->addColumn('check', function ($row) {
        return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" data-type="booking" onclick="dataTableRowCheck('.$row->id.',this)">';
    })
    ->editColumn('map' , function ($bookingdata){
        return "<a class='btn-link btn-link-hover' href=" .route('order.follow.map', ['orderId'=>$bookingdata->id]).">".__('messages.follow_on_map') ."</a>";
    })
    ->editColumn('status' , function ($query){
        return bookingstatus($query->status);
    })
    ->editColumn('driver' , function ($query){
        $driver = $query->driver;
        if($driver != null) { return view('driver.driver', compact('driver'));}
        return 'search on driver..';
    })
    ->editColumn('subservice' , function ($query){
        return  $service_name = ($query->subServiceId != null && isset($query->subService)) ? $query->subService->name : "--";
      })
      ->editColumn('startAddress' , function ($query){
          return  $query->startAddress;
        })
      ->editColumn('endAddress' , function ($query){
          return  $query->startAddress;
        })
      ->editColumn('distance' , function ($query){
          return  $query->distance;
        })
      ->editColumn('time' , function ($query){
          return  $query->time;
        })
      ->editColumn('rideCommission' , function ($query){
          return  $query->time;
        })
      ->editColumn('start_at', function($query) {
          $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
          $datetime = $sitesetup ? json_decode($sitesetup->value) : null;
         
          $formattedDate =  optional($datetime)->date_format && optional($datetime)->time_format
          ? date(optional($datetime)->date_format, strtotime($query->startAt)) . ' / ' . date(optional($datetime)->time_format, strtotime($query->startAt))
          : $query->startAt;
          return $formattedDate;
      })
      ->editColumn('status' , function ($query){
          return bookingstatus($query->status);
      })
      ->editColumn('total_amount' , function ($query){
          return $query->totalAmount ? getPriceFormat($query->totalAmount) : '-';
      })

    ->rawColumns(['check' ,'id','user' ,'map', 'service_id', 'office','total_amount','status','details','date'])
    ->make(true);
   }
}