<?php
namespace App\Http\Services\Dashboard\CommissionManagement\ToView\fleet;

use App\Models\User;
use App\Models\Office;
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

class ViewFleetOfficeCommissions extends Controller
{
    public function __invoke (Request $request)
    {
        $auth_user = authSession();
        // $repo = new RepositoryCaller();
        // $commissions = $repo->CommissionsRepository()->readRepository()->getByValue('type','driver-owner');
        $pageTitle = __('messages.update_form_title',['form'=> __('update_commission')]);
        $fleet = FleetOffice::first();
        return view('commission.fleet_commissions.office_commission', compact('fleet','pageTitle','auth_user'));
    }
}
