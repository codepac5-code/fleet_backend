<?php
namespace App\Http\Repositories\DriverJobApplicationRepositories;
use App\Models\{DriverJobApplication};

class DriverJobApplicationRepositoryCaller{

    static public function createRepository(){return (new DriverJobApplicationCreateRepository());}
    static public function readRepository(){return (new DriverJobApplicationReadRepository());}
    static public function updateRepository(){return (new DriverJobApplicationUpdateRepository());}
    static public function deleteRepository(){return (new DriverJobApplicationDeleteRepository());}
    static public function get_model() : DriverJobApplication {return (new DriverJobApplication());}


}