<?php
namespace App\Http\Repositories\SubscriptionPolicyRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\SubscriptionPolicy;

class SubscriptionPolicyUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new SubscriptionPolicy();
    }

}
