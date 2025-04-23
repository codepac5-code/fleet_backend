<?php
namespace App\Http\Repositories\NotficationRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\Notfication;

class NotficationReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Notfication();
    }

}