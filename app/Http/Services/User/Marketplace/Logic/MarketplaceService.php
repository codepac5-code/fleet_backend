<?php

namespace App\Http\Services\User\Marketplace\Logic;

use App\Http\Core\Classes\Dispatch\Geo;
use App\Http\Core\Classes\Places\PlacesService;
use App\Http\Core\Classes\Pricing\PricingService;
use App\Http\Core\Classes\Pricing\TariffResolver;
use App\Http\Core\Classes\Ride\Polyline;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Services\User\Support\Presenters\CatalogPresenter;
use App\Http\Services\User\Support\Presenters\OfficePresenter;
use App\Http\Core\Classes\Billing\RegionBilling;
use App\Http\Core\Const\Subscription\SubscriptionStatus;
use App\Models\OfficeSubscription;
use App\Http\Core\GeoServices\ShardManager;
use App\Models\InfrastructureNode;
use App\Models\Office;
use App\Models\OfficeSubServicePrice;
use App\Models\SavedPlace;
use App\Models\Service;
use App\Models\SubService;
use Throwable;

class MarketplaceService
{
    /// Assumed average speed (m/s) for turning a distance into a duration —
    /// the same 8 m/s the booking pipeline uses, so quotes stay consistent.
    private const SPEED_MPS = 8;

    public function __construct(
        private PlacesService $places,
        private TariffResolver $tariffs,
        private PricingService $pricing,
        private \App\Http\Core\Classes\Pricing\MeterPricingService $meter = new \App\Http\Core\Classes\Pricing\MeterPricingService(),
        private \App\Http\Core\GeoServices\RouteEstimator $routes = new \App\Http\Core\GeoServices\RouteEstimator(),
    ) {
    }

    public function officesSearch(array $route): array
    {
        $subIds = $this->matchingSubServiceIds($route['service'] ?? null, $route['serviceClass'] ?? null);

        // Only offices that actually OFFER the sub-service, and — in a
        // subscription country — only those whose subscription entitles them.
        $officeIds = OfficeSubServicePrice::query()
            ->offered()
            ->whereIn('sub_service_id', $subIds)
            ->pluck('office_id')
            ->unique()
            ->all();

        $officeIds = $this->entitledOffices($officeIds);

        $pLat = (float) $route['pickup']['lat'];
        $pLng = (float) $route['pickup']['lng'];

        $offices = Office::query()->whereIn('id', $officeIds)->get()->all();

        usort($offices, fn ($a, $b) => $this->distance($a, $pLat, $pLng) <=> $this->distance($b, $pLat, $pLng));

        // Price each office for THIS route through the same tariff + pricing
        // engine the booking pipeline uses, so the fare on the card is the fare
        // the rider is actually charged (meter = base + per-km + per-minute;
        // fixed = the flat fare). ETA is the office's own drive to the pickup.
        $service = $route['service'] ?? null;
        $serviceClass = (string) ($route['serviceClass'] ?? '');

        $tripDistance = Geo::haversineMeters(
            $pLat,
            $pLng,
            (float) $route['dropoff']['lat'],
            (float) $route['dropoff']['lng'],
        );
        $tripDuration = (int) round($tripDistance / self::SPEED_MPS);

        // Meter offers (explicit `route.meter`) price on the METER (open +
        // per-km + per-minute from the sub-service / office override) — so a
        // metered ride shows each office's real fare. Regular office search keeps
        // the tariff quote.
        $meterSubId = (($route['meter'] ?? false) && is_numeric($serviceClass) && count($subIds) === 1)
            ? (int) $subIds[0]
            : null;

        $cards = [];
        foreach ($offices as $office) {
            $card = OfficePresenter::card($office);
            $card['eta_minutes'] = $this->etaMinutes($office, $pLat, $pLng);

            if ($meterSubId !== null) {
                $quote = $this->meter->quote((int) $office->id, $meterSubId, $tripDistance, $tripDuration);
                $card['fare_minor'] = (int) $quote['fare_minor'];
                $card['pricing_style'] = 'meter';
                $card['currency_code'] = (string) $quote['currency_code'];
                $card['fare_breakdown'] = $quote['breakdown'];
            } else {
                // The office's OWN published price is the truth: resolve the
                // sub-service it offers for this class (office_sub_service_prices →
                // sub_services), falling back to a legacy ServiceTariff only when
                // it never set one. Every listed office offers the class (that is
                // how discovery found it), so the card is priced from its rates.
                $tariff = $serviceClass === ''
                    ? null
                    : $this->tariffs->forOfficeServiceOrSub((int) $office->id, null, $service, $serviceClass);

                if ($tariff !== null) {
                    $quote = $this->pricing->quote($tariff, $tripDistance, $tripDuration);
                    $card['fare_minor'] = (int) $quote['fare_minor'];
                    $card['pricing_style'] = (string) $quote['pricing_style'];
                    $card['currency_code'] = (string) $tariff['currency_code'];
                    $card['fare_breakdown'] = $quote['breakdown'] ?? [];
                }
            }

            $cards[] = $card;
        }

        return ['offices' => $cards];
    }

