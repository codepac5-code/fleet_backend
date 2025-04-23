<?php
namespace App\Http\Services\Dashboard\CommissionManagement\ToView\office;

use App\Models\User;
use App\Models\Office;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Const\Options\Roles;
use App\Http\Core\Response\SendResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Services\Dashboard\ServiceManagement\AddService\Request\AddServiceRequest;
use App\Models\City;
use App\Models\Country;
use App\Models\FleetOffice;
use App\Models\Service;

class ViewDriverCarCommissions extends Controller
{
    public function __invoke (Request $request)
    {
        $auth_user = authSession();
        // $repo = new RepositoryCaller();
        // $commissions = $repo->CommissionsRepository()->readRepository()->getByValue('type','driver-owner');
        $user = auth()->user();
        if(!check_auth_user_has_role([Roles::Office->value])){
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        // if($user->hasAnyRole()){

        // }
        $id = $user->id;
        $office = Office::find($id);
        $pageTitle = __('messages.update_form_title',['form'=> __('update_commission')]);

        return view('commission.office_commissions.driver_commissions', compact('office','pageTitle','auth_user'));
    }
}
