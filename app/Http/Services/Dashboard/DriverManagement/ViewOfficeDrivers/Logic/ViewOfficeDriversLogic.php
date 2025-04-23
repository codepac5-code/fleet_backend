<?php
namespace App\Http\Services\Dashboard\DriverManagement\ViewOfficeDrivers\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class ViewOfficeDriversLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ViewOfficeDriversInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $auth_user = authSession();
        $office = $this->repository->OfficeRepository()->readRepository()
        ->getFirstWithRelation(['*'],['drivers'],['id'=>$this->input->getOfficeId()]);

        if($office == null )
        {
            $msg = __('messages.not_found_entry',['name' => __('messages.driver')] );
            return redirect(route('office.index'))->withError($msg);
        }
        $pageTitle = __('messages.view_form_title',['form'=> __('messages.office')]);

        return view('driver.view', compact('pageTitle' ,'office' ,'auth_user' ));
   }
}