<?php
namespace App\Http\Core\Classes;

use Dotenv\Parser\Value;
use Illuminate\Support\Facades\Log;
use Sk\Geohash\Geohash;
use Illuminate\Support\Facades\Redis;

abstract class RedisManagerData
{

    private static $defaultExpiry;

    // public static function __construct(array $config = [], $defaultExpiry = 1800)
    // {
    //     $defaultConfig = [
    //         'host'      => env('REDIS_HOST', '127.0.0.1'),
    //         'port'      => env('REDIS_PORT', 6379),
    //         'database'  => env('REDIS_DB', 0),
    //         'password'  => env('REDIS_PASSWORD', null),
    //         'client'    => env('REDIS_CLIENT', 'phpredis'),
    //     ];

    //     self::connectionConfig = array_merge($defaultConfig, $config);
    //     self::defaultExpiry = $defaultExpiry;

    //     self::initializeRedisConnection();
    // }


    // private function initializeRedisConnection()
    // {
    //     Redis::connection([
    //         'host' => self::connectionConfig['host'],
    //         'port' => self::connectionConfig['port'],
    //         'database' => self::connectionConfig['database'],
    //         'password' => self::connectionConfig['password'],
    //         'client' => self::connectionConfig['client'],
    //     ]);
    // }

    /** ==========  Redis Functions ========== */

    public static function set($key, $value, $expiry = 1800)
    {
        $expiry = $expiry ?? self::$defaultExpiry;
        Redis::setex($key, $expiry, is_array($value) ? json_encode($value) : $value);
    }

    public static function get($key) {
        $value = Redis::get($key);
        return json_decode($value, true) ?? $value; 
    }

    public static function exists($key)
    {
        return Redis::exists($key);
    }

    public static function delete($key)
    {
        Redis::del($key);
    }

    public static function geoAdd($key, $latitude, $longitude, $id, $expiry = 43200)
    {
        Redis::geoadd($key, $longitude, $latitude, $id);
        Redis::expire($key, $expiry);
    }

    public static function getGeoHash($latitude, $longitude, $precision = 6)
    {
        $g = new Geohash();
        return $g->encode($latitude, $longitude, $precision);
    }




    /** ========== Driver's Functions ========== */
    public static function get_drivers_in_area_by_radius($longitude , $latitude , $radius , $area){
        // $areaKey = RedisKeyTemplate::AREA_DRIVERS->generateKey(['area' => $area]);
        return Redis::georadius($area , $longitude , $latitude , $radius , 'km');
    }

    public static function makeDriverOffline($driverId){
        
        $keyDriverArea = RedisKeyTemplate::DRIVER_LOCATION->generateKey(['driverId' => $driverId]);
        if (self::exists($keyDriverArea)) {
            $area = self::get_driver_area( $driverId );
            //   $areaKey = RedisKeyTemplate::AREA_DRIVERS->generateKey(['area' => $area]);
            //   Redis::zrem($areaKey, $driverId);
            Redis::zrem( $area , $driverId );
            // Redis::zrem("all_drivers_locations", $driverId);
            self::delete($keyDriverArea);

            // for driver location map in home page
            self::delete('driver_location:'.$driverId);


            Log::info("make driver offline");
        }
    }

    public static function makeDriverOnline($driverId , $latitude, $longitude, $precision = 6)  {
        RedisManagerData::makeDriverOffline($driverId);
        RedisManagerData::store_driver_area( $driverId , $latitude, $longitude, $precision);
        $area = RedisManagerData::get_driver_area( $driverId );

        Redis::geoadd( $area , $longitude , $latitude , $driverId );

        // Redis::geoadd("all_drivers_locations", $longitude, $latitude, $driverId);
        // Redis::expire("all_drivers_locations", 3600 * 12);
        Redis::expire( $area , 3600 * 12 );
        Log::info("Driver $driverId added to area $area");

        // driver loacation on map
        Redis::hmset("driver_location:".$driverId, [
            'longitude' => $longitude,
            'latitude' =>  $latitude,
          ]);
     // RedisManagerData::makeDriverOffline($driverId);
    }

    public static function store_driver_area( $driverId , $latitude, $longitude, $precision = 6) {
        $key     = RedisKeyTemplate::DRIVER_LOCATION->generateKey(['driverId' => $driverId]);
        $geoHash = self::getGeoHash($latitude, $longitude, $precision );
        self::set($key , $geoHash ,43200 );

        $key     = RedisKeyTemplate::DRIVER_LONGITUDE_LATITUDE->generateKey(['driverId' => $driverId]);
        $data = ['long'=> $longitude, 'lat'=> $latitude];
        self::set($key ,  $data ,43200 );

        return $geoHash;
    }


    public static function get_driver_area( $driverId ) {
        $key     = RedisKeyTemplate::DRIVER_LOCATION->generateKey(['driverId' => $driverId]);
        return self::get($key);
    }
    
    public static function isDriverOnline( $driverId ) {
        $key     = RedisKeyTemplate::DRIVER_LOCATION->generateKey(['driverId' => $driverId]);
        return self::exists($key);
    }
    






    
    /** ========== Order's Functions ========== */

    public static function storeOrderDetails($orderId, array $details , $expiry = 1800){
        $key     = RedisKeyTemplate::ORDER->generateKey(['orderId' => $orderId]);
        self::set($key, $details, $expiry);
    }

    public static function getOrderDetails($orderId){
        $key     = RedisKeyTemplate::ORDER->generateKey(['orderId' => $orderId]);
        return self::get($key);
    }

