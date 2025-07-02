<?php
namespace App\Http\Repositories\OfficeServiceRepositories;
use App\Models\{OfficeService};

class OfficeServiceRepositoryCaller{

    static public function createRepository(){return (new OfficeServiceCreateRepository());}
    static public function readRepository(){return (new OfficeServiceReadRepository());}
    static public function updateRepository(){return (new OfficeServiceUpdateRepository());}
    static public function deleteRepository(){return (new OfficeServiceDeleteRepository());}
    static public function get_model() : OfficeService {return (new OfficeService());}


}