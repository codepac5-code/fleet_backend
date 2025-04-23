<?php
namespace App\Http\Core\Classes;


use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ImageManager
{
    protected $disk ;
    protected $default_photos = 
    [
        'profile' => '\storage\images\system\user_no_photo4a305405-ad0b-473a-a40c-456397a18b96.png',
    ];

    public function __construct($disk = 'public')
    {
        $this->disk = $disk;
    }
    

    public function upload($file, $path = 'images')
    {
        $uniqueName = Str::uuid() . '.' . $file->getClientOriginalExtension();

        $filePath = $file->storeAs($path, $uniqueName, $this->disk);

        return $filePath;
    }

    public function withStorge($filePath)
    {
        return "/"."storage/" . $filePath;
    }

    public function default_profile_photo()
    {
        return $this->default_photos['profile']; 
    }

    public function default_photo()
    {
        return "/"."storage/" .'123456789';
    }


    public function getOnlineUrl($filePath)
    {
        return Storage::disk($this->disk)->url($filePath); 
    }


    public function delete($filePath)
    {
    //    if( ! is_default_photo()){
        return Storage::disk($this->disk)->delete($filePath);
       //}
    }
}
