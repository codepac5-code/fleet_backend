<?php
namespace App\Http\Repositories\SubscriptionPolicyRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\SubscriptionPolicy;

class SubscriptionPolicyCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new SubscriptionPolicy();
    }
}
