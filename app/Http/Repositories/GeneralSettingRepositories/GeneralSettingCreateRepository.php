<?php
namespace App\Http\Repositories\GeneralSettingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\GeneralSetting;

class GeneralSettingCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new GeneralSetting();
    }
}