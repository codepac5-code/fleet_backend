<?php
namespace App\Http\Services\User\ProfileManagement\EdateImageProfile\Logic;

use App\Http\Core\Classes\ImageManager;
use Illuminate\Support\Facades\Storage;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Services\User\ProfileManagement\EdateImageProfile\Logic\EdateImageProfileInput;

class EdateImageProfileLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private EdateImageProfileInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller();
    }


    public function execute (): ResponseModel {

        $ImageManager = new ImageManager();
        
        if($this->input->getPhoto() != null){
             $path = $ImageManager->upload($this->input->getPhoto(), $path = 'user/profile');
             $path = $ImageManager->withStorge( $path );
        }
        else {
            $path = $ImageManager->default_profile_photo();
        }

        // $name = time() . "_." . $this->input->getPhoto()->getClientOriginalExtension();
        // $path = $this->input->getPhoto()->storeAs("user/profile", $name);
        
        $userReadRepositer = $this->repository->UserRepository()->readRepository();
        $user = $userReadRepositer->find($this->input->getUserId(),['id',"photo"]);
        $ImageManager->delete($user->photo);


        $userUpdateRepositer = $this->repository->UserRepository()->updateRepository();
        $userUpdateRepositer->update(['id'=>$this->input->getUserId()],
        [
            'photo' => $path
        ]);

        // Storage::delete(substr(parse_url($oldPath, PHP_URL_PATH),9));

        $response  = new EdateImageProfileOutput([]  , 'update successfully');
        return $response->send_as_object();

   }
}
