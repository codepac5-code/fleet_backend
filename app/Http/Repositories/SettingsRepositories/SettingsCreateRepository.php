<?php
namespace App\Http\Repositories\SettingsRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\Settings;

class SettingsCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Settings();
    }
}