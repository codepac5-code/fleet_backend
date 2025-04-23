<?php
namespace App\Http\Repositories\FrontendSettingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\FrontendSetting;

class FrontendSettingCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new FrontendSetting();
    }
}