<?php
namespace App\Http\Services\Dashboard\CouponManagement\ToView;

use App\Models\Coupon;
use App\Models\Service;
use App\Models\SubService;
use App\Models\User;
use Illuminate\Http\Request;


class CU_CouponPage 
{
    public function __invoke (Request $request)
    {
        $id = $request->id;
        $auth_user = authSession();

        $coupondata = Coupon::find($id);
        $pageTitle = __('messages.update_form_title',['form'=> __('messages.coupon')]);

        if($coupondata == null){
            $pageTitle = __('messages.add_button_form',['form' => __('messages.coupon')]);
            $coupondata = new Coupon;
        }

        $services = Service::all();
        return view('coupon.create', compact('pageTitle' ,'coupondata' ,'auth_user' ,'services'));
    }
}
