<?php
namespace App\Http\Services\Dashboard\BookingManagement;

use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Response\SendResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class officeOrderController extends Controller
{
    public function __invoke(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $pageTitle = __('messages.orders');
        $auth_user = 55; //authSession();

        $office = null;
        if(isset($request->officeId)){
            $office = Office::find($request->officeId);
            if($office == null){
                return view('errors.404');
            }
        }


        $assets = ['datatable'];
        return view('booking.follow.office_orders', compact('pageTitle','auth_user','assets','filter','office'));
    }
}