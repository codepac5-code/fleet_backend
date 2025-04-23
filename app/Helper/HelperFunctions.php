<?php

use App\Helper\Helper;
use Sk\Geohash\Geohash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Http\Core\Const\Options\Guard;
use Illuminate\Queue\Console\RetryCommand;
use Illuminate\Support\Facades\Notification;
use App\Http\Core\Const\Messages\ErrorMessages;

function upload_and_save_file($model, $file, $collection = 'default') {
    $randomString = bin2hex(random_bytes(30));
    $finalName = time() . "_" . $randomString . "." . $file->getClientOriginalExtension();

    $mediaItem = $model->addMedia($file)->usingFileName($finalName)->toMediaCollection($collection);
    return $mediaItem->getUrl();
}
        // check Password function
        if (! function_exists('checkPassWord')) {
            function checkPassWord($password , $hashedPassword) :bool {
                return Hash::check($password, $hashedPassword);
            }
        }
        
        function getAuthUser( $guardName = null ) {
            if($guardName != null){
                return  Auth::guard($guardName)->user();
            }

            return Auth::user();
         }

         function check_auth_user_has_role( $roles = [] ) {
            return Auth::user()->hasAnyRole($roles);
         }


        function convert_response_to_json() : void {
            request()->headers->set('Accept', 'application/json');
        }

        //    $request->user()->token()->revoke();


        // create Token function
        function getToken($user) :String {
         return $user->createToken('API Token')->accessToken;
        }


        // hash data function
        function hashData($data) : String{
            return Hash::make($data);
        }

        // make Exception function
        function make_exception( $message , $code = 500 ){
            throw new Exception(  $message , $code );
        }
        

        function setCatch($key, $value) : void{

            if(!Cache::put($key, $value, 600)){
                make_exception(ErrorMessages::getKey(ErrorMessages::$SomeThingWentWrong));
            }

        }
        


    function getCatch($key){
        return Cache::get($key);
    }

    function fireEvent($event ){
        event($event);
    }

    function make_Driver_offline($driverId) {
        $key_driver_area ='driver.'.$driverId.':area';
        if( key_exists_in_redis($key_driver_area) ){
                $area    = Redis::get( $key_driver_area);
                $areaKey = "drivers-area:$area";
                Redis::zrem($areaKey, $driverId);
                Redis::del( $key_driver_area);
            }
    }

    function order_not_accepted($orderId) {
    $key = 'order.'.$orderId.':notAcceptedByDriver';
    return Redis::exists($key);
    }

    function store_to_order_details_to_redis($orderId , $array , $ex = 1800){
        $key = 'order.'.$orderId.':notAcceptedByDriver';
        storeArrayToRedis($key , $array, $ex  );
    }


    function get_order_details_from_redis($orderId){
        $key = 'order.'.$orderId.':notAcceptedByDriver';
        return  readArrayFromRedis($key);
    }


    function delete_order_details_from_redis($orderId){
        $key = 'order.'.$orderId.':notAcceptedByDriver';
        Redis::del($key);
    }


    function select_by_language($ar_selected = ['*'] , $en_selected =['*'] ):Array{
        switch(app()->getLocale()){
            case 'ar': return $ar_selected;
            case 'en': return $en_selected;
            default  : return ['*'];
        }
    }
    // function make_order_accepted($orderId) {
    //     $key_driver_area ='driver.'.$driverId.':area';
    //     if( key_exists_in_redis($key_driver_area) ){
    //             $area    = Redis::get( $key_driver_area);
    //             $areaKey = "drivers-area:$area";
    //             Redis::zrem($areaKey, $driverId);
    //             Redis::del( $key_driver_area);
    //         }
    // }

    function make($driverId) {
        $key_driver_area ='driver.'.$driverId.':area';
        if( key_exists_in_redis($key_driver_area) ){
                $area    = Redis::get( $key_driver_area);
                $areaKey = "drivers-area:$area";
                Redis::zrem($areaKey, $driverId);
                Redis::del( $key_driver_area);
            }
    }

    


    function makeSignature($body) {
            $json = json_encode($body);
            openssl_sign($json, $sign , Helper::$private_key , OPENSSL_ALGO_SHA256);
            $x_signature = base64_encode($sign);
            return $x_signature;
    }


    function beginTransaction(){
        DB::beginTransaction();
    }

    function commitTransaction(){
        DB::commit();
    }

    function rollbackTransaction(){
        DB::rollback();
    }


    function storeArrayToRedis($key , $array, $ex = 1800 ){
        Redis::setex($key , $ex, json_encode($array));
    }

    function storeToRedis( $key , $data , $ex = 1800) {
        Redis::setex( $key , $ex , $data );
    }

    function readFromRedis( $key ){
        return Redis::get( $key );
    }

    function key_exists_in_redis( $key ){
        return Redis::exists( $key );
    }

    function get_geohash_area($latitude , $longitude ){
        $g = new Geohash();
        return $g->encode($latitude, $longitude, 6);
    }

    function geoadd_new( $areaKey, $latitude , $longitude  , $id){
        Redis::geoadd($areaKey , $longitude , $latitude, $id);
        Redis::expire($areaKey , 3600 * 12 );
    }




    
    function readArrayFromRedis($key ) : array {
       return json_decode(Redis::get($key ), true);
    }

    function create_file_and_add_content($path , $content) :bool{
            if (file_exists($path)) {
                return false;
             }
             else {
                $fh = fopen($path, 'wb');
                fwrite($fh, $content);
                fclose($fh);
                chmod($path, 0777);
                return true;
            }

        }

    function authenticate( $credentials ,  $remember = false , $guardName = 'user' ):bool{
       return Auth::guard($guardName)->attempt($credentials, $remember);
    }



        // function authSession($force=false){
        //     $session = new \App\Models\User;
        //     if($force){
        //         $user = \Auth::user()->getRoleNames();
        //         \Session::put('auth_user',$user);
        //         $session = \Session::get('auth_user');
        //         return $session;
        //     }
        //     if(\Session::has('auth_user')){
        //         $session = \Session::get('auth_user');
        //     }else{
        //         $user = \Auth::user();
        //         \Session::put('auth_user',$user);
        //         $session = \Session::get('auth_user');
        //     }
        //     return $session;
        // }

        function add_spacename_and_function($path , $namespace ,$function) :bool{
            if (!file_exists($path)) {
                echo "file not exit";
                return false;
             }
             else {
                $lines = file($path);
                $fileContent = '';

                $inClass = false;
                $not_read = true;
                if ($lines === false) {
                    echo "Error reading the file.";
                } else {
                    foreach ($lines as $line) {
                        if (!$inClass && strpos(strtolower($line), "class") !== false) {
                            $fileContent .= $namespace;
                            $fileContent .= $line . "\n";
                            $fileContent .="\t".$function;
                            $inClass = true;
                            if($inClass && $not_read && strpos(strtolower($line), "{")){
                                $not_read = false;
                                // $fileContent .= $line;
                                // $fileContent .= $content;
                            }
                        }
                        else{

                            if($inClass && $not_read && strpos(strtolower($line), "{")){
                                $not_read = false;
                                $fileContent .= $line;
                                $fileContent .= $function;
                            }
                            else{
                            $fileContent .=$line;
                            }
                        }

                    }
                }
                // $fr = fopen($path, 'r');
                // $fileRead = file_get_contents($path);
                $fh = fopen($path, 'w');
                // $content = substr($fileRead,0,strrpos($fileRead, '}'))."\n".$content."\n}";
                fwrite($fh, $fileContent);
                fclose($fh);
                chmod($path, 0777);
                return true;
            }

        }



        function createDirectories($path , $separator) : string {
          //  $path = substr($path, 1);
            $directories = explode($separator, $path);
            $currentPath = '';
            $first = true;
           // print_r($directories);
            foreach ($directories as $directory) {
                if ($directory !== '') {
                    $first ? $currentPath .= $directory : $currentPath .= $separator . $directory;
                    $first = false;
                    if (!is_dir($currentPath)) {
                        mkdir($currentPath, 0777, true);
                    }
                }
            }
            return $currentPath."\\";
        }


