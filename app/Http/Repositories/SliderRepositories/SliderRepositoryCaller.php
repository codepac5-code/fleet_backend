<?php
namespace App\Http\Repositories\SliderRepositories;

use App\Http\Repositories\SliderRepositories\SliderReadRepository;
use App\Http\Repositories\SliderRepositories\SliderCreateRepository;
use App\Http\Repositories\SliderRepositories\SliderDeleteRepository;
use App\Http\Repositories\SliderRepositories\SliderUpdateRepository;

class SliderRepositoryCaller{

    static public function createRepository(){return (new SliderCreateRepository());}
    static public function readRepository(){return (new SliderReadRepository());}
    static public function updateRepository(){return (new SliderUpdateRepository());}
    static public function deleteRepository(){return (new SliderDeleteRepository());}

}
