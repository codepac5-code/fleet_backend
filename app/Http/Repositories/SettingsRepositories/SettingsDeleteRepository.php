<?php
namespace App\Http\Repositories\SettingsRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\Settings;

class SettingsDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Settings();
    }
}