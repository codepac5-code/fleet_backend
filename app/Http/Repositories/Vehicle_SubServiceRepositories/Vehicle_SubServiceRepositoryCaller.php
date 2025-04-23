<?php
namespace App\Http\Repositories\Vehicle_SubServiceRepositories;
use App\Models\{Vehicle_SubService};

class Vehicle_SubServiceRepositoryCaller{

    static public function createRepository(){return (new Vehicle_SubServiceCreateRepository());}
    static public function readRepository(){return (new Vehicle_SubServiceReadRepository());}
    static public function updateRepository(){return (new Vehicle_SubServiceUpdateRepository());}
    static public function deleteRepository(){return (new Vehicle_SubServiceDeleteRepository());}
    static public function get_model() : Vehicle_SubService {return (new Vehicle_SubService());}


}