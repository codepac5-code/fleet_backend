<?php
namespace App\Http\Core\Const\Selected;

class SelectByLanguage
{
    public static function subService(): array
    {
        return self::select_by_language(
            [
                'id',
                'name',
                'image',
                'status',
                'description',
                'openPrice',
                'kmPrice',
                'minutePrice',
                'serviceId',
            ],
            [
                'id',
                'image',
                'status',
                'openPrice',
                'kmPrice',
                'minutePrice',
                'serviceId',
                'name_en as name',
                'description_en as description',
            ]
        );
    }

    public static function paymentMethod(): array
    {
        return self::select_by_language(
            [
                'name',
                'id',
                'type'
            ],
            [
                'name_en as name',
                'id',
                'type'
            ]
        );
    }

        public static function help_suggestions(): array
    {
        return self::select_by_language(
            [
                'title',
                'description',
                'isActive',
                'priority',
                'category',
                'created_by',
                'target_user',
            ],
            [
                    'title_en as title',
                    'description',
                    'isActive',
                    'priority',
                    'category',
                    'created_by',
                    'target_user',
            ]
        );
    }

    public static function select_by_language($ar_selected = ['*'], $en_selected = ['*']): array
    {
        switch (app()->getLocale()) {
            case 'ar':
                return $ar_selected;
            case 'en':
                return $en_selected;
            default:
                return ['*'];
        }
    }
}
