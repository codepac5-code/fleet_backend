<?php
namespace App\Http\Repositories\PublicDriverAppSettingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\PublicDriverAppSetting;

class PublicDriverAppSettingDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new PublicDriverAppSetting();
    }
}