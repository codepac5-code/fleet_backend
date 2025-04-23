<?php
namespace App\Http\Services\Dashboard\BannersManagement\ViewBannersList\Controller;

use App\Http\Services\Dashboard\BannersManagement\ViewBannersList\Logic\ViewBannersListInput;
use App\Http\Services\Dashboard\BannersManagement\ViewBannersList\Logic\ViewBannersListLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use Illuminate\Http\Request;

class ViewBannersListController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        $input = new ViewBannersListInput($request->all());

        $service = new ViewBannersListLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return $result; // send response..
    }
}