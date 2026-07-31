<?php

namespace App\Http\Services\Panel\Tariffs\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Pricing\TariffService;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\OfficeSubServicePrice;
use App\Models\Service;
use App\Models\SubService;
use App\Models\TravelRoutes;
use Illuminate\Contracts\View\View;
use Throwable;

class TariffsPageController extends Controller
{
    public function __invoke(EntityScope $scope, TariffService $tariffs): View
    {
        $officeId = (int) $scope->officeId();

        $rows = array_map(fn ($t) => [
            'service_class'    => $t->service_class,
            'currency_code'    => $t->currency_code,
            'pricing_style'    => $t->pricing_style,
            'base_minor'       => (int) $t->base_minor,
            'per_km_minor'     => (int) $t->per_km_minor,
            'per_minute_minor' => (int) $t->per_minute_minor,
            'minimum_minor'    => (int) $t->minimum_minor,
            'fixed_minor'      => (int) $t->fixed_minor,
            'is_active'        => (bool) $t->is_active,
        ], $tariffs->forOffice($officeId));

        return view('panel.tariffs.index', [
            'entity'         => $scope->guard(),
            'tariffs'        => $rows,
            'currency'       => ShardManager::currency(),
            'serviceClasses' => $this->serviceClasses($rows, $officeId),
            // The rider-facing price does NOT come from this screen; it comes
            // from the sub-services this office offers. Say so here, with the
            // count, or an office prices a class it never even offers.
            'offered'        => $this->offeredCount($officeId),
            // Travel is NOT priced here — it is priced per corridor. Show this
            // office's corridor coverage so "where do I price travel?" is
            // answered on the page where the question is asked.
            'corridors'      => $this->corridorSummary($officeId),
        ]);
    }

    /**
     * A booking carries the SUB-SERVICE NAME in `service_class` — every row in
     * `service_tariffs` and every booking on every shard reads that way. The
     * list here used to be six invented words (standard, comfort, suv…) that
     * matched no sub-service anywhere, so a tariff saved from this screen could
     * never be hit by a booking. It is now the country's own catalog, labelled
     * in the reader's language and grouped by service, plus any class the
     * office already has a row for so old rows stay editable.
     */
    private function serviceClasses(array $rows, int $officeId): array
    {
        $isAr = app()->getLocale() === 'ar';
        $conn = TenantConnection::current();

        try {
            $services = Service::on($conn)->orderBy('id')->get();
            $subs = SubService::on($conn)->where('status', 1)->orderBy('id')->get();
            $offered = OfficeSubServicePrice::on($conn)->offered()->where('office_id', $officeId)->pluck('sub_service_id')->all();
        } catch (Throwable $e) {
            $services = collect();
            $subs = collect();
            $offered = [];
        }

        $titles = $services->mapWithKeys(fn ($s) => [
            (int) $s->id => [
                'title' => $isAr ? ($s->title ?: $s->title_en) : ($s->title_en ?: $s->title),
                'travel' => (bool) $s->travel_service,
            ],
        ])->all();

        $groups = [];
        $known = [];

        foreach ($subs as $sub) {
            $value = trim((string) ($sub->name_en ?: $sub->name));

            if ($value === '') {
                continue;
            }

            $service = $titles[(int) $sub->serviceId] ?? ['title' => textByLanguage('أخرى', 'Other'), 'travel' => false];
            $label = $isAr ? ($sub->name ?: $sub->name_en) : ($sub->name_en ?: $sub->name);

            $groups[$service['title']][] = [
                'value'   => $value,
                'label'   => trim((string) $label) ?: $value,
                'travel'  => $service['travel'],
                'offered' => in_array((int) $sub->id, array_map('intval', $offered), true),
            ];

            $known[strtolower($value)] = true;
        }

        $orphans = [];

        foreach ($rows as $row) {
            $class = trim((string) $row['service_class']);

            if ($class !== '' && ! isset($known[strtolower($class)])) {
                $orphans[] = ['value' => $class, 'label' => $class, 'travel' => false, 'offered' => false];
                $known[strtolower($class)] = true;
            }
        }

        if ($orphans !== []) {
            $groups[textByLanguage('فئات قديمة', 'Legacy classes')] = $orphans;
        }

        return $groups;
    }

    private function offeredCount(int $officeId): int
    {
        try {
            return OfficeSubServicePrice::on(TenantConnection::current())
                ->offered()
                ->where('office_id', $officeId)
                ->count();
        } catch (Throwable $e) {
            return 0;
        }
    }

    private function corridorSummary(int $officeId): array
    {
        try {
            // TravelRoutes is not tenant-routed, so the connection is explicit.
            $rows = TravelRoutes::on(TenantConnection::current())
                ->where('officeId', $officeId)
                ->get(['trip_price']);
        } catch (Throwable $e) {
            return ['count' => 0, 'min' => null, 'max' => null];
        }

        return [
            'count' => $rows->count(),
            'min' => $rows->min('trip_price'),
            'max' => $rows->max('trip_price'),
        ];
    }
}
