<?php

namespace App\Http\Core\Classes\Ride;

use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\Const\Ride\BookingStatus;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\Order\OrderRedisModel;
use App\Models\Driver;
use App\Models\Office;
use App\Models\RideBooking;
use App\Models\SubService;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Feeds app-pipeline rides (RideBooking) into the legacy order-board Redis store
 * that the panel Live Trip Monitor reads. Without it the monitor only ever showed
 * legacy Booking rows and app rides were invisible. Reuses OrderRedisModel's
 * shard-prefixed keys/channels, so the board stays isolated per country.
 *
 * Best-effort: a Redis/broadcast hiccup never breaks a ride's lifecycle.
 */
class RideBoardBridge extends OrderRedisModel
{
    /** Every board status set, so a ride's id is cleared from stale ones on move. */
    private const BOARD_STATUSES = [
        'Pending', 'Search on driver', 'Ongoing', 'Hold', 'In Progress', 'Completed', 'Cancelled',
    ];

    public static function sync(RideBooking $booking): void
    {
        try {
            if (! config('services.realtime.order_board')) {
                return;
            }

            $status = self::mapStatus((string) $booking->status);

            if ($status === null) {
                // scheduled / not-yet-live / unknown → make sure it's off the board
                self::drop((int) $booking->id, $booking->office_id ? (int) $booking->office_id : null);

                return;
            }

            $order = self::toOrder($booking, $status);

            foreach (self::BOARD_STATUSES as $s) {
                if ($s !== $status) {
                    Redis::zrem(self::statusKey($s), $order->id);
                }
            }

            Redis::zadd(self::statusKey($status), $order->id, $order->id);

            $ttl = $status === OrderStatus::$Completed ? 86400 : ($status === OrderStatus::$Cancelled ? 3600 : 0);

            if ($ttl > 0) {
                Redis::setex(self::orderKey($order->id), $ttl, serialize($order));
            } else {
                Redis::set(self::orderKey($order->id), serialize($order));
            }

            self::pushBoard($order, 'upsert');
        } catch (Throwable $e) {
            Log::warning('RideBoardBridge sync failed: ' . $e->getMessage());
        }
    }

    private static function drop(int $id, ?int $officeId): void
    {
        try {
            foreach (self::BOARD_STATUSES as $s) {
                Redis::zrem(self::statusKey($s), $id);
            }

            Redis::del(self::orderKey($id));
            self::pushBoardRemove($id, $officeId);
        } catch (Throwable $e) {
            // best-effort
        }
    }

    private static function mapStatus(string $status): ?string
    {
        return match ($status) {
            BookingStatus::MATCHING, BookingStatus::PENDING_ACCEPTANCE => OrderStatus::$SearchOnDriver,
            BookingStatus::ASSIGNED, BookingStatus::ARRIVING, BookingStatus::CONFIRMED => OrderStatus::$OnGoing,
            BookingStatus::ARRIVED => OrderStatus::$Hold,
            BookingStatus::ON_TRIP => OrderStatus::$InProgress,
            BookingStatus::COMPLETED => OrderStatus::$Completed,
            BookingStatus::CANCELLED, BookingStatus::REJECTED, BookingStatus::DECLINED, BookingStatus::NO_DRIVER_EXPIRED => OrderStatus::$Cancelled,
            default => null,
        };
    }

    private static function toOrder(RideBooking $b, string $status): object
    {
        $conn = $b->getConnectionName();

        $o = new \stdClass();
        $o->id           = (int) $b->id;
        $o->status       = $status;
        $o->is_scheduled = false;
        $o->isRide       = true;
        $o->officeId     = $b->office_id ? (int) $b->office_id : null;
        $o->officeName   = $o->officeId ? Office::on($conn)->where('id', $o->officeId)->value('officeName') : null;
        $o->startAddress = (string) ($b->pickup_title ?? '');
        $o->endAddress   = (string) ($b->dropoff_title ?? '');
        $o->totalAmount  = ((int) ($b->total_minor ?? 0)) / 100;
        $o->distance     = ((float) ($b->distance_m ?? 0)) / 1000;
        $o->paymentType  = $b->payment_method ?? null;
        $o->paymentStatus = null;
        $o->created_at   = $b->created_at;

        $u = User::query()->where('id', $b->user_id)->first(['firstName', 'lastName', 'phoneNumber']);
        $o->user = $u ? (object) ['firstName' => $u->firstName, 'lastName' => $u->lastName, 'phoneNumber' => $u->phoneNumber] : null;

        $o->driver = null;
        if ($b->driver_id) {
            $d = Driver::on($conn)->where('id', $b->driver_id)->with('vehicle')->first();
            if ($d) {
                $v = $d->vehicle;
                $o->driver = (object) [
                    'firstName'   => $d->firstName,
                    'lastName'    => $d->lastName,
                    'phoneNumber' => $d->phoneNumber,
                    'vehicle'     => $v ? (object) [
                        'vehicleBrand' => $v->vehicleBrand,
                        'model'        => $v->model,
                        'plate'        => $v->plate,
                        'color'        => $v->color,
                        'modelYear'    => $v->modelYear,
                        'seatsCount'   => $v->seatsCount,
                    ] : null,
                ];
            }
        }

        $o->subService = null;
        if ($b->sub_service_id) {
            $name = SubService::on($conn)->where('id', $b->sub_service_id)
                ->value(app()->getLocale() === 'en' ? 'name_en' : 'name');
            $o->subService = $name ? (object) ['name' => $name] : null;
        }
        if ($o->subService === null && ! empty($b->service_class)) {
            $o->subService = (object) ['name' => $b->service_class];
        }

        return $o;
    }
}
