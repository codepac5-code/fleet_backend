<?php
namespace App\Http\Repositories\SliderRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\Slider;

class SliderReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Slider();
    }

}