    /// Minutes for the office to reach the pickup, from its own coordinates.
    /// Null when the office has no location on file — better an absent ETA than
    /// a fabricated "0 min away".
    private function etaMinutes(Office $office, float $pLat, float $pLng): ?int
    {
        if ($office->lat === null || $office->lng === null) {
            return null;
        }

        $metres = Geo::haversineMeters((float) $office->lat, (float) $office->lng, $pLat, $pLng);

        return max(1, (int) round($metres / self::SPEED_MPS / 60));
    }

    public function estimate(array $pickup, array $dropoff): array
    {
        $pLat = (float) $pickup['lat'];
        $pLng = (float) $pickup['lng'];
        $dLat = (float) $dropoff['lat'];
        $dLng = (float) $dropoff['lng'];

        // Estimated distance + time from Google Maps (real driving route via the
        // Directions API; straight-line haversine only as an offline fallback).
        [$distance, $duration] = $this->routes->estimate($pLat, $pLng, $dLat, $dLng);
        $currency = $this->currency();

        $classes = SubService::query()->where('status', 1)->orderBy('id')->get()->map(function (SubService $sub) use ($distance, $duration, $currency) {
            $fare = (float) $sub->openPrice
                + (float) $sub->kmPrice * ($distance / 1000)
                + (float) $sub->minutePrice * ($duration / 60);

            return array_merge(CatalogPresenter::serviceClass($sub), [
                'openPrice' => (float) $sub->openPrice,
                'kmPrice' => (float) $sub->kmPrice,
                'minutePrice' => (float) $sub->minutePrice,
                'base_fare' => (float) $sub->openPrice,
                'fare_minor' => (int) round($fare * 100),
                'currency_code' => $currency,
            ]);
        })->all();

        return [
            'distance_m' => $distance,
            'duration_s' => $duration,
            'currency_code' => $currency,
            'polyline' => Polyline::encode([[$pLat, $pLng], [$dLat, $dLng]]),
            'classes' => $classes,
        ];
    }

    public function officeProfile(int $id): array
    {
        $office = Office::query()->find($id);

        if ($office === null) {
            throw DomainException::notFound('office_not_found');
        }

        $subServices = OfficeSubServicePrice::query()
            ->where('office_id', $id)
            ->with('subService')
            ->get()
            ->pluck('subService')
            ->filter()
            ->unique('id')
            ->values();

        $services = Service::query()
            ->whereIn('id', $subServices->pluck('serviceId')->unique()->all())
            ->orderBy('id')
            ->get()
            ->map(fn (Service $s) => CatalogPresenter::service($s))
            ->all();

        $classes = $subServices->map(fn (SubService $c) => CatalogPresenter::serviceClass($c))->all();

        return array_merge(OfficePresenter::card($office), [
            'services' => $services,
            'classes' => $classes,
        ]);
    }

    public function catalogServices(): array
    {
        $services = Service::query()
            ->where('status', 1)
            ->orderBy('id')
            ->get()
            ->map(fn (Service $s) => CatalogPresenter::service($s))
            ->all();

        return ['services' => $services];
    }

    public function catalogClasses(?int $service): array
    {
        $query = SubService::query()->where('status', 1)->orderBy('id');

        if ($service !== null) {
            $query->where('serviceId', $service);
        }

        $classes = $query->get();

        // A class inherits its parent service's fixed/metered nature, so a
        // fixed (travel) service's classes are all FIXED even if the row's own
        // is_travel flag wasn't set. Keeps class pricing consistent with Home.
        $travelByService = Service::query()
            ->whereIn('id', $classes->pluck('serviceId')->unique()->all())
            ->pluck('travel_service', 'id');

        return ['classes' => $classes->map(function (SubService $c) use ($travelByService) {
            $card = CatalogPresenter::serviceClass($c);
            $card['is_travel'] = (bool) ($travelByService[$c->serviceId] ?? false) || (bool) $c->is_travel;

            return $card;
        })->all()];
    }

    public function placesSuggest(int $userId, string $q, ?string $country = null): array
    {
        // 1) The rider's own saved places (carry coordinates directly).
        $saved = SavedPlace::query()->where('user_id', $userId);
        if ($q !== '') {
            $saved->where(fn ($w) => $w->where('title', 'like', '%' . $q . '%')
                ->orWhere('address', 'like', '%' . $q . '%'));
        }

        $results = $saved->orderBy('id')->limit(6)->get()->map(fn (SavedPlace $p) => [
            'id' => (string) $p->id,
            'title' => $p->title,
            'address' => $p->address,
            'lat' => (float) $p->lat,
            'lng' => (float) $p->lng,
            'source' => 'recent',
        ])->all();

        // 2) Live Google Places predictions, biased to the rider's country. These
        // carry no coordinates — the app resolves them via placeDetails() on tap.
        if ($q !== '') {
            foreach ($this->places->autocomplete($q, null, null, null, $country) as $g) {
                $results[] = [
                    'place_id' => (string) ($g['place_id'] ?? ''),
                    'title' => (string) ($g['primary'] ?? ''),
                    'address' => (string) ($g['secondary'] ?? ''),
                    'source' => 'google',
                ];
            }
        }

        return ['results' => $results];
    }

    /** Resolve a Google prediction's coordinates + address once the rider taps it. */
    public function placeDetails(string $placeId): ?array
    {
        if ($placeId === '') {
            return null;
        }

        return $this->places->details($placeId);
    }

    private function matchingSubServiceIds($service, $serviceClass): array
    {
        $query = SubService::query()->where('status', 1);

        if ($serviceClass !== null && $serviceClass !== '') {
            if (is_numeric($serviceClass)) {
                $query->where('id', (int) $serviceClass);
            } else {
                $query->where(fn ($w) => $w->where('name', $serviceClass)->orWhere('name_en', $serviceClass));
            }
        }

        if ($service !== null && $service !== '' && is_numeric($service)) {
            $query->where('serviceId', (int) $service);
        }

        return $query->pluck('id')->all();
    }

    private function distance(Office $office, float $lat, float $lng): int
    {
        if ($office->lat === null || $office->lng === null) {
            return PHP_INT_MAX;
        }

        return Geo::haversineMeters((float) $office->lat, (float) $office->lng, $lat, $lng);
    }

    /**
     * Drops offices that are not entitled to trade. Commission countries never
     * gate on a subscription; subscription countries do — an office whose trial
     * lapsed or whose payment failed should stop being offered, not keep
     * selling for free. Fail-open: if the check itself breaks, nobody is hidden.
     */
    private function entitledOffices(array $officeIds): array
    {
        if ($officeIds === [] || ! RegionBilling::isSubscription()) {
            return $officeIds;
        }

        try {
            $entitled = OfficeSubscription::query()
                ->whereIn('office_id', $officeIds)
                ->whereIn('status', SubscriptionStatus::ENTITLED)
                ->pluck('office_id')
                ->unique()
                ->all();
        } catch (Throwable $e) {
            return $officeIds;
        }

        return array_values(array_intersect($officeIds, $entitled));
    }

    private function currency(): string
    {
        // The country the rider is actually browsing — this used to take the
        // FIRST active node's currency, so a Syrian rider could be quoted in
        // whichever currency happened to be first in the table.
        try {
            return ShardManager::currency();
        } catch (Throwable $e) {
            return ShardManager::DEFAULT_CURRENCY;
        }
    }
}
