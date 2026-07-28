<?php

namespace App\Http\Services\User\Support\Presenters;

use App\Models\RideBooking;
use App\Models\RideRating;

class BookingPresenter
{
    public static function row(RideBooking $b, ?string $status = null): array
    {
        return [
            'id' => (int) $b->id,
            'user_id' => (int) $b->user_id,
            'office_id' => (int) $b->office_id,
            'driver_id' => $b->driver_id !== null ? (int) $b->driver_id : null,
            'vehicle_id' => $b->vehicle_id !== null ? (int) $b->vehicle_id : null,
            'service' => $b->service,
            'service_class' => $b->service_class,
            'pricing_style' => $b->pricing_style,
            'status' => $status ?? (string) $b->status,
            'pickup_lat' => (float) $b->pickup_lat,
            'pickup_lng' => (float) $b->pickup_lng,
            'pickup_title' => $b->pickup_title,
            'pickup_note' => $b->pickup_note,
            'dropoff_lat' => (float) $b->dropoff_lat,
            'dropoff_lng' => (float) $b->dropoff_lng,
            'dropoff_title' => $b->dropoff_title,
            'stops' => $b->stops ?? [],
            'distance_m' => (int) $b->distance_m,
            'duration_s' => (int) $b->duration_s,
            'currency_code' => $b->currency_code,
            'fare_minor' => (int) $b->fare_minor,
            'discount_minor' => (int) $b->discount_minor,
            'waiting_minor' => (int) $b->waiting_minor,
            'tip_minor' => (int) $b->tip_minor,
            'total_minor' => (int) $b->total_minor,
            'held_minor' => (int) $b->held_minor,
            'payment_method' => $b->payment_method,
            'stripe_payment_intent_id' => $b->stripe_payment_intent_id,
            'promo_code' => $b->promo_code,
            'coupon_id' => $b->coupon_id !== null ? (int) $b->coupon_id : null,
            'scheduled_at' => self::iso($b->scheduled_at),
            'passengers' => $b->passengers !== null ? (int) $b->passengers : null,
            'luggage' => $b->luggage !== null ? (int) $b->luggage : null,
            'flight_no' => $b->flight_no,
            'assigned_at' => self::iso($b->assigned_at),
            'completed_at' => self::iso($b->completed_at),
            'cancelled_at' => self::iso($b->cancelled_at),
            'rated_at' => self::iso($b->rated_at),
            'cancel_reason' => $b->cancel_reason,
            'change_revision' => (int) $b->change_revision,
            'source' => $b->source,
            'created_by' => $b->created_by,
            'created_at' => self::iso($b->created_at),
        ];
    }

    public static function detail(RideBooking $b, ?string $status, ?array $office, ?array $rating, ?array $driver = null): array
    {
        return array_merge(self::row($b, $status), [
            'office' => $office,
            'rating' => $rating,
            'driver' => $driver,
        ]);
    }

    public static function listRow(RideBooking $b, ?string $status, ?array $office): array
    {
        return array_merge(self::row($b, $status), ['office' => $office]);
    }

    public static function rating(RideRating $r): array
    {
        return [
            'id' => (int) $r->id,
            'booking_id' => (int) $r->booking_id,
            'rater_type' => $r->rater_type,
            'rater_id' => (int) $r->rater_id,
            'ratee_type' => $r->ratee_type,
            'ratee_id' => (int) $r->ratee_id,
            'stars' => (int) $r->stars,
            'tags' => $r->tags ?? [],
            'comment' => $r->comment,
            'book_again' => $r->book_again !== null ? (bool) $r->book_again : null,
            'favorite' => $r->favorite !== null ? (bool) $r->favorite : null,
        ];
    }

    private static function iso($dt): ?string
    {
        return $dt !== null ? $dt->toIso8601ZuluString() : null;
    }
}
