<?php
namespace App\Http\Services\Dashboard\CommissionManagement\UpdateOfficeCommissions\Logic;

use App\Http\Core\Const\Options\Roles;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class UpdateOfficeCommissionsLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private UpdateOfficeCommissionsInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        if(!check_auth_user_has_role([Roles::Office->value])){
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }

        $office_info = $this->repository->OfficeRepository()->updateRepository();
        
        if(demoUserPermission()){
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }

        switch($this->input->getType())
        {
            case 'office_car' :
                $office_commission  = $this->input->getCommissionWithOfficeCar();
                $driver_commission = $this->input->getDriverCommissionPrecentage();

                if(($driver_commission + $driver_commission) != 100){
                    $message = 'مجموع نسب العمولات لا يساي الـ 100 ';
                    return redirect(route('driver.index'))->withErrors(['completed_value'=>$message]);
                }
           
                $office_info->updateFirst([],[
                    'commission_with_office_car' => $office_commission,
                    'driver_commission_precentage' => $driver_commission,
                ]);
                $message = ' تم تحديث عمولات السائقين بنجاح ';
                return redirect(route('commissions.free-driver'))->withSuccess($message);
                break;

            case 'driver_car':
                $office_commission  = $this->input->getCommissionWithDriverCar();
                $driver_commission = $this->input->getDriverCarCommissionPrecentage();

                if(($driver_commission + $office_commission) != 100){
                    $message = 'مجموع نسب العمولات لا يساي الـ 100 ';
                    return redirect(route('commissions.free-driver'))->withErrors(['completed_value'=>$message]);
                }

                $office_info->updateFirst([],[
                    'commission_with_driver_car' =>$office_commission,
                    'driver_car_commission_precentage' => $driver_commission,
                ]);
                $message = ' تم تحديث عمولات السائقين بنجاح ';
                return redirect(route('commissions.free-driver'))->withSuccess($message);
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