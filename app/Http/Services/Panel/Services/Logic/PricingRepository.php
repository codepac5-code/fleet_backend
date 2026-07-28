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

    public function catalog(bool $activeOnly = false): array
    {
        $conn = $this->connection();
        $isAr = app()->getLocale() === 'ar';

        $services = Service::on($conn)->orderBy('id')->get();

        $subQuery = SubService::on($conn)->orderBy('id');
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
            ])
            ->all();
    }

    public function syncPrices(int $officeId, array $rows): void
    {
        $conn = $this->connection();

        foreach ($rows as $subServiceId => $values) {
            $open = $values['openPrice'] ?? null;
            $km = $values['kmPrice'] ?? null;
            $minute = $values['minutePrice'] ?? null;

            $hasAny = ($open !== null && $open !== '') || ($km !== null && $km !== '') || ($minute !== null && $minute !== '');

            $query = OfficeSubServicePrice::on($conn)
                ->where('office_id', $officeId)
                ->where('sub_service_id', (int) $subServiceId);

            if (! $hasAny) {
                $query->delete();
                continue;
            }

            $existing = (clone $query)->first();
            $payload = [
                'office_id'      => $officeId,
                'sub_service_id' => (int) $subServiceId,
                'openPrice'      => (float) ($open ?: 0),
                'kmPrice'        => (float) ($km ?: 0),
                'minutePrice'    => (float) ($minute ?: 0),
            ];

            if ($existing) {
                $existing->setConnection($conn)->update($payload);
            } else {
                $model = new OfficeSubServicePrice($payload);
                $model->setConnection($conn);
                $model->save();
            }
        }
    }
}
