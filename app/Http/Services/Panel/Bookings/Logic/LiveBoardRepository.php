<?php

namespace App\Http\Services\Panel\Bookings\Logic;

use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\Order\OrderRedisModel;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Support\Carbon;

class LiveBoardRepository
{
    private const ACTIVE_CAP = 400;
    private const DONE_CAP    = 80;
    private const SEARCH_CAP  = 150;

    public function __construct(private EntityScope $scope) {}

    public function summary(): array
    {
        $scopeOffice = $this->scope->isAdmin() ? null : $this->scope->officeId();
        $cutoff      = Carbon::now()->subDay();
        $fleetLabel  = textByLanguage('أسطول النظام', 'System fleet');

        $searching = [];
        $sources   = [];

        foreach (LiveTripPresenter::activeGroups() as $group => $statuses) {
            $isDone = $group === 'completed';
            $cap    = $isDone ? self::DONE_CAP : self::ACTIVE_CAP;

            foreach ($statuses as $status) {
                foreach ($this->read($status, $cap) as $order) {
                    if (! $this->eligible($order, $scopeOffice)) {
                        continue;
                    }
                    if ($isDone && ! $this->isRecent($order, $cutoff)) {
                        continue;
                    }

                    $oid     = $order->officeId !== null ? (int) $order->officeId : null;
                    $key     = $oid !== null ? (string) $oid : 'fleet';
                    $isFleet = $oid === null;

                    if (! isset($sources[$key])) {
                        $sources[$key] = [
                            'key'       => $key,
                            'label'     => $isFleet ? $fleetLabel : ($order->officeName ?? ('#' . $oid)),
                            'isFleet'   => $isFleet,
                            'pending'   => 0,
                            'ongoing'   => 0,
                            'completed' => 0,
                            'total'     => 0,
                        ];
                    }

                    $sources[$key][$group]++;
                    $sources[$key]['total']++;

                    if ($group === 'pending' && count($searching) < self::SEARCH_CAP) {
                        $searching[] = LiveTripPresenter::fromOrder($order);
                    }
                }
            }
        }

        usort($searching, fn ($a, $b) => $b['sortKey'] <=> $a['sortKey']);

        $sources = array_values($sources);
        usort($sources, function ($a, $b) {
            if ($a['isFleet'] !== $b['isFleet']) {
                return $a['isFleet'] ? -1 : 1;
            }

            return $b['total'] <=> $a['total'];
        });

        return ['searching' => $searching, 'sources' => $sources];
    }

    public function companyTrips(string $companyKey): array
    {
        if ($this->scope->isAdmin()) {
            $isFleet  = $companyKey === 'fleet';
            $officeId = $isFleet ? null : (int) $companyKey;
        } else {
            $officeId = $this->scope->officeId();
            $isFleet  = false;
        }

        $cutoff = Carbon::now()->subDay();
        $groups = [
            'ongoing'   => [OrderStatus::$OnGoing, OrderStatus::$InProgress, OrderStatus::$Hold],
            'completed' => [OrderStatus::$Completed],
        ];

        $trips = [];

        foreach ($groups as $group => $statuses) {
            $isDone = $group === 'completed';
            $cap    = $isDone ? self::DONE_CAP : self::ACTIVE_CAP;

            foreach ($statuses as $status) {
                foreach ($this->read($status, $cap) as $order) {
                    if ($order === null || ! empty($order->is_scheduled)) {
                        continue;
                    }

                    $oid = $order->officeId !== null ? (int) $order->officeId : null;

                    if ($isFleet ? $oid !== null : $oid !== $officeId) {
                        continue;
                    }
                    if ($isDone && ! $this->isRecent($order, $cutoff)) {
                        continue;
                    }

                    $trips[] = LiveTripPresenter::fromOrder($order);
                }
            }
        }

        usort($trips, fn ($a, $b) => [$a['priority'], $b['sortKey']] <=> [$b['priority'], $a['sortKey']]);

        return ['trips' => $trips];
    }

    private function read(string $status, int $cap): iterable
    {
        try {
            return OrderRedisModel::getByStatusPaginated($status, 0, $cap);
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function eligible($order, ?int $scopeOffice): bool
    {
        if ($order === null || ! empty($order->is_scheduled)) {
            return false;
        }

        if ($scopeOffice !== null && (int) ($order->officeId ?? 0) !== $scopeOffice) {
            return false;
        }

        return true;
    }

    private function isRecent($order, Carbon $cutoff): bool
    {
        if (empty($order->created_at)) {
            return true;
        }

        try {
            return Carbon::parse($order->created_at)->greaterThanOrEqualTo($cutoff);
        } catch (\Throwable $e) {
            return true;
        }
    }
}
