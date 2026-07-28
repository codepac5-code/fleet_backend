<?php
namespace App\Http\Repositories\TravelRoutesRepositories;
use App\Models\{TravelRoutes};

class TravelRoutesRepositoryCaller{

    static public function createRepository(){return (new TravelRoutesCreateRepository());}
    static public function readRepository(){return (new TravelRoutesReadRepository());}
    static public function updateRepository(){return (new TravelRoutesUpdateRepository());}
    static public function deleteRepository(){return (new TravelRoutesDeleteRepository());}
    static public function get_model() : TravelRoutes {return (new TravelRoutes());}


}