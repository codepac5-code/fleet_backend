<?php
namespace App\Http\Repositories\CommissionEarningsRepositories;
use App\Models\{CommissionEarnings};

class CommissionEarningsRepositoryCaller{

    static public function createRepository(){return (new CommissionEarningsCreateRepository());}
    static public function readRepository(){return (new CommissionEarningsReadRepository());}
    static public function updateRepository(){return (new CommissionEarningsUpdateRepository());}
    static public function deleteRepository(){return (new CommissionEarningsDeleteRepository());}
    static public function get_model() : CommissionEarnings {return (new CommissionEarnings());}


}