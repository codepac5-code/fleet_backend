<?php
namespace App\Http\Services\Dashboard\BannersManagement\Views;

use App\Models\User;
use App\Models\Driver;
use App\Models\Office;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Services\Dashboard\ServiceManagement\AddService\Request\AddServiceRequest;
use App\Models\Slider;

class CU_BannerPageController extends Controller
{
    public function __invoke (Request $request)
    {
        $id = $request->id;
        $auth_user = authSession();

        $banner = Slider::find($id);
        $pageTitle = __('messages.update_form_title',['form'=> __('messages.banner')]);

        if($banner == null){
            $pageTitle = __('messages.add_button_form',['form' => __('messages.banner')]);
            $banner = new Slider;
        }

        $offices = Office::all(); 
        return view('banner.create', compact('pageTitle' ,'banner' ,'auth_user' , 'offices'));
    }
}
