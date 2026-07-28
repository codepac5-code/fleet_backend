<?php

namespace App\Http\Core\Classes\Support;

use App\Http\Core\Classes\Event\DomainEvent;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventType;
use App\Http\Core\Repositories\Ride\RideBookingRepository;
use App\Http\Core\Repositories\Support\DriverSafetyRepository;
use App\Models\DriverSafetyEvent;

class DriverSafetyService
{
    public function __construct(
        private DriverSafetyRepository $repository,
        private RideBookingRepository $bookings,
        private ?EventBus $events = null
    ) {
    }

    public function open(int $driverId, ?int $bookingId, ?float $lat, ?float $lng): array
    {
        return $this->present($this->record($driverId, $bookingId, 'sos_opened', null, 'open', null, $lat, $lng, null));
    }

    public function sos(int $driverId, ?int $bookingId, float $lat, float $lng, ?int $holdMs): array
    {
        $officeId = $this->officeFor($bookingId);
        $event = $this->record($driverId, $bookingId, 'sos', null, 'active', null, $lat, $lng, $holdMs);

        $this->alert($event, $officeId, $bookingId, $driverId, [
            'kind' => 'sos', 'driver_id' => $driverId, 'booking_id' => $bookingId, 'lat' => $lat, 'lng' => $lng,
        ]);

        return $this->present($event);
    }

    public function report(int $driverId, ?int $bookingId, string $category, string $note): array
    {
        $officeId = $this->officeFor($bookingId);
        $event = $this->record($driverId, $bookingId, 'report', $category, 'open', $note, null, null, null);

        $this->alert($event, $officeId, $bookingId, $driverId, [
            'kind' => 'safety_report', 'driver_id' => $driverId, 'booking_id' => $bookingId, 'category' => $category,
        ]);

        return $this->present($event);
    }

    private function record(int $driverId, ?int $bookingId, string $kind, ?string $category, string $status, ?string $note, ?float $lat, ?float $lng, ?int $holdMs): DriverSafetyEvent
    {
        return $this->repository->create([
            'driver_id' => $driverId,
            'booking_id' => $bookingId,
            'office_id' => $this->officeFor($bookingId),
            'kind' => $kind,
            'category' => $category,
            'status' => $status,
            'note' => $note,
            'lat' => $lat,
            'lng' => $lng,
            'hold_ms' => $holdMs,
            'created_at' => now(),
        ]);
    }

    private function alert(DriverSafetyEvent $event, ?int $officeId, ?int $bookingId, int $driverId, array $payload): void
    {
        if ($this->events === null) {
            return;
        }

        // Fleet ops room always hears safety events — this is the live SOS alert
        // the panel had no channel for. Additive: driver/office/booking rooms are
        // unchanged, and the apps never subscribe to `admin`.
        $channels = [Channel::admin(), Channel::driver($driverId)];

        if ($officeId !== null) {
            $channels[] = Channel::office($officeId);
        }

        if ($bookingId !== null) {
            $channels[] = Channel::booking($bookingId);
        }

        $this->events->emit(new DomainEvent(EventType::SUPPORT_MESSAGE_CREATED, $channels, array_merge($payload, ['event_id' => (int) $event->id])));
    }

    private function officeFor(?int $bookingId): ?int
    {
        if ($bookingId === null) {
            return null;
        }

        $booking = $this->bookings->find($bookingId);

        return $booking !== null ? (int) $booking->office_id : null;
    }

    private function present(DriverSafetyEvent $event): array
    {
        return [
            'event_id' => (int) $event->id,
            'kind' => $event->kind,
            'category' => $event->category,
            'status' => $event->status,
            'booking_id' => $event->booking_id !== null ? (int) $event->booking_id : null,
            'office_id' => $event->office_id !== null ? (int) $event->office_id : null,
        ];
    }
}
