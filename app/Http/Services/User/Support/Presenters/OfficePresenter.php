<?php

namespace App\Http\Services\User\Support\Presenters;

use App\Models\Office;

class OfficePresenter
{
    private const PALETTES = ['a', 'b', 'c', 'd', 'e'];

    public static function card(Office $office): array
    {
        $name = (string) ($office->displayName ?: $office->officeName);

        return [
            'id' => (int) $office->id,
            'officeName' => $name,
            'initials' => $office->initials ?: self::initials($name),
            'palette' => $office->palette ?: self::palette((int) $office->id),
            'logo' => self::logo($office->logo),
            'rating' => (float) ($office->rating ?? 0),
            'ratings_count' => (int) ($office->ratings_count ?? 0),
            'is_verified' => (bool) $office->is_verified,
            'is_monitored' => (bool) $office->is_monitored,
            'on_time_percentage' => $office->on_time_percentage !== null ? (float) $office->on_time_percentage : null,
            'avg_response_minutes' => $office->avg_response_minutes !== null ? (int) $office->avg_response_minutes : null,
            'contactNumber' => $office->contactNumber,
            'country' => $office->country,
            'city' => $office->city,
            'region' => $office->region,
            'address' => $office->address,
            'lat' => $office->lat !== null ? (float) $office->lat : null,
            'lng' => $office->lng !== null ? (float) $office->lng : null,
            'working_hours' => $office->working_hours,
            'ratingExcellent' => (int) ($office->ratingExcellent ?? 0),
            'ratingGood' => (int) ($office->ratingGood ?? 0),
            'ratingAverage' => (int) ($office->ratingAverage ?? 0),
            'ratingPoor' => (int) ($office->ratingPoor ?? 0),
        ];
    }

    private static function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $letters = '';

        foreach (array_slice($parts, 0, 2) as $part) {
            $letters .= mb_substr($part, 0, 1);
        }

        return mb_strtoupper($letters !== '' ? $letters : mb_substr($name, 0, 2));
    }

    private static function palette(int $id): string
    {
        return self::PALETTES[$id % count(self::PALETTES)];
    }

    private static function logo(?string $logo): ?string
    {
        if ($logo === null || $logo === '') {
            return null;
        }

        if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://')) {
            return $logo;
        }

        return asset('storage/' . ltrim($logo, '/'));
    }
}
