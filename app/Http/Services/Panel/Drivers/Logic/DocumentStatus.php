<?php

namespace App\Http\Services\Panel\Drivers\Logic;

class DocumentStatus
{
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    private const MAP = [
        self::PENDING  => ['ar' => 'قيد المراجعة', 'en' => 'Pending', 'tone' => 'warning'],
        self::APPROVED => ['ar' => 'مقبول', 'en' => 'Approved', 'tone' => 'success'],
        self::REJECTED => ['ar' => 'مرفوض', 'en' => 'Rejected', 'tone' => 'danger'],
    ];

    public static function all(): array
    {
        return [self::PENDING, self::APPROVED, self::REJECTED];
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::MAP as $key => $meta) {
            $options[$key] = textByLanguage($meta['ar'], $meta['en']);
        }

        return $options;
    }

    public static function label(?string $status): string
    {
        $entry = self::MAP[$status] ?? null;

        return $entry ? textByLanguage($entry['ar'], $entry['en']) : (string) $status;
    }

    public static function tone(?string $status): string
    {
        return self::MAP[$status]['tone'] ?? 'gray';
    }
}
