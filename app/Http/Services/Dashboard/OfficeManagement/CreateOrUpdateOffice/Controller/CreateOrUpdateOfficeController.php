<?php
namespace App\Http\Services\Dashboard\OfficeManagement\CreateOrUpdateOffice\Controller;

use App\Models\Office;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\OfficeManagement\CreateOrUpdateOffice\Logic\CreateOrUpdateOfficeInput;
use App\Http\Services\Dashboard\OfficeManagement\CreateOrUpdateOffice\Logic\CreateOrUpdateOfficeLogic;
use App\Http\Services\Dashboard\OfficeManagement\CreateOrUpdateOffice\Request\CreateOrUpdateOfficeRequest;
use App\Models\City;
use App\Models\Country;

class CreateOrUpdateOfficeController extends Controller
{
    public function __invoke(CreateOrUpdateOfficeRequest $request)
    {

        $data = $request->all();


        if($request->is('application/json') && $request->is('api/*') ){
            $date['isApi'] =true;
        }

        // validate input data and pass it to the service..
        $input = new CreateOrUpdateOfficeInput($data);

        $service = new CreateOrUpdateOfficeLogic($input); // call the service's logic

        // execute service and get result..
        return  $service->execute(); // send response..
    }


   
}
