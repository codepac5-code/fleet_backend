<?php
namespace App\Http\Repositories\StateRepositories;
use App\Repositories\basic\DeleteRepository;
use App\Models\State;

class StateDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new State();
    }
}