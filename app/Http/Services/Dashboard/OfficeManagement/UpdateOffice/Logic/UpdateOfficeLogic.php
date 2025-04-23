<?php
namespace App\Http\Services\Dashboard\OfficeManagement\UpdateOffice\Logic;

use App\Http\Core\Classes\ImageManager;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class UpdateOfficeLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private UpdateOfficeInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {



        beginTransaction();
        $new_data = 
        [
            "email"         =>$this->input->getEmail(),
            "country"       =>$this->input->getCountry(),
            "region"        =>$this->input->getRegion(),
            "city"          =>$this->input->getCity(),
            "contactNumber" =>$this->input->getContactNumber(),
            // "status"        =>$this->input->getStatus(),
            "address"       =>$this->input->getAddress(),
            "officeName"    =>$this->input->getName(),
            'limitOrders'   =>$this->input->getLimitOrders(),
            // 'walletBalance' =>$this->input->getWalletBalance(),
        ];

        $ImageManager = new ImageManager();
        if($this->input->hasImage()){
            $path = $ImageManager->upload($this->input->getLogo(), $path = 'office/logo');
            $path = $ImageManager->withStorge( $path );
            $new_data['logo'] = $path;
           // $ImageManager->delete();
        }

        $updated = $this->repository->OfficeRepository()->updateRepository()->update(
            ['id'=>$this->input->getId()],
            $new_data
        );

        if($updated <= 0 ){
            rollbackTransaction();
            $ImageManager->delete($path);
            return redirect()->back()->withErrors(__('message.something_wrong'));
        }

        
        commitTransaction();
        $message = __('messages.update_form',[ 'form' => __('messages.office') ] );

		return redirect(route('office.index'))->withSuccess($message);
   }
}