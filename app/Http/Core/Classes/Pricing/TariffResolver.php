<?php

namespace App\Http\Core\Classes\Pricing;

use App\Http\Core\GeoServices\ShardManager;
use App\Models\OfficeSubServicePrice;
use App\Models\ServiceTariff;
use App\Models\SubService;

/**
 * Resolves an office's pricing for a ride. There are THREE stores, with a
 * defined hierarchy — kept explicit here because they were drifting:
 *
 *  1. `sub_services`            — base meter rates (open/km/minute) per class.
 *  2. `office_sub_service_prices` — an office's override of (1). This pair is the
 *     SOURCE OF TRUTH for the rider marketplace + booking pipeline (meter cards
 *     price through {@see \App\Http\Core\Classes\Ride\MeterService}, and
 *     {@see forOfficeSubService} bridges it into the tariff shape). Edited on the
 *     legacy "my-services" screen.
 *  3. `service_tariffs` (ServiceTariff) — a LEGACY per-(office, service_class)
 *     store, now only a FALLBACK: it prices a path when the office has NOT
 *     published a matching sub-service price. It is also still what marketplace
 *     discovery ({@see offeringOfficeIds}) reads.
 *
 * UNIFIED PRECEDENCE (operator decision): `office_sub_service_prices` (an
 * office's own price, falling back to the `sub_services` catalog base) is the
 * SOURCE OF TRUTH for meter pricing on EVERY booking path — the office controls
 * its prices. {@see forOfficeServiceOrSub} resolves the sub-service even when the
 * caller passes only a `service_class` string (mapping it to a sub-service the
 * office actually offers), and falls back to ServiceTariff only when the office
 * publishes no matching sub-service price. So an office that set a price on its
 * "my services" screen is billed from THAT everywhere (marketplace, booking,
 * manual booking, change-office, live meter, end-trip reconcile).
 *
 * FIXED (Travel) corridor pricing is separate again: the flat city-to-city price
 * lives in `travel_routes` and is read by
 * {@see \App\Http\Core\Classes\Ride\FixedTripService}, NOT here. It is edited on
 * the new-panel "Fixed corridors" screen.
 */
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

        // An enabled row with no rates means "offer it at the catalog price",
        // not "charge zero".
        if ($override !== null && ! $override->isPriceOverride()) {
            $override = null;
        }

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
     * Resolve the tariff for a booking. The office's own sub-service price is the
     * truth: use the explicit sub-service when the caller carries its id, else
     * map the `service_class` to a sub-service the office actually offers. Only
     * when the office publishes no matching sub-service price does this fall back
     * to the legacy per-office ServiceTariff.
     */
    public function forOfficeServiceOrSub(int $officeId, ?int $subServiceId, ?string $service, string $serviceClass): ?array
    {
        if ($subServiceId === null || $subServiceId <= 0) {
            $subServiceId = $this->offeredSubServiceId($officeId, $service, $serviceClass);
        }

        if ($subServiceId !== null && $subServiceId > 0) {
            $tariff = $this->forOfficeSubService($officeId, $subServiceId);

            if ($tariff !== null) {
                return $tariff;
            }
        }

        return $this->forOfficeService($officeId, $service, $serviceClass);
    }

    /**
     * The sub-service the office OFFERS for a `service_class` string, or null.
     *
     * `service_class` is the sub-service itself — sent as its numeric id or its
     * (localized) name — so map it to candidate sub-services, then keep only one
     * the office actually publishes a price for. Returning null means "the office
     * has no price for this class", which is what lets ServiceTariff stay a
     * genuine fallback for offices that never used the "my services" screen.
     */
    private function offeredSubServiceId(int $officeId, ?string $service, string $serviceClass): ?int
    {
        if ($officeId <= 0 || $serviceClass === '') {
            return null;
        }

        // Best-effort: on a shard (or a test) where the catalog tables are not
        // provisioned, resolving nothing here just means ServiceTariff prices the
        // ride — never a crash on the booking path.
        try {
            $subIds = SubService::query()
                ->where('status', 1)
                ->when(
                    is_numeric($serviceClass),
                    fn ($q) => $q->where('id', (int) $serviceClass),
                    fn ($q) => $q->where(fn ($w) => $w->where('name', $serviceClass)->orWhere('name_en', $serviceClass))
                )
                ->when(
                    $service !== null && $service !== '' && is_numeric($service),
                    fn ($q) => $q->where('serviceId', (int) $service)
                )
                ->pluck('id')
                ->all();

            if ($subIds === []) {
                return null;
            }

            $id = OfficeSubServicePrice::query()
                ->offered()
                ->where('office_id', $officeId)
                ->whereIn('sub_service_id', $subIds)
                ->value('sub_service_id');

            return $id !== null ? (int) $id : null;
        } catch (\Throwable $e) {
            return null;
        }
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
        $fromTariff = ServiceTariff::query()
            ->where('service_class', $serviceClass)
            ->where('is_active', true)
            ->where(fn ($w) => $w->where('service', $service)->orWhereNull('service'))
            ->pluck('office_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        // Discovery follows the same truth as pricing: an office that published a
        // price for the sub-service on its "my services" screen offers the class
        // even with no ServiceTariff. Union, so this never DROPS an office that
        // used to appear — it only adds the catalog-only ones.
        return collect(array_merge($fromTariff, $this->officesPublishing($service, $serviceClass)))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /** Offices that publish a price for a sub-service matching (service, class). */
    private function officesPublishing(string $service, string $serviceClass): array
    {
        if ($serviceClass === '') {
            return [];
        }

        try {
            $subIds = SubService::query()
                ->where('status', 1)
                ->when(
                    is_numeric($serviceClass),
                    fn ($q) => $q->where('id', (int) $serviceClass),
                    fn ($q) => $q->where(fn ($w) => $w->where('name', $serviceClass)->orWhere('name_en', $serviceClass))
                )
                ->when(
                    $service !== '' && is_numeric($service),
                    fn ($q) => $q->where('serviceId', (int) $service)
                )
                ->pluck('id')
                ->all();

            if ($subIds === []) {
                return [];
            }

            return OfficeSubServicePrice::query()
                ->offered()
                ->whereIn('sub_service_id', $subIds)
                ->pluck('office_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
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
