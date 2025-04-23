<?php
namespace App\Http\Repositories\SliderRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\Slider;

class SliderDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Slider();
    }
}
