<?php
namespace App\Http\Services\Driver\SendDriverIssue\Logic;

use App\Http\Core\Classes\ImageManager;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class SendDriverIssueLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private SendDriverIssueInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $ImageManager = new ImageManager();
        
        if($this->input->getPhoto() != null){
             $path = $ImageManager->upload($this->input->getPhoto(), $path = 'user/report');
             $path = $ImageManager->withStorge( $path );
        }
        else {
            $path = null;
        }

        $user = getAuthUser();

        $this->repository->DriversIssueRepository()->createRepository()
        ->create([
            'subject'=>$this->input->getSubject(),
            'description'=>$this->input->getDescription(),
            'photo'=> $path,
            'driverId'=> $user->id
        ]);

        $response  = new SendDriverIssueOutput([] , 'issue sending..');
        return $response->send_as_object();
   }
}