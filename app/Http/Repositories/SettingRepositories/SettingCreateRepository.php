<?php
namespace App\Http\Repositories\SettingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\Setting;

class SettingCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Setting();
    }
}