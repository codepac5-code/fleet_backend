<?php
namespace App\Http\Repositories\SettingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\Setting;

class SettingDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Setting();
    }
}