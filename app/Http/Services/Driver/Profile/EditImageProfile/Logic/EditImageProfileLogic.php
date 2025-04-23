<?php
namespace App\Http\Services\Driver\Profile\EditImageProfile\Logic;

use App\Http\Core\Classes\ImageManager;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Illuminate\Support\Facades\Storage;

class EditImageProfileLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private EditImageProfileInput $input,  /*| Pass Request To Service*/
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

        // $name = time() . "_." . $this->input->getPhoto()->getClientOriginalExtension() ;
        // $path = $this->input->getPhoto()->storeAs("driver/profile",$name);

        $driverReadRepositer = $this->repository->DriverRepository()->readRepository();
        $driver = $driverReadRepositer->find($this->input->getDriverId(),['id',"photo"]);

        $ImageManager->delete($driver->photo);

        $userUpdateRepositer = $this->repository->DriverRepository()->updateRepository();
        $userUpdateRepositer->update(['id'=>$this->input->getDriverId()],
        [
            'photo' => $path
        ]);


        $response  = new EditImageProfileOutput([]  , 'update successfully');
        return $response->send_as_object();
   }
}
