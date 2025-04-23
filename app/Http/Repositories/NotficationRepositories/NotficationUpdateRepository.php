<?php
namespace App\Http\Repositories\NotficationRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\Notfication;

class NotficationUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new Notfication();
    }

}