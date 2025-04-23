<?php
namespace App\Http\Repositories\SubscriptionPolicyRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\SubscriptionPolicy;

class SubscriptionPolicyDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new SubscriptionPolicy();
    }
}
