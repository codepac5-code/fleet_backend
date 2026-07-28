<?php

namespace App\Http\Services\Panel\Bookings\Logic;

use App\Http\Core\Const\Options\OrderStatus;

class LiveTripPresenter
{
    public static function groups(): array
    {
        return [
            'pending'   => [OrderStatus::$Pending, OrderStatus::$SearchOnDriver],
            'ongoing'   => [OrderStatus::$OnGoing, OrderStatus::$InProgress, OrderStatus::$Hold],
            'completed' => [OrderStatus::$Completed],
            'cancelled' => [OrderStatus::$Cancelled],
        ];
    }

    public static function activeGroups(): array
    {
        $groups = self::groups();
        unset($groups['cancelled']);

        return $groups;
    }

    public static function groupOf(?string $status): string
    {
        foreach (self::groups() as $group => $statuses) {
            if (in_array($status, $statuses, true)) {
                return $group;
            }
        }

        return 'pending';
    }

    public static function priority(string $group): int
    {
        return match ($group) {
            'pending'   => 0,
            'ongoing'   => 1,
            'completed' => 2,
            default     => 3,
        };
    }

    public static function stageOf(?string $status): int
    {
        return match ($status) {
            OrderStatus::$Pending, OrderStatus::$SearchOnDriver => 1,
            OrderStatus::$OnGoing    => 2,
            OrderStatus::$Hold       => 3,
            OrderStatus::$InProgress => 4,
            OrderStatus::$Completed  => 5,
            default                  => 0,
        };
    }

    public static function statusLabel(?string $status): string
    {
        return match ($status) {
            OrderStatus::$Pending        => textByLanguage('بانتظار سائق', 'Pending'),
            OrderStatus::$SearchOnDriver => textByLanguage('جارٍ البحث عن سائق', 'Searching for driver'),
            OrderStatus::$OnGoing        => textByLanguage('السائق على الطريق', 'Driver on the way'),
            OrderStatus::$Hold           => textByLanguage('بانتظار الراكب', 'Awaiting passenger'),
            OrderStatus::$InProgress     => textByLanguage('انطلقت الرحلة', 'Trip started'),
            OrderStatus::$Completed      => textByLanguage('اكتملت الرحلة', 'Completed'),
            OrderStatus::$Cancelled      => textByLanguage('ملغاة', 'Cancelled'),
            default                      => (string) $status,
        };
    }

    public static function fromOrder($order): array
    {
        $driver = $order->driver ?? null;
        $user   = $order->user ?? null;
        $sub    = $order->subService ?? null;

        $car         = null;
        $vehicleText = null;
        if ($driver && ($driver->vehicle ?? null)) {
            $v   = $driver->vehicle;
            $car = [
                'brand' => $v->vehicleBrand ?? null,
                'model' => $v->model ?? null,
                'plate' => $v->plate ?? null,
                'color' => $v->color ?? null,
                'year'  => $v->modelYear ?? null,
                'seats' => $v->seatsCount ?? null,
            ];
            $vehicleText = trim(implode(' ', array_filter([$v->vehicleBrand ?? null, $v->model ?? null])));
            if (! empty($v->plate)) {
                $vehicleText = trim($vehicleText . ' · ' . $v->plate);
            }
        }

        $status   = $order->status ?? null;
        $group    = self::groupOf($status);
        $officeId = $order->officeId !== null ? (int) $order->officeId : null;

        return [
            'id'           => (int) ($order->id ?? 0),
            'isRide'       => (bool) ($order->isRide ?? false),
            'status'       => $status,
            'statusLabel'  => self::statusLabel($status),
            'group'        => $group,
            'priority'     => self::priority($group),
            'stage'        => self::stageOf($status),
            'isScheduled'  => (bool) ($order->is_scheduled ?? false),
            'officeId'     => $officeId,
            'office'       => $order->officeName ?? null,
            'source'       => [
                'key'     => $officeId !== null ? (string) $officeId : 'fleet',
                'label'   => $officeId !== null
                    ? ($order->officeName ?? ('#' . $officeId))
                    : textByLanguage('أسطول النظام', 'System fleet'),
                'isFleet' => $officeId === null,
            ],
            'service'      => $sub->name ?? null,
            'startAddress' => (string) ($order->startAddress ?? ''),
            'endAddress'   => (string) ($order->endAddress ?? ''),
            'amount'       => getPriceFormat($order->totalAmount ?? 0),
            'distance'      => number_format((float) ($order->distance ?? 0), 1),
            'paymentType'   => $order->paymentType ?? null,
            'paymentStatus' => $order->paymentStatus ?? null,
            'createdAt'     => self::timeString($order->created_at ?? null),
            'sortKey'       => (int) ($order->id ?? 0),
            'customer'      => [
                'name'  => $user ? trim(($user->firstName ?? '') . ' ' . ($user->lastName ?? '')) : null,
                'phone' => $user->phoneNumber ?? null,
            ],
            'driver'        => $driver ? [
                'name'        => trim(($driver->firstName ?? '') . ' ' . ($driver->lastName ?? '')),
                'phone'       => $driver->phoneNumber ?? null,
                'vehicleText' => $vehicleText,
                'car'         => $car,
            ] : null,
        ];
    }

    private static function timeString($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format('H:i');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
