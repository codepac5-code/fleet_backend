<?php
namespace App\Http\Repositories\GeneralSettingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\GeneralSetting;

class GeneralSettingDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new GeneralSetting();
    }
}