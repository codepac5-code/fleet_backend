<?php
namespace App\Http\Repositories\FleetOfficeRepositories;
use App\Models\{FleetOffice};

class FleetOfficeRepositoryCaller{

    static public function createRepository(){return (new FleetOfficeCreateRepository());}
    static public function readRepository(){return (new FleetOfficeReadRepository());}
    static public function updateRepository(){return (new FleetOfficeUpdateRepository());}
    static public function deleteRepository(){return (new FleetOfficeDeleteRepository());}
    static public function get_model() : FleetOffice {return (new FleetOffice());}


}