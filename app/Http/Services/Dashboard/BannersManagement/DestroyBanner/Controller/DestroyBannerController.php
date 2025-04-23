<?php
namespace App\Http\Services\Dashboard\BannersManagement\DestroyBanner\Controller;

use App\Http\Services\Dashboard\BannersManagement\DestroyBanner\Logic\DestroyBannerInput;
use App\Http\Services\Dashboard\BannersManagement\DestroyBanner\Logic\DestroyBannerLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use Illuminate\Http\Request;

class DestroyBannerController extends Controller
{
    public function __invoke(Request $request )
    {
        // validate input data and pass it to the service..
        $data = $request->all();
        $input = new DestroyBannerInput($data);

        $service = new DestroyBannerLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute(); // send response..
    }
}