<?php
namespace App\Http\Services\Dashboard\BookingManagement\Follow\FollowOrdersToView;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Booking;

class FollowOrdersToViewController extends Controller
{
    public function __invoke( Request $request )
    {
        $auth_user = authSession();
        $user = auth()->user();
        // $user->last_notification_seen = now();
        $user->save();

        $tabpage = 'info';

       $pageTitle = __('messages.view_form_title', ['form' => __('messages.booking')]);
       return view('booking.follow.view', compact('pageTitle',  'auth_user', 'tabpage'));
    }
}
