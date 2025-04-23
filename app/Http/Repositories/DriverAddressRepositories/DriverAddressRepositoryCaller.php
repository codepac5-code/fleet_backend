<?php
namespace App\Http\Repositories\DriverAddressRepositories;
use App\Models\{DriverAddress};

class DriverAddressRepositoryCaller{

    static public function createRepository(){return (new DriverAddressCreateRepository());}
    static public function readRepository(){return (new DriverAddressReadRepository());}
    static public function updateRepository(){return (new DriverAddressUpdateRepository());}
    static public function deleteRepository(){return (new DriverAddressDeleteRepository());}
    static public function get_model() : DriverAddress {return (new DriverAddress());}


}