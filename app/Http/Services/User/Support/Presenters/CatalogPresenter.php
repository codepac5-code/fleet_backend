<?php

namespace App\Http\Services\User\Support\Presenters;

use App\Models\Service;
use App\Models\SubService;

class CatalogPresenter
{
    public static function service(Service $service): array
    {
        return [
            'id' => (int) $service->id,
            'title' => self::localized($service->title, $service->title_en) ?: $service->name,
            'description' => self::localized($service->description, $service->description_en),
            'image' => self::image($service->image),
            'icon' => $service->icon ?? null,
            'badge' => $service->badge ?? null,
            'sort_order' => (int) ($service->sort_order ?? 0),
            'status' => (bool) $service->status,
            'travel_service' => (bool) $service->travel_service,
        ];
    }

    public static function serviceClass(SubService $class): array
    {
        return [
            'id' => (int) $class->id,
            'serviceId' => (int) $class->serviceId,
            'name' => self::localized($class->name, $class->name_en) ?: $class->name,
            'description' => self::localized($class->description, $class->description_en),
            'image' => self::image($class->image),
            'icon' => $class->icon ?? null,
            'badge' => $class->badge ?? null,
            'sort_order' => (int) ($class->sort_order ?? 0),
            'status' => (bool) $class->status,
            'is_travel' => (bool) $class->is_travel,
            // Indicative pricing inputs so the class list can show a real
            // number instead of 0. The authoritative per-office fare still
            // comes from the tariff engine at office-search / booking time.
            'openPrice' => (float) ($class->openPrice ?? 0),
            'kmPrice' => (float) ($class->kmPrice ?? 0),
            'minutePrice' => (float) ($class->minutePrice ?? 0),
        ];
    }

    private static function localized(?string $native, ?string $english): ?string
    {
        if ((app()->getLocale() ?: 'en') === 'en') {
            return $english !== null && $english !== '' ? $english : $native;
        }

        return $native;
    }

    private static function image(?string $image): ?string
    {
        if ($image === null || $image === '') {
            return null;
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        return asset('storage/' . ltrim($image, '/'));
    }
}
