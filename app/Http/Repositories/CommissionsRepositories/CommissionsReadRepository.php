<?php
namespace App\Http\Repositories\CommissionsRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\Commissions;

class CommissionsReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Commissions();
    }

}