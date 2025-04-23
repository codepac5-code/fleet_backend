<?php
namespace App\Http\Repositories\FrontendSettingRepositories;
use App\Models\{FrontendSetting};

class FrontendSettingRepositoryCaller{

    static public function createRepository(){return (new FrontendSettingCreateRepository());}
    static public function readRepository(){return (new FrontendSettingReadRepository());}
    static public function updateRepository(){return (new FrontendSettingUpdateRepository());}
    static public function deleteRepository(){return (new FrontendSettingDeleteRepository());}
    static public function get_model() : FrontendSetting {return (new FrontendSetting());}


}