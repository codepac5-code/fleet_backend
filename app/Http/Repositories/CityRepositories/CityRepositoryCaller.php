<?php
namespace App\Http\Repositories\CityRepositories;

class CityRepositoryCaller{

    static public function createRepository(){return (new CityCreateRepository());}
    static public function readRepository(){return (new CityReadRepository());}
    static public function updateRepository(){return (new CityUpdateRepository());}
    static public function deleteRepository(){return (new CityDeleteRepository());}

}