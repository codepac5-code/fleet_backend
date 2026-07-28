<?php

namespace App\Http\Core\Const\Support;

class SupportLayer
{
    const OFFICE = 'office';
    const FLEETOS = 'fleetos';

    const CATEGORY_LAYER = [
        'past_trip' => self::OFFICE,
        'lost_item' => self::OFFICE,
        'office_complaint' => self::OFFICE,
        'safety_report' => self::OFFICE,
        'refund' => self::FLEETOS,
        'payment' => self::FLEETOS,
        'safety' => self::FLEETOS,
        'policy' => self::FLEETOS,
        'sos' => self::FLEETOS,
    ];

    public static function forCategory(string $category): string
    {
        return self::CATEGORY_LAYER[$category] ?? self::FLEETOS;
    }
}