//         function comman_message_response( $message, $status_code = 200){
//             return response()->json( [ 'message' => $message ], $status_code );
//         }

//         function comman_custom_response( $response, $status_code = 200 ){
//             return response()->json($response,$status_code);
//         }


//         function demoUserPermission(){
//             if(\Auth::user()->hasAnyRole(['demo_admin'])){
//                 return true;
//             }else{
//                 return false;
//             }
//         }



// function getSingleMedia($model, $collection = 'profile_image', $skip=true   ){
//     if (!\Auth::check() && $skip) {
//         return asset('images/user/user.png');
//     }
//     $media = null;
//     if ($model !== null) {
//         $media = $model->getFirstMedia($collection);
//     }

//     if (getFileExistsCheck($media)) {
//         return $media->getFullUrl();
//     }else{

//         switch ($collection) {
//             case 'image_icon':
//                 $media = asset('images/user/user.png');
//                 break;
//             case 'profile_image':
//                 $media = asset('images/user/user.png');
//                 break;
//             case 'service_attachment':
//                 $media = asset('images/default.png');
//                 break;
//             case 'site_logo':
//                 $media = asset('images/fleet.png');
//                 break;
//             case 'site_favicon':
//                 $media = asset('images/favicon.png');
//                 break;
//             case 'app_image':
//                 $media = asset('images/frontend/mb-serv-1.png');
//                 break;
//             case 'app_image_full':
//                 $media = asset('images/frontend/mb-serv-full.png');
//                 break;
//             case 'footer_logo':
//                 $media = asset('landing-images/logo/logo.png');
//                 break;
//             case 'logo':
//                 $media = asset('images/fleet.png');
//                 break;
//             case 'favicon':
//                 $media = asset('images/favicon.png');
//                 break;
//             case 'loader':
//                 $media = asset('images/loader.gif');
//                 break;
//             default:
//                 $media = asset('images/default.png');
//                 break;
//         }
//         return $media;
//     }
// }


// function imageSession($type='get'){
//     if(\Session::get('images_data') == ''){
//         $type='set';
//     }
//     switch ($type){
//         case "set" :
//             $settings = \App\Models\Setting::where('type','theme-setup')->where('key','theme-setup')->first();
//             \Session::put('images_data',$settings);
//             break;
//         default :
//             break;
//     }
//     return \Session::get('images_data');
// }

// function getFileExistsCheck($media){
//     $mediaCondition = false;

//     if($media) {
//         if($media->disk == 'public') {
//             $mediaCondition = file_exists($media->getPath());
//         } else {
//             $mediaCondition = \Storage::disk($media->disk)->exists($media->getPath());
//         }
//     }

//     return $mediaCondition;
// }
