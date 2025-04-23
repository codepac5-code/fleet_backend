<?php
namespace App\Http\Repositories\SliderRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\Slider;

class SliderCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Slider();
    }
}
