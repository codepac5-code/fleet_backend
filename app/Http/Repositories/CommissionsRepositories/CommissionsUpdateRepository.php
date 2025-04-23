<?php
namespace App\Http\Repositories\CommissionsRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\Commissions;

class CommissionsUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new Commissions();
    }

}