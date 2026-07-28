<?php

namespace App\Http\Services\Panel\Wallet\Logic;

use App\Models\Driver;
use App\Models\FleetOffice;
use App\Models\Office;
use App\Models\User;

class PartyLabel
{
    private const MAP = [
        Office::class      => ['ar' => 'مكتب', 'en' => 'Office', 'icon' => 'bi-building'],
        Driver::class      => ['ar' => 'سائق', 'en' => 'Driver', 'icon' => 'bi-taxi-front'],
        User::class        => ['ar' => 'عميل', 'en' => 'Customer', 'icon' => 'bi-person'],
        FleetOffice::class => ['ar' => 'المنصّة', 'en' => 'Platform', 'icon' => 'bi-hexagon-fill'],
    ];

    public static function label(?string $type): string
    {
        if (! $type) {
            return '—';
        }

        $entry = self::MAP[$type] ?? null;

        return $entry ? textByLanguage($entry['ar'], $entry['en']) : class_basename($type);
    }

    public static function icon(?string $type): string
    {
        return self::MAP[$type]['icon'] ?? 'bi-circle';
    }
}
