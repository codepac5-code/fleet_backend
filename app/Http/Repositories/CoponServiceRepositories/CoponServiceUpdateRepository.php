<?php
namespace App\Http\Repositories\CoponServiceRepositories;
use App\Models\CouponService;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;

class CoponServiceUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new CouponService();
    }

}
