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

        // validate input data and pass it to the service..
        $input = new ViewOfficeListInput($request->validated());

        $service = new ViewOfficeListLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute();

        // return SendResponse::sendSuccessResponse($result); // send response..
    }
}
