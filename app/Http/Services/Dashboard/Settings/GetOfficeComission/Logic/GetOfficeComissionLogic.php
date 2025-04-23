<?php
namespace App\Http\Services\Dashboard\Settings\GetOfficeComission\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class GetOfficeComissionLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private GetOfficeComissionInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $auth_user = authSession();
        $office = $this->repository->OfficeRepository()
        ->readRepository()->find($this->input->getOfficeId());

        if (empty($office)) {
            $msg = __('messages.not_found_entry', ['name' => __('messages.office')]);
            return redirect(route('office.index'))->withError($msg);
        }
        $pageTitle = __('messages.view_form_title', ['form' => __('messages.office')]);

        if ($office->commissionType === 'percentage') {
            $commission = $office->commissionValue . '%'; 
        }
        else {
            $commission = number_format($office->commissionValue, 2) . ' ' . __('messages.currency'); 
        }

        $commissionType = $office->commissionType;



        return view('setting.comission', compact('pageTitle', 'office', 'auth_user','commissionType','commission'));

   }
}