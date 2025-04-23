<?php
namespace App\Http\Repositories\CoponServiceRepositories;
use App\Models\CouponService;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;

class CoponServiceCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new CouponService();
    }
}
