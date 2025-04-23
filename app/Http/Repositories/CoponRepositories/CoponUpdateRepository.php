<?php
namespace App\Http\Repositories\CoponRepositories;

use App\Models\Coupon;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;

class CoponUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new Coupon();
    }

}
