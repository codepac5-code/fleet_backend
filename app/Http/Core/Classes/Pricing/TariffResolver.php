<?php

namespace App\Http\Core\Classes\Pricing;

use App\Http\Core\GeoServices\ShardManager;
use App\Models\OfficeSubServicePrice;
use App\Models\ServiceTariff;
use App\Models\SubService;

class TariffResolver
{
    /**
     * Meter rates sourced from the SUB-SERVICE catalog (the operator's chosen
     * source of truth): the office's `office_sub_service_prices` override when it
     * has one, otherwise the `sub_services` base. `openPrice/kmPrice/minutePrice`
     * are major-unit decimals; the tariff shape is minor units, so ×100. Returns
     * the same array shape as a ServiceTariff so PricingService prices it
     * identically — the bridge that unifies meter pricing onto sub_services
     * without disturbing the pricing engine.
     */
    public function forOfficeSubService(int $officeId, int $subServiceId): ?array
    {
        $sub = SubService::query()->find($subServiceId);

        if ($sub === null) {
            return null;
        }

        $override = $officeId > 0
            ? OfficeSubServicePrice::query()
                ->where('office_id', $officeId)
                ->where('sub_service_id', $subServiceId)
                ->first()
            : null;

        $src = $override ?? $sub;

        return [
            'pricing_style' => self::styleMeter(),
            'currency_code' => ShardManager::currency(),
            'base_minor' => (int) round((float) $src->openPrice * 100),
            'per_km_minor' => (int) round((float) $src->kmPrice * 100),
            'per_minute_minor' => (int) round((float) $src->minutePrice * 100),
            'minimum_minor' => 0,
            'fixed_minor' => 0,
        ];
    }

    /**
     * Resolve the tariff for a booking: prefer the sub-service catalog when the
     * booking is tied to a sub-service, otherwise fall back to the per-office
     * ServiceTariff (backward compatible).
     */
    public function forOfficeServiceOrSub(int $officeId, ?int $subServiceId, ?string $service, string $serviceClass): ?array
    {
        if ($subServiceId !== null && $subServiceId > 0) {
            $tariff = $this->forOfficeSubService($officeId, $subServiceId);

            if ($tariff !== null) {
                return $tariff;
            }
        }

        return $this->forOfficeService($officeId, $service, $serviceClass);
    }

    private static function styleMeter(): string
    {
        return 'meter';
    }

    public function forOffice(int $officeId, string $serviceClass): ?array
    {
        $tariff = ServiceTariff::query()
            ->where('office_id', $officeId)
            ->where('service_class', $serviceClass)
            ->where('is_active', true)
            ->first();

        return $this->shape($tariff);
    }

    public function forOfficeService(int $officeId, ?string $service, string $serviceClass): ?array
    {
        $tariff = ServiceTariff::query()
            ->where('office_id', $officeId)
            ->where('service_class', $serviceClass)
            ->where('is_active', true)
            ->when($service !== null, fn ($q) => $q->where(fn ($w) => $w->where('service', $service)->orWhereNull('service'))->orderByRaw('service is null'))
            ->first();

        return $this->shape($tariff);
    }

    public function offeringOfficeIds(string $service, string $serviceClass): array
    {
        return ServiceTariff::query()
            ->where('service_class', $serviceClass)
            ->where('is_active', true)
            ->where(fn ($w) => $w->where('service', $service)->orWhereNull('service'))
            ->orderBy('office_id')
            ->pluck('office_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function officesForService(string $service): array
    {
        return ServiceTariff::query()
            ->where('is_active', true)
            ->where(fn ($w) => $w->where('service', $service)->orWhereNull('service'))
            ->distinct()
            ->pluck('office_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function classesForService(string $service): array
    {
        return ServiceTariff::query()
            ->where('is_active', true)
            ->where(fn ($w) => $w->where('service', $service)->orWhereNull('service'))
            ->distinct()
            ->pluck('service_class')
            ->all();
    }

    private function shape(?ServiceTariff $tariff): ?array
    {
        if ($tariff === null) {
            return null;
        }

        return [
            'pricing_style' => $tariff->pricing_style,
            'currency_code' => $tariff->currency_code,
            'base_minor' => (int) $tariff->base_minor,
            'per_km_minor' => (int) $tariff->per_km_minor,
            'per_minute_minor' => (int) $tariff->per_minute_minor,
            'minimum_minor' => (int) $tariff->minimum_minor,
            'fixed_minor' => (int) $tariff->fixed_minor,
        ];
    }
}
