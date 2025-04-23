<?php
namespace App\Http\Repositories\FrontendSettingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\FrontendSetting;

class FrontendSettingUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new FrontendSetting();
    }

}