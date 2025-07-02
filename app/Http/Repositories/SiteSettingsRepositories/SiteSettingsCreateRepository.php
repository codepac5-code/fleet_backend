<?php
namespace App\Http\Repositories\SiteSettingsRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\SiteSettings;

class SiteSettingsCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new SiteSettings();
    }
}