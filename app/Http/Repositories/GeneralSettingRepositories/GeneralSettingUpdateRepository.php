<?php
namespace App\Http\Repositories\GeneralSettingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\GeneralSetting;

class GeneralSettingUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new GeneralSetting();
    }

}