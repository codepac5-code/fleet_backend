<?php
namespace App\Http\Repositories\PublicUserAppSettingRepositories;
use App\Models\{PublicUserAppSetting};

class PublicUserAppSettingRepositoryCaller{

    static public function createRepository(){return (new PublicUserAppSettingCreateRepository());}
    static public function readRepository(){return (new PublicUserAppSettingReadRepository());}
    static public function updateRepository(){return (new PublicUserAppSettingUpdateRepository());}
    static public function deleteRepository(){return (new PublicUserAppSettingDeleteRepository());}
    static public function get_model() : PublicUserAppSetting {return (new PublicUserAppSetting());}


}