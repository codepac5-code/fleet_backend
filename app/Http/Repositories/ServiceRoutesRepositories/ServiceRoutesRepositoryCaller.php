<?php
namespace App\Http\Repositories\ServiceRoutesRepositories;
use App\Models\{ServiceRoutes};

class ServiceRoutesRepositoryCaller{

    static public function createRepository(){return (new ServiceRoutesCreateRepository());}
    static public function readRepository(){return (new ServiceRoutesReadRepository());}
    static public function updateRepository(){return (new ServiceRoutesUpdateRepository());}
    static public function deleteRepository(){return (new ServiceRoutesDeleteRepository());}
    static public function get_model() : ServiceRoutes {return (new ServiceRoutes());}


}