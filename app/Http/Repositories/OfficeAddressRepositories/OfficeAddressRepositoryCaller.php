<?php
namespace App\Http\Repositories\OfficeAddressRepositories;
use App\Models\{OfficeAddress};

class OfficeAddressRepositoryCaller{

    static public function createRepository(){return (new OfficeAddressCreateRepository());}
    static public function readRepository(){return (new OfficeAddressReadRepository());}
    static public function updateRepository(){return (new OfficeAddressUpdateRepository());}
    static public function deleteRepository(){return (new OfficeAddressDeleteRepository());}
    static public function get_model() : OfficeAddress {return (new OfficeAddress());}


}