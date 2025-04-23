<?php
namespace App\Http\Repositories\FleetRepositories;
use App\Models\{Fleet};

class FleetRepositoryCaller{

    static public function createRepository(){return (new FleetCreateRepository());}
    static public function readRepository(){return (new FleetReadRepository());}
    static public function updateRepository(){return (new FleetUpdateRepository());}
    static public function deleteRepository(){return (new FleetDeleteRepository());}
    static public function get_model() : Fleet {return (new Fleet());}


}