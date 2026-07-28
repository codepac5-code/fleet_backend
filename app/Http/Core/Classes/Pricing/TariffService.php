<?php

namespace App\Http\Core\Classes\Pricing;

use App\Models\ServiceTariff;

class TariffService
{
    /**
     * Convert a dashboard-entered amount to minor units.
     *
     * The tariff form collects WHOLE currency (8000.50) because that is what a
     * human types; storage is minor units. The form used to post straight into
     * `*_minor`, so an office that meant 8000 stored 8000 minor and every ride
     * priced at 80.00 — a hundredfold error with nothing to warn them.
     *
     * @param string|null $major whole-currency input, e.g. "8000.50"
     * @param string|null $minor explicit minor units; wins when present, so
     *                           existing callers and the API keep working
     */
    public static function toMinor(?string $major, ?string $minor = null): int
    {
        if ($minor !== null && $minor !== '') {
            return max(0, (int) $minor);
        }

        return max(0, (int) round(((float) $major) * 100));
    }

    public function upsertForOffice(int $officeId, string $serviceClass, string $currency, string $pricingStyle, array $rates): ServiceTariff
    {
        return ServiceTariff::query()->updateOrCreate(
            ['office_id' => $officeId, 'service_class' => $serviceClass],
            [
                'currency_code' => $currency,
                'pricing_style' => $pricingStyle,
                'base_minor' => max(0, (int) ($rates['base_minor'] ?? 0)),
                'per_km_minor' => max(0, (int) ($rates['per_km_minor'] ?? 0)),
                'per_minute_minor' => max(0, (int) ($rates['per_minute_minor'] ?? 0)),
                'minimum_minor' => max(0, (int) ($rates['minimum_minor'] ?? 0)),
                'fixed_minor' => max(0, (int) ($rates['fixed_minor'] ?? 0)),
                'is_active' => (bool) ($rates['is_active'] ?? true),
            ]
        );
    }

    public function forOffice(int $officeId): array
    {
        return ServiceTariff::query()
            ->where('office_id', $officeId)
            ->orderBy('service_class')
            ->get()
            ->all();
    }

    public function remove(int $officeId, string $serviceClass): void
    {
        ServiceTariff::query()
            ->where('office_id', $officeId)
            ->where('service_class', $serviceClass)
            ->delete();
    }
}
