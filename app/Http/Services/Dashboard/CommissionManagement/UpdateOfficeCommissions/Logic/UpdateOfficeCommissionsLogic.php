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
    
        // if(!check_auth_user_has_role([Roles::Office->value])){
        //     return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        // }

        $officeId = getOfficeIdByAuthUser();
        $office = $this->repository->OfficeRepository()->updateRepository();

        // if(demoUserPermission()){
        //     return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        // }

        switch($this->input->getType())
        {
            case 'office_car' :
                $driver_commission = $this->input->getDriverCommissionPrecentage();
                $office_commission  = 100 -  $driver_commission;


                $office->updateFirst(['id'=>$officeId],[
                    'commission_with_office_car' => $office_commission,
                    'driver_commission_precentage' => $driver_commission,
                ]);
                $message = ' تم تحديث عمولات السائقين بنجاح ';
                // return redirect(route('commissions.free-driver'))->withSuccess($message);
                break;

            case 'driver_car':
                $driver_commission = $this->input->getDriverCarCommissionPrecentage();
                $office_commission  = 100 -  $driver_commission;

                $office->updateFirst(['id'=>$officeId],[
                    'commission_with_driver_car' =>$office_commission,
                    'driver_car_commission_precentage' => $driver_commission,
                ]);
                $message = ' تم تحديث عمولات السائقين بنجاح ';
                // return redirect(route('commissions.free-driver'))->withSuccess($message);
                break;

            default :
            $message = 'نوع العمولات المطلوب غير مدعوم';
            return response()->json([
                        'success' => false,
                        'message' =>  $message
                    ]);
        }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
   }
}
