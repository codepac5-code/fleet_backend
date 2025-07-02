<?php
namespace App\Http\Core\SubSystems\RedisDatabase\RedisModels\Driver;

use App\Http\Core\Classes\RedisManagerData;

abstract class DriverRedisModel  {


    public static function storeDriverServices($driverId , array $services = []){
        $key = DriverRedisKeies::DriverServices->generateKey(['driverId'=>$driverId]);
        RedisManagerData::set($key , $services ,86400 );
    }

    public static function deleteDriverServices($driverId){
        $key = DriverRedisKeies::DriverServices->generateKey(['driverId'=>$driverId]);
        if(RedisManagerData::exists($key)){
            RedisManagerData::delete($key);
        }
    }

    public static function getDriverServices($driverId) :array {
        $key = DriverRedisKeies::DriverServices->generateKey(['driverId'=>$driverId]);
        if (RedisManagerData::exists($key)){
            return RedisManagerData::get($key);
        }
        return [];
    }
}