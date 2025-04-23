<?php
namespace App\Http\Repositories\FleetStatisticRepositories;
use App\Models\{FleetStatistic};

class FleetStatisticRepositoryCaller{

    static public function createRepository(){return (new FleetStatisticCreateRepository());}
    static public function readRepository(){return (new FleetStatisticReadRepository());}
    static public function updateRepository(){return (new FleetStatisticUpdateRepository());}
    static public function deleteRepository(){return (new FleetStatisticDeleteRepository());}
    static public function get_model() : FleetStatistic {return (new FleetStatistic());}


}