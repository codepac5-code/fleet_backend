<?php

namespace App\Http\Services\Dashboard\ServiceManagement\ViewService\Controller;

use App\Models\Service;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Response\SendResponse;
use App\Http\Controllers\Controller;
use App\Http\Services\Dashboard\ServiceManagement\ViewService\Logic\ViewServiceInput;
use App\Http\Services\Dashboard\ServiceManagement\ViewService\Logic\ViewServiceLogic;
use App\Http\Services\Dashboard\ServiceManagement\ViewService\Request\ViewServiceListRequest;

class ViewServiceController extends Controller
{
    public function __invoke( ViewServiceListRequest $request)
    {

        // validate input data and pass it to the service..
        $input = new ViewServiceInput($request->validated());

        $service = new ViewServiceLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute();

    }
}
