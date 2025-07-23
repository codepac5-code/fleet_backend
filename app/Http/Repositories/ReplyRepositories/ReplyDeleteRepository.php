<?php
namespace App\Http\Repositories\ReplyRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\Reply;

class ReplyDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Reply();
    }
}