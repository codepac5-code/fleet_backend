<?php
namespace App\Http\Services\User\GetSlides\Logic;

use App\Http\Core\Const\Messages\SuccessMessages;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class GetSlidesLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private GetSlidesInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller();
    }


    public function execute (): ResponseModel {

        $select = select_by_language([
            'image',
            'title',
            'description',
            'isActive',
        ] , [
            'title_en as title',
            'description_en as description',
            'image',
            'isActive',
        ]);

        $sliderReadRepository = $this->repository->SliderRepository()->readRepository();
        $slides = $sliderReadRepository->getByConditions([ 'isActive' => true ],
        $select 
    );

        $response  = new GetSlidesOutput(
        data: $slides,
        message:SuccessMessages::getKey('')
    );
        return $response->send_as_object();
   }
}
