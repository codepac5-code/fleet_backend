<?php
namespace App\Http\Repositories\ServiceRepositories;

use App\Models\Service;

class ServiceRepositoryCaller{

    static public function get_model() : Service {return (new Service());}
    static public function createRepository(){return (new ServiceCreateRepository());}
    static public function readRepository(){return (new ServiceReadRepository());}
    static public function updateRepository(){return (new ServiceUpdateRepository());}
    static public function deleteRepository(){return (new ServiceDeleteRepository());}


}