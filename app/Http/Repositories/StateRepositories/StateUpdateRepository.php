<?php
namespace App\Http\Repositories\StateRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\State;

class StateUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new State();
    }

}