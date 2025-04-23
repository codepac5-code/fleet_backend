<?php
namespace App\Http\Repositories\VehicleRepositories;
use App\Models\{Vehicle};

class VehicleRepositoryCaller{

    static public function createRepository(){return (new VehicleCreateRepository());}
    static public function readRepository(){return (new VehicleReadRepository());}
    static public function updateRepository(){return (new VehicleUpdateRepository());}
    static public function deleteRepository(){return (new VehicleDeleteRepository());}
    static public function get_model() : Vehicle {return (new Vehicle());}


}