<?php
namespace App\Http\Services\Dashboard\VehicleManagement;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Response\SendResponse;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Services\Dashboard\ServiceManagement\AddService\Request\AddServiceRequest;

class IndexVehicleController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        
        $filter = [
            'status' => $request->status,
        ];
        $pageTitle = __('messages.vehicles' );
        $auth_user = 2;//authSession();
        $assets = ['datatable'];
        return view('vehicle.index', compact('pageTitle','auth_user','assets','filter'));

        $response = (new ResponseModel(
            data:
            [
                'pageTitle'=>$pageTitle,
                'auth_user'=>$auth_user,
                'assets'=>$assets,
                'filter'=>$filter
            ],
            message:'',
            status:200,
            viewPath:'booking.index'
       ));

    }
}
