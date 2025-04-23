<?php
namespace App\Http\Services\Driver\ChangeConnected\Logic;

use App\Http\Core\Classes\RedisManagerData;
use Illuminate\Support\Facades\Redis;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\Const\Messages\Attributes;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Const\Messages\ErrorMessages;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Services\Driver\ChangeConnected\Logic\ChangeConnectedOutput;

class ChangeConnectedLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ChangeConnectedInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ) {
        $this->repository = new RepositoryCaller();
    }


    public function execute (): ResponseModel {

        $driverUpdateRepository = $this->repository->DriverRepository()->updateRepository();
        $driver = $driverUpdateRepository->update(['id'=>$this->input->getDriverId()] , [
            'isConected' => $this->input->getIsConnected()
        ]);
        
        if($driver == null ){ make_exception('driver not found'); }

        if( !$this->input->getIsConnected()){
            RedisManagerData::makeDriverOffline($this->input->getDriverId());
        }
        else {
            RedisManagerData::makeDriverOffline($this->input->getDriverId());
            RedisManagerData::makeDriverOnline(
                $this->input->getDriverId(),
                $this->input->getLatitude(),
                $this->input->getLongitude(),
            );
        }

        $response  = new ChangeConnectedOutput($this->input->getIsConnected() , 'your status changed ');
        return $response->send_as_array();
   }
}