    public static function deleteOrderDetails($orderId)
    {
        $key     = RedisKeyTemplate::ORDER->generateKey(['orderId' => $orderId]);
        self::delete($key);
    }

    public static function updateOrderDetails($orderId , array $details)
    {

        $redis_key  = RedisKeyTemplate::ORDER->generateKey(['orderId' => $orderId]);
        $order_data = self::get($redis_key);
        foreach ($details as $key => $value)  {
            $order_data[$key] = $value;
        }
        self::set($redis_key, $order_data);
    }

    public static function OrderNotAccepted($orderId)
    {
        $key  = RedisKeyTemplate::ORDER_NOT_ACCEPT->generateKey(['orderId' => $orderId]);
        return self::exists($key);
    }

    public static function setOrderNotAccepted( $orderId , $expiry = 1800)
    {
        $key     = RedisKeyTemplate::ORDER_NOT_ACCEPT->generateKey(['orderId' => $orderId]);
        self::set($key, true, $expiry);
    }

    public static function AcceptOrder( $orderId , $expiry = 1800)
    {
        $key = RedisKeyTemplate::ORDER_NOT_ACCEPT->generateKey(['orderId' => $orderId]);
        if(self::exists($key)){
            self::delete($key);
        }
    }



    //==============<<< SATISTICS  >>==========

    
    //-------------------- Daily System Rides Satistic -----

    public static function get_system_daily_completed_rides(){

    $key = RedisKeyTemplate::System_Daily_Completed_Rides->value;
        if(self::exists($key)){
           return  self::get($key);
        }
        return 0;
    }

    public static function set_system_daily_completed_rides($value){

        $key = RedisKeyTemplate::System_Daily_Completed_Rides->value;
                self::set($key , $value);
            return 0;
        }



        public static function set_system_daily_ongoing_rides($value){
            $key = RedisKeyTemplate::System_Daily_Ongoing_Rides->value;
            self::set($key , $value);
        }

        public static function get_system_daily_ongoing_rides(){
            $key = RedisKeyTemplate::System_Daily_Ongoing_Rides->value;
            if(self::exists($key)){
               return  self::get($key);
            }
            return 0;        
        }

        public static function set_system_daily_pending_rides($value){
            $key = RedisKeyTemplate::System_Daily_Pending_Rides->value;
            self::set($key , $value);
        }
        
        public static function get_system_daily_pending_rides(){
            $key = RedisKeyTemplate::System_Daily_Pending_Rides->value;
            if(self::exists($key)){
               return  self::get($key);
            }
            return 0;        
        }


        //--------- office
        public static function get_office_daily_completed_rides($officeId){

            $key = RedisKeyTemplate::Office_Daily_Completed_Rides->generateKey(['officeId'=>$officeId]);
                if(self::exists($key)){
                   return  self::get($key);
                }
                return 0;
            }
        
        public static function set_office_daily_completed_rides($officeId , $value){
    
            $key = RedisKeyTemplate::Office_Daily_Completed_Rides->generateKey(['officeId'=>$officeId]);
                if(self::exists($key)){
                    self::set($key , $value);
                }
                return 0;
            }
    
    
    
        public static function set_office_daily_ongoing_rides( $officeId, $value , $expiry=86400){
            $key = RedisKeyTemplate::Office_Daily_Ongoing_Rides->generateKey(['officeId'=>$officeId]);
            self::set($key , $value);
        }

        public static function get_office_daily_ongoing_rides($officeId){
            $key = RedisKeyTemplate::Office_Daily_Ongoing_Rides->generateKey(['officeId'=>$officeId]);
            if(self::exists($key)){
                return  self::get($key);
            }
            return 0;        
        }

        public static function set_office_daily_pending_rides( $officeId , $value  , $expiry=86400){
            $key = RedisKeyTemplate::Office_Daily_Pending_Rides->generateKey(['officeId'=>$officeId]);
            self::set($key , $value);
        }
        
        public static function get_office_daily_pending_rides($officeId){
            $key = RedisKeyTemplate::Office_Daily_Pending_Rides->generateKey(['officeId'=>$officeId]);
            if(self::exists($key)){
                return  self::get($key);
            }
            return 0;        
        }



    // -------------------- << Wallet Status >> ------

    //  public static function add_to_system_pending_amount( $value ){
    //     $key = RedisKeyTemplate::System_Pending_Amount->generateKey([]);
    //     $ammount = $value;
    //     if(self::exists($key)){
    //         $ammount =  self::get($key) + $value;
    //     }
    //     self::set($key , $ammount );
    //     return $ammount ;
    //  }


     public static function get_system_pending_amount(){
        $key = RedisKeyTemplate::System_Pending_Amount->value;
        return self::get($key);
     }

     public static function set_system_pending_amount($value , $expiry=86400 ){
        $key = RedisKeyTemplate::System_Pending_Amount->value;
        return self::set($key , $value,$expiry);
     }


     public static function get_office_pending_amount($officeId){
        $key = RedisKeyTemplate::Office_Pending_Amount->generateKey(['officeId'=>$officeId]);
        return self::get($key);
     }


     public static function set_office_pending_amount($officeId , $value, $expiry=86400 ){
        $key = RedisKeyTemplate::Office_Pending_Amount->generateKey(['officeId'=>$officeId]);
        return self::set($key , $value,$expiry);
     }


    //  public static function 
    // case System_Daily_Completed_Rides     = 'system_daily_completed_rides';    
    // case System_Daily_Ongoing_Rides       = 'system_daily_ongoing_rides';  
    // case System_Daily_Pending_Rides       = 'system_daily_pending_rides';    
    


}
