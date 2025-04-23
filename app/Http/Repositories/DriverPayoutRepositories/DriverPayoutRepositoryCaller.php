<?php
namespace App\Http\Repositories\DriverPayoutRepositories;
use App\Models\{DriverPayout};

class DriverPayoutRepositoryCaller{

    static public function createRepository(){return (new DriverPayoutCreateRepository());}
    static public function readRepository(){return (new DriverPayoutReadRepository());}
    static public function updateRepository(){return (new DriverPayoutUpdateRepository());}
    static public function deleteRepository(){return (new DriverPayoutDeleteRepository());}
    static public function get_model() : DriverPayout {return (new DriverPayout());}


}