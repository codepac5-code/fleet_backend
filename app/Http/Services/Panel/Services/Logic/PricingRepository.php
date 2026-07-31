<?php

namespace App\Http\Services\Panel\Services\Logic;

use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\OfficeSubServicePrice;
use App\Models\Service;
use App\Models\SubService;

class PricingRepository
{
    private function connection(): ?string
    {
        return TenantConnection::current();
    }

    /**
     * The catalogue an office may price.
     *
     * An office is set up under one or more main services, and only the
     * sub-services beneath those are its to sell. Passing $serviceIds narrows
     * the catalogue to them; omitting it (null) returns everything, which is
     * what the platform admin's own screens still want. Before this,
     * participation was a free-for-all: a city-taxi office could tick — and
     * price — the airport corridors of a travel service it does not run.
     *
     * An empty array is NOT "no filter": an office assigned nothing yet may
     * price nothing, and silently showing it the whole catalogue would undo the
     * boundary at exactly the moment it matters.
     */
    public function catalog(bool $activeOnly = false, ?array $serviceIds = null): array
    {
        $conn = $this->connection();
        $isAr = app()->getLocale() === 'ar';

        $services = Service::on($conn)
            ->when($serviceIds !== null, fn ($q) => $q->whereIn('id', $serviceIds))
            ->orderBy('id')
            ->get();

        $subQuery = SubService::on($conn)
            ->when($serviceIds !== null, fn ($q) => $q->whereIn('serviceId', $serviceIds))
            ->orderBy('id');
        if ($activeOnly) {
            $subQuery->where('status', 1);
        }
        $subsByService = $subQuery->get()->groupBy('serviceId');

        return $services->map(function ($service) use ($subsByService, $isAr) {
            $subs = ($subsByService[$service->id] ?? collect())->map(fn ($s) => [
                'id'          => $s->id,
                'name'        => $isAr ? ($s->name ?: $s->name_en) : ($s->name_en ?: $s->name),
                'status'      => (int) $s->status,
                'openPrice'   => (float) $s->openPrice,
                'kmPrice'     => (float) $s->kmPrice,
                'minutePrice' => (float) $s->minutePrice,
            ])->values()->all();

            return [
                'id'          => $service->id,
                'title'       => $isAr ? ($service->title ?: $service->title_en) : ($service->title_en ?: $service->title),
                'status'      => (int) $service->status,
                // A travel service is sold as fixed corridors, not by the metre,
                // so its screen must send the office to the corridors instead of
                // asking for an open/km/minute price it will never charge.
                'isTravel'    => (bool) $service->travel_service,
                'subServices' => $subs,
            ];
        })->values()->all();
    }

    public function officePrices(int $officeId): array
    {
        return OfficeSubServicePrice::on($this->connection())
            ->where('office_id', $officeId)
            ->get()
            ->keyBy('sub_service_id')
            ->map(fn ($p) => [
                'openPrice'   => (float) $p->openPrice,
                'kmPrice'     => (float) $p->kmPrice,
                'minutePrice' => (float) $p->minutePrice,
                'is_enabled'  => (bool) ($p->is_enabled ?? true),
            ])
            ->all();
    }

    /**
     * Saves what an office OFFERS and, separately, what it charges.
     *
     * Blank rates used to delete the row — and since the rider search collects
     * offices from this table, that quietly removed the office from the service
     * while the screen said it would fall back to the base price. Now the row
     * survives with `is_enabled`, and empty rates simply mean "catalog price".
     */
    public function syncPrices(int $officeId, array $rows, ?array $serviceIds = null): void
    {
        $conn = $this->connection();

        // The form only ever renders the office's OWN services, but the POST is
        // just a map of ids — so the boundary is enforced here too, where it
        // cannot be bypassed by editing the request.
        if ($serviceIds !== null) {
            $allowed = SubService::on($conn)->whereIn('serviceId', $serviceIds)->pluck('id')->all();
            $rows = array_intersect_key($rows, array_flip($allowed));
        }

        foreach ($rows as $subServiceId => $values) {
            $open = $values['openPrice'] ?? null;
            $km = $values['kmPrice'] ?? null;
            $minute = $values['minutePrice'] ?? null;
            $enabled = ! empty($values['enabled']);

            $query = OfficeSubServicePrice::on($conn)
                ->where('office_id', $officeId)
                ->where('sub_service_id', (int) $subServiceId);

            $existing = (clone $query)->first();

            if (! $enabled) {
                // Not offered: keep any price the office had typed, so
                // re-enabling later does not lose it.
                if ($existing) {
                    $existing->setConnection($conn)->update(['is_enabled' => false]);
                }

                continue;
            }

            $payload = [
                'office_id'      => $officeId,
                'sub_service_id' => (int) $subServiceId,
                'openPrice'      => (float) ($open ?: 0),
                'kmPrice'        => (float) ($km ?: 0),
                'minutePrice'    => (float) ($minute ?: 0),
                'is_enabled'     => true,
            ];

            if ($existing) {
                $existing->setConnection($conn)->update($payload);
                continue;
            }

            $row = new OfficeSubServicePrice($payload);
            $row->setConnection($conn);
            $row->save();
        }
    }
}
