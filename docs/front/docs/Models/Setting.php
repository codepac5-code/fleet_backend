<?php
namespace App\Models;


use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    use HasFactory;
    protected $table = "settings";
    protected $primaryKey = "id";
    protected $fillable = ['type' ,'name','key','value_ar','value_en' ,'value', 'officeId'];
    public $timestamps = false;


    protected $casts = [
        'type' => 'string',
        'key'   => 'string',
        'value' => 'string',
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

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::updated(function () {
            self::flushCache();
        });

        static::created(function () {
            self::flushCache();
        });

        static::deleted(function () {
            self::flushCache();
        });
    }
    public static function getValueByKey($key,$type)
    {
        $setting = self::where('key', $key);

        if ($type !== null) {
            $setting->where('type', $type);
        }

        $setting = $setting->first();

        if ($setting) {
            if ($key === 'privacy_policy' || $key === 'terms_condition'|| $key === 'help_support' || $key === 'refund_cancellation_policy' || $key === 'data_deletion_request' || $key === 'earning-setting' || $key === 'userdashboard-setting') {
                return $setting->value;
            } else {
                return json_decode($setting->value);
            }
        }

        return null;

    }
}
