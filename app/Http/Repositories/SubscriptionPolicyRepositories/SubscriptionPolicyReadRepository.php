<?php
namespace App\Http\Repositories\SubscriptionPolicyRepositories;

use App\Http\Core\Const\Options\Settings;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\SubscriptionPolicy;

class SubscriptionPolicyReadRepository extends ReadRepository
{

    public function __construct()
    {
        $this->model = new SubscriptionPolicy();
    }

    public function getPolicyByLanguage() {
        $subscriptionPolicy = $this->model->query()->where("lang",Settings::getLanguage())->get();
        return $subscriptionPolicy;
    }
}
