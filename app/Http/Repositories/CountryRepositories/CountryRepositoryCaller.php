<?php
namespace App\Http\Repositories\CountryRepositories;

class CountryRepositoryCaller{

    static public function createRepository(){return (new CountryCreateRepository());}
    static public function readRepository(){return (new CountryReadRepository());}
    static public function updateRepository(){return (new CountryUpdateRepository());}
    static public function deleteRepository(){return (new CountryDeleteRepository());}

}