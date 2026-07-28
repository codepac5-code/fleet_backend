<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SiteFaq;

class SiteFaq extends Model
{
    protected $connection = 'global';

    protected $table = 'site_faqs';

    protected $fillable = ['question_en', 'question_ar', 'answer_en', 'answer_ar', 'sort', 'is_active'];

    protected $casts = [
        'sort' => 'integer',
        'is_active' => 'boolean',
    ];

    public static function active()
    {
        try {
            return self::query()->where('is_active', true)->orderBy('sort')->orderBy('id')->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }
}
