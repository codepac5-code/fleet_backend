<?php
namespace App\Http\Repositories\FrontendSettingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\FrontendSetting;

class FrontendSettingDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new FrontendSetting();
    }
}