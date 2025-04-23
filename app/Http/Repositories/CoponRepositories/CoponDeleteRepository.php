<?php
namespace App\Http\Repositories\CoponRepositories;

use App\Models\Coupon;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;

class CoponDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Coupon();
    }
}
