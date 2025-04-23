<?php
namespace App\Http\Services\Dashboard\DriverManagement\ViewDriversList\Controller;

use App\Models\Driver;
use App\Models\Setting;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Services\Dashboard\DriverManagement\ViewDriversList\Logic\ViewDriversListInput;
use App\Http\Services\Dashboard\DriverManagement\ViewDriversList\Logic\ViewDriversListLogic;

use App\Http\Services\Dashboard\DriverManagement\ViewDriversList\Request\ViewDriversListRequest;

class ViewDriversListController extends Controller
{
    public function __invoke(ViewDriversListRequest $request)
    {        


        
        // validate input data and pass it to the service..
        $input = new ViewDriversListInput($request->validated());

        $service = new ViewDriversListLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return $result ;// send response..
    }


}