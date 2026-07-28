<?php

namespace App\Http\Core\Classes\Ride;

use App\Http\Core\Classes\Dispatch\DispatchService;
use App\Http\Core\Classes\Dispatch\Geo;
use App\Http\Core\Classes\Pricing\PricingService;
use App\Http\Core\Classes\Pricing\TariffResolver;
use App\Http\Core\Const\Ride\ServiceCatalog;
use App\Http\Core\Exceptions\DomainException;
use App\Models\ServiceTariff;

class MarketplaceService
{
    private const SEARCH_RADIUS_M = 8000;
    private const SPEED_M_PER_MIN = 500;

    public function __construct(
        private TariffResolver $tariffs,
        private PricingService $pricing,
        private DispatchService $dispatch,
        private OfficeReadModel $offices,
        private RouteClassifier $classifier
    ) {
    }

    public function tripOptions(string $service, float $pLat, float $pLng, float $dLat, float $dLng): array
    {
        if (! ServiceCatalog::isService($service)) {
            throw DomainException::make('invalid_service', 422);
        }

        [$distance, $duration] = $this->route($pLat, $pLng, $dLat, $dLng);
        $style = ServiceCatalog::style($service);
        $currency = null;

        $classes = [];

        foreach (ServiceCatalog::classes($service) as $class) {
            $officeIds = $this->tariffs->offeringOfficeIds($service, $class);

            if ($officeIds === []) {
                $classes[] = ['class' => $class, 'available' => false, 'reason' => 'no_supply'];
                continue;
            }

            $fares = [];

            foreach ($officeIds as $officeId) {
                $tariff = $this->tariffs->forOfficeService($officeId, $service, $class);

                if ($tariff === null) {
                    continue;
                }

                $currency ??= $tariff['currency_code'];
                $fares[] = (int) $this->pricing->quote($tariff, $distance, $duration)['fare_minor'];
            }

            if ($fares === []) {
                $classes[] = ['class' => $class, 'available' => false, 'reason' => 'no_supply'];
                continue;
            }

            $entry = ['class' => $class, 'available' => true, 'pricing_style' => $style];

            if ($style === 'meter') {
                $entry['fare_range_minor'] = [min($fares), max($fares)];
            } else {
                $entry['fare_minor'] = min($fares);
            }

            $classes[] = $entry;
        }

        return [
            'route' => [
                'distance_m' => $distance,
                'duration_s' => $duration,
                'detected_route_type' => $this->classifier->classify($pLat, $pLng, $dLat, $dLng, $distance),
                'polyline' => Polyline::encode([[$pLat, $pLng], [$dLat, $dLng]]),
            ],
            'pricing_style' => $style,
            'currency_code' => $currency,
            'classes' => $classes,
        ];
    }

    public function officesAvailable(string $service, string $serviceClass, float $pLat, float $pLng, float $dLat, float $dLng): array
    {
        if (! ServiceCatalog::isService($service)) {
            throw DomainException::make('invalid_service', 422);
        }

        [$distance, $duration] = $this->route($pLat, $pLng, $dLat, $dLng);
        $style = ServiceCatalog::style($service);

        $offices = [];

        foreach ($this->tariffs->offeringOfficeIds($service, $serviceClass) as $officeId) {
            $tariff = $this->tariffs->forOfficeService($officeId, $service, $serviceClass);

            if ($tariff === null) {
                continue;
            }

            $fare = (int) $this->pricing->quote($tariff, $distance, $duration)['fare_minor'];
            $supply = $this->supply($officeId, $pLat, $pLng);
            $summary = $this->offices->summary($officeId);

            $offices[] = array_merge($summary, [
                'eta_min' => $supply['eta_min'],
                'cars_nearby' => $supply['cars_nearby'],
                'pricing_style' => $style,
                'fare_minor' => $fare,
                'currency_code' => $tariff['currency_code'],
                'free_cancel_min' => 5,
            ]);
        }

        usort($offices, fn ($a, $b) => $this->rank($a) <=> $this->rank($b));

        return [
            'best_office_id' => $offices[0]['office_id'] ?? null,
            'offices' => $offices,
        ];
    }

    public function browse(?string $service, ?string $search, ?int $cursorId, int $limit): array
    {
        $restrictIds = null;

        if ($service !== null && $service !== '') {
            if (! ServiceCatalog::isService($service)) {
                throw DomainException::make('invalid_service', 422);
            }

            $restrictIds = $this->tariffs->officesForService($service);

            if ($restrictIds === []) {
                return ['data' => [], 'meta' => ['next_cursor' => null, 'has_more' => false]];
            }
        }

        $rows = $this->offices->paginate($restrictIds, $search, $cursorId, $limit);
        $hasMore = count($rows) > $limit;
        $items = array_slice($rows, 0, $limit);

        return [
            'data' => array_map(fn ($o) => $this->offices->summary((int) $o->id), $items),
            'meta' => [
                'next_cursor' => $hasMore && $items !== [] ? (string) end($items)->id : null,
                'has_more' => $hasMore,
            ],
        ];
    }

    public function officeProfile(int $officeId): array
    {
        $summary = $this->offices->summary($officeId);

        $rows = ServiceTariff::query()
            ->where('office_id', $officeId)
            ->where('is_active', true)
            ->get(['service', 'service_class', 'pricing_style']);

        $services = [];

        foreach ($rows as $row) {
            $key = (string) ($row->service ?? 'ride');
            $services[$key]['service'] = $key;
            $services[$key]['pricing_style'] = $row->pricing_style;
            $services[$key]['classes'][] = $row->service_class;
        }

        return array_merge($summary, [
            'stats' => ['rating' => $summary['rating'], 'ratings_count' => $summary['ratings_count']],
            'services' => array_values($services),
            'cancellation_policy' => ['free_cancel_min' => 5],
        ]);
    }

    private function route(float $pLat, float $pLng, float $dLat, float $dLng): array
    {
        $distance = Geo::haversineMeters($pLat, $pLng, $dLat, $dLng);
        $duration = (int) round($distance / 8);

        return [$distance, $duration];
    }

    private function supply(int $officeId, float $lat, float $lng): array
    {
        $candidates = $this->dispatch->findCandidates($officeId, $lat, $lng, self::SEARCH_RADIUS_M, 20, 60);

        if ($candidates === []) {
            return ['eta_min' => null, 'cars_nearby' => 0];
        }

        $nearest = (float) $candidates[0]['distance_m'];

        return [
            'eta_min' => max(1, (int) ceil($nearest / self::SPEED_M_PER_MIN)),
            'cars_nearby' => count($candidates),
        ];
    }

    private function rank(array $office): array
    {
        return [
            $office['eta_min'] === null ? 1 : 0,
            $office['eta_min'] ?? PHP_INT_MAX,
            $office['fare_minor'],
            -1 * (float) $office['rating'],
        ];
    }
}
