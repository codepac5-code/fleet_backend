<?php
namespace App\Http\Repositories\VehicleBrandRepositories;
use App\Models\{VehicleBrand};

class VehicleBrandRepositoryCaller{

    static public function createRepository(){return (new VehicleBrandCreateRepository());}
    static public function readRepository(){return (new VehicleBrandReadRepository());}
    static public function updateRepository(){return (new VehicleBrandUpdateRepository());}
    static public function deleteRepository(){return (new VehicleBrandDeleteRepository());}
    static public function get_model() : VehicleBrand {return (new VehicleBrand());}


}