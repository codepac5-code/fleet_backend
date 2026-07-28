<?php

namespace App\Http\Services\Panel\Bookings\Logic;

use App\Http\Core\Const\Options\OrderStatus;

class BookingStatus
{
    private const LABELS = [
        'Pending'           => ['ar' => 'معلّق', 'en' => 'Pending'],
        'Search on driver'  => ['ar' => 'بحث عن سائق', 'en' => 'Searching for driver'],
        'Ongoing'           => ['ar' => 'جارٍ', 'en' => 'Ongoing'],
        'In Progress'       => ['ar' => 'قيد التنفيذ', 'en' => 'In progress'],
        'Hold'              => ['ar' => 'معلّق مؤقتاً', 'en' => 'On hold'],
        'Completed'         => ['ar' => 'مكتمل', 'en' => 'Completed'],
        'Cancelled'         => ['ar' => 'ملغى', 'en' => 'Cancelled'],
        'scheduled'         => ['ar' => 'مجدول', 'en' => 'Scheduled'],
    ];

    public static function settable(): array
    {
        $keys = [
            OrderStatus::$Pending,
            OrderStatus::$OnGoing,
            OrderStatus::$InProgress,
            OrderStatus::$Hold,
            OrderStatus::$Completed,
            OrderStatus::$Cancelled,
        ];

        $options = [];
        foreach ($keys as $key) {
            $options[$key] = self::label($key);
        }

        return $options;
    }

    public static function label(?string $status): string
    {
        if ($status === null || $status === '') {
            return '—';
        }

        $entry = self::LABELS[$status] ?? null;

        return $entry ? textByLanguage($entry['ar'], $entry['en']) : $status;
    }
}
