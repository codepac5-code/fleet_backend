<?php
namespace App\Http\Services\Dashboard\CommissionManagement\ToView;

use App\Models\User;
use App\Models\Office;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Services\Dashboard\ServiceManagement\AddService\Request\AddServiceRequest;
use App\Models\City;
use App\Models\Country;
use App\Models\FleetOffice;
use App\Models\Service;

class CommissionsLayout extends Controller
{
    public function __invoke (Request $request)
    {


        $officeId = getOfficeIdByAuthUser();
        $pageTitle = __('messages.update_form_title',['form'=> __('update_commission')]);
        $auth_user = authSession();

        if(is_null($officeId)){
                // $repo = new RepositoryCaller();
                // $commissions = $repo->CommissionsRepository()->readRepository()->getByValue('type','driver-owner');
                $commissions = FleetOffice::first();
                return view('commission.fleet_commissions.index', compact('commissions','pageTitle','auth_user'));
        }

        $commissions = Office::find($officeId);
        return view('commission.office_commissions.index', compact('commissions','pageTitle','auth_user'));
    }
}
