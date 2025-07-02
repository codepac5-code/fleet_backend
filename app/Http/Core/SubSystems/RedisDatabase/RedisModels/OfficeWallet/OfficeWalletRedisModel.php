<?php
namespace App\Http\Core\SubSystems\RedisDatabase\RedisModels\OfficeWallet;

use App\Http\Core\Classes\HelperClasses\FromTo;
use App\Http\Core\Classes\RedisManagerData;
use Illuminate\Support\Facades\Redis;

abstract class OfficeWalletRedisModel {

    public static function addBalanceValueByBalanceStatus( int $officeId, string $balance_status , $value){
        $key =  OfficeWalletRedisKeies::Balance->generateKey(['status'=>$balance_status , 'officeId'=>$officeId]);
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


    public static function getBalanceValueByStatus($officeId,string $balance_status ){
        $key =  OfficeWalletRedisKeies::Balance->generateKey(['status'=>$balance_status , 'officeId'=>$officeId]);
        if(RedisManagerData::exists($key)){
          return  RedisManagerData::get($key);
        }
        return 0;
    }

    public static function moveBalance( $officeId ,string $from ,string $to , $balance ) :FromTo{
        $from_balance = OfficeWalletRedisModel::addBalanceValueByBalanceStatus($officeId ,$from , -$balance);
        $to_balance  = OfficeWalletRedisModel::addBalanceValueByBalanceStatus($officeId , $to , +$balance);
        return new FromTo( $from_balance , $to_balance );
    } 




}