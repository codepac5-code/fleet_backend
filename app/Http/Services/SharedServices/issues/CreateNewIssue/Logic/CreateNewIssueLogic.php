<?php
namespace App\Http\Services\SharedServices\issues\CreateNewIssue\Logic;

use App\Http\Core\Classes\ImageManager;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class CreateNewIssueLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private CreateNewIssueInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $ImageManager = new ImageManager();
        
        if($this->input->getPhoto() != null){
             $path = $ImageManager->upload($this->input->getPhoto(), $path = 'user/issue');
             $path = $ImageManager->withStorge( $path );
        }
        else {
            $path = null;
        }


        $this->repository->IssueRepository()->createRepository()
        ->createNewIssueForAuthUser(
           $this->input->getSubject(),
           $this->input->getDescription(),
           $path,
        );

        $response  = new CreateNewIssueOutput([] , 'issue sending..');
        return $response->send_as_object();
   }
}