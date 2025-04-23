<?php
namespace App\Http\Repositories\PublicDriverAppSettingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\PublicDriverAppSetting;

class PublicDriverAppSettingUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new PublicDriverAppSetting();
    }

}