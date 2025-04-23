<?php
namespace App\Http\Services\Dashboard\CommissionManagement\UpdateCommissions\Logic;

use App\Http\Core\Const\Options\Roles;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class UpdateCommissionsLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private UpdateCommissionsInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $fleet_office_info = $this->repository->FleetOfficeRepository()->updateRepository();
        
        if(demoUserPermission()){
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }

        switch($this->input->getType())
        {
            case 'fleet_driver' :
                $fleet_commission  = $this->input->getFleetCommissionValueWithDriver();
                $driver_commission = $this->input->getDriverCommission();

                if(($fleet_commission + $driver_commission) != 100){
                    $message = 'مجموع نسب العمولات لا يساي الـ 100 ';
                    return redirect(route('commissions.free-driver'))->withErrors(['completed_value'=>$message]);
                }

                $fleet_office_info->updateFirst([],[
                    'fleet_commission_value_with_driver' => $fleet_commission,
                    'driver_commission_value' => $driver_commission,
                ]);
                $message = ' تم تحديث عمولات السائقين بنجاح ';
                return redirect(route('commissions.fleet'))->withSuccess($message);
                break;

            case 'fleet_office':
                $fleet_commission  = $this->input->getFleetCommissionValueWithDriver();
                $office_commission = $this->input->getOfficeCommission();

                if(($fleet_commission + $office_commission) != 100){
                    $message = 'مجموع نسب العمولات لا يساي الـ 100 ';
                    return redirect(route('commissions.fleet.office'))->withErrors(['completed_value'=>$message]);
                }

                $fleet_office_info->updateFirst([],[
                    'fleet_commission_value_with_driver' =>$fleet_commission,
                    'office_commission_value' => $office_commission,
                ]);
                $message = ' تم تحديث عمولات المكاتب بنجاح ';
                return redirect(route('commissions.fleet'))->withSuccess($message);
                break;
            
            default :
            $message = 'نوع العمولات المطلوب غير مدعوم';
            return redirect()->back()->withErrors($message);
            break;
            // case 'fleet_office_driver':
            //     break;

            // case 'fleet_office_driver_owner':
            //     break;
        }

   }
}