<?php
namespace App\Http\Repositories\CoponUserRepositories;

use App\Models\Coupon;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;

class CoponUserDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Coupon();
    }
}
