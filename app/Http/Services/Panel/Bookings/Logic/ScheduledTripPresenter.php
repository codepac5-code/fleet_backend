<?php

namespace App\Http\Services\Panel\Bookings\Logic;

class ScheduledTripPresenter
{
    public static function toArray($row, string $entity): array
    {
        $isRide = (bool) ($row->isRide ?? false);
        $group  = $row->group ?? BookingRepository::scheduledGroupOf((string) $row->status);
        $time   = $row->scheduled_time;
        $r      = fn (string $name) => "panel.{$entity}.{$name}";

        // App rides (isRide) open the RideBooking detail page and are view-only on
        // this board — the legacy assign/cancel routes target the old Booking id.
        $showUrl = $isRide ? route($r('rides.show'), $row->id) : route($r('booking.show'), $row->id);

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
            'editable'         => ! $isRide && in_array($group, ['upcoming', 'active'], true),
            'urls'             => [
                'show'   => $showUrl,
                'assign' => $isRide ? null : route($r('booking.assign'), $row->id),
                'cancel' => $isRide ? null : route($r('booking.cancel'), $row->id),
            ],
        ];
    }
}
