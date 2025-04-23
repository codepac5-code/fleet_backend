<?php
namespace App\Http\Repositories\AddressRepositories;

class AddressRepositoryCaller{
    
    static public function createRepository(){return (new AddressCreateRepository());}
    static public function readRepository(){return (new AddressReadRepository());}
    static public function updateRepository(){return (new AddressUpdateRepository());}
    static public function deleteRepository(){return (new AddressDeleteRepository());}
    
}