<?php
namespace App\Http\Repositories\DriverRepositories;

use App\Models\Driver;

class DriverRepositoryCaller{

    static public function createRepository(){return (new DriverCreateRepository());}
    static public function readRepository(){return (new DriverReadRepository());}
    static public function updateRepository(){return (new DriverUpdateRepository());}
    static public function deleteRepository(){return (new DriverDeleteRepository());}
    static public function get_model(){return (new Driver());}

}
