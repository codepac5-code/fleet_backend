<?php
namespace App\Http\Repositories\CoponServiceRepositories;
use App\Models\CouponService;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;

class CoponServiceReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new CouponService();
    }

}
