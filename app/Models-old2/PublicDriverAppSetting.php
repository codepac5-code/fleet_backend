<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Country;
use PublicDriverAppSetting;

class PublicDriverAppSetting extends Model
{
    use HasFactory;

    protected $table = "public_driver_app_settings";
    protected $primaryKey = "id";
    protected $fillable = ['type' ,'name','key','ar_value' , 'en_value'];
    public    $timestamps = false;


    protected $casts = [
        'type'     => 'string',
        'key'      => 'string',
        'ar_value' => 'string',
        'en_value' => 'string',
    ];

    // public function country()
    // {
    //     return $this->belongsTo(Country::class,'value','id')
    //         ->withDefault(function () { return (object) []; });
    // }
    public static function getAllSettings()
    {
        return Cache::rememberForever('settings.all', function () {
            return self::all();
        });
    }

    /**
     * Flush the cache.
     */
    public static function flushCache()
    {
        Cache::forget('settings.all');
    }
}
