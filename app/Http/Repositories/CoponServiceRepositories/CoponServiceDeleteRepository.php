<?php
namespace App\Http\Repositories\CoponServiceRepositories;
use App\Models\CouponService;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;

class CoponServiceDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new CouponService();
    }
}
