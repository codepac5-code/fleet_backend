<?php
namespace App\Http\Services\Dashboard\CommissionManagement\ToView\office;

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

class ViewOfficeCommission extends Controller
{
    public function __invoke (Request $request)
    {
        $auth_user = authSession();

        $repo = new RepositoryCaller();
        $commissions = $repo->CommissionsRepository()->readRepository()->getByValue('type','office-owner');
        
        $pageTitle = __('messages.update_form_title',['form'=> __('update_commission')]);

        return view('commission.office_commission', compact('pageTitle' ,'commissions' ,'auth_user' ));
    }
}
