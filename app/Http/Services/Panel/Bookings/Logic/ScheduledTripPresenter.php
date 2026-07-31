<?php

namespace App\Http\Services\Panel\Bookings\Logic;

use App\Http\Core\Const\Ride\BookingStatus;

class ScheduledTripPresenter
{
    public static function toArray($row, string $entity): array
    {
        $isRide = (bool) ($row->isRide ?? false);
        $group  = $row->group ?? BookingRepository::scheduledGroupOf((string) $row->status);
        $time   = $row->scheduled_time;
        $r      = fn (string $name) => "panel.{$entity}.{$name}";

        // App rides (isRide) open the RideBooking detail page. Manual driver
        // assignment runs through the RideBooking-aware route; the legacy
        // assign/cancel routes target the old Booking id, so cancel stays on the
        // legacy pipeline only.
        $showUrl = $isRide ? route($r('rides.show'), $row->id) : route($r('booking.show'), $row->id);

        $status = (string) $row->status;

        $assignable = in_array($group, ['upcoming', 'active'], true)
            && ! in_array($status, [BookingStatus::ARRIVED, BookingStatus::ON_TRIP], true);

        // Fixed corridor trips await the office's acceptance in PENDING_ACCEPTANCE.
        $acceptable = $isRide && $status === BookingStatus::PENDING_ACCEPTANCE;

        // Cancellable until the trip is closed OR the driver is already en route
        // (those cancel from the live board so the meter/settlement is handled).
        $cancellable = $isRide
            && ! in_array($status, BookingStatus::TERMINAL, true)
            && ! in_array($status, BookingStatus::LIVE_SUB, true);

        return [
            'id'               => (int) $row->id,
            'status'           => $row->status,
            'statusLabel'      => $row->statusLabel ?? (string) $row->status,
            'group'            => $group,
            'period'           => $row->period ?? 'week',
            'date'             => $time ? $time->format('Y-m-d') : null,
            'time'             => $time ? $time->format('H:i') : null,
            'scheduledDisplay' => $time ? $time->translatedFormat('j M، H:i') : null,
            'startAddress'     => $row->startAddress,
            'endAddress'       => $row->endAddress,
            'amount'           => getPriceFormat($row->totalAmount ?? 0),
            'distance'         => number_format((float) $row->distance, 1),
            'paymentType'      => $row->paymentType,
            'paymentStatus'    => $row->paymentStatus,
            'customer'         => [
                'name'  => trim((string) $row->customer),
                'phone' => $row->customer_phone,
            ],
            'driver'           => $row->driverId ? [
                'id'    => (int) $row->driverId,
                'name'  => trim((string) $row->driver),
                'phone' => $row->driver_phone,
            ] : null,
            'editable'         => $assignable,
            'urls'             => [
                'show'   => $showUrl,
                'assign' => $assignable
                    ? ($isRide ? route($r('booking.rides.assign'), $row->id) : route($r('booking.assign'), $row->id))
                    : null,
                'accept' => $acceptable ? route($r('booking.rides.accept'), $row->id) : null,
                'cancel' => $isRide
                    ? ($cancellable ? route($r('booking.rides.cancel'), $row->id) : null)
                    : ($assignable ? route($r('booking.cancel'), $row->id) : null),
            ],
        ];
    }
}
