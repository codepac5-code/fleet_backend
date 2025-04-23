<?php
namespace App\Http\Core\SubSystems\RedisDatabase\RedisModels;

use Illuminate\Database\Eloquent\Model;
use Mockery\Matcher\Any;

interface RedisModel {
    
    public static function insert($id , $data ) : bool;
    public static function update($id , $data ) : bool;
    public static function delete($id) : bool;
    public static function get($id);


}