<?php
namespace App\Http\Core\SubSystems\RedisDatabase\RedisModels\FleetWallet;

use App\Http\Core\Classes\HelperClasses\FromTo;
use App\Http\Core\Classes\RedisManagerData;
use Illuminate\Support\Facades\Redis;

abstract class FleetWalletRedisModel {

    public static function addBalanceValueByBalanceStatus(string $balance_status , $value){
        $key =  FleetWalletRedisKeies::Balance->generateKey(['status'=>$balance_status]);
        if(RedisManagerData::exists($key)){
            $balance = RedisManagerData::get($key);
            $new_balance = $balance + $value;
            if($new_balance < 0)
            {
                $new_balance = 0;
            }
            Redis::set($key ,$new_balance);
            return $new_balance;
        }
        if($value < 0 ){
            $value = 0;
        }
        Redis::set($key,$value);
        return $value;
    }


    public static function getBalanceValueByStatus(string $balance_status ){
        $key =  FleetWalletRedisKeies::Balance->generateKey(['status'=>$balance_status]);
        if(RedisManagerData::exists($key)){
          return   RedisManagerData::get($key); // balance
        }
        return 0;
    }

    public static function moveBalance(string $from ,string $to , $balance ) :FromTo{
        $from_balance = FleetWalletRedisModel::addBalanceValueByBalanceStatus($from , -$balance);
        $to_balance  = FleetWalletRedisModel::addBalanceValueByBalanceStatus($to , +$balance);
        
        return new FromTo( $from_balance , $to_balance );
    } 




}