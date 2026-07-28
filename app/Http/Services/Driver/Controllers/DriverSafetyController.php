<?php

namespace App\Http\Services\Driver\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Event\DomainEvent;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventType;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Services\User\Support\Reply;
use App\Models\DriverSafetyContact;
use App\Models\DriverSafetyEvent;
use App\Models\DriverStatusLink;
use App\Models\RideBooking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Driver safety surface: emergency contacts, SOS events, and shareable
 * live-status links. Contacts + links are driver-owned (`driver_*` tables);
 * SOS writes `driver_safety_events`. All scoped to the authenticated driver.
 */
class DriverSafetyController extends Controller
{
    public function __construct(private EventBus $events = new EventBus())
    {
    }

    /** GET /driver/safety/contacts */
    public function contacts(Request $request): JsonResponse
    {
        $driverId = (int) $request->user()->id;

        $items = DriverSafetyContact::query()
            ->where('driver_id', $driverId)
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->get()
            ->map(fn (DriverSafetyContact $c) => [
                'id' => (int) $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'relation' => $c->relation,
                'is_primary' => (bool) $c->is_primary,
                'auto_share' => (bool) $c->auto_share,
            ])
            ->all();

        return Reply::ok(['items' => $items]);
    }

    /** POST /driver/safety/contacts */
    public function storeContact(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:32'],
            'relation' => ['nullable', 'string', 'max:40'],
            'is_primary' => ['nullable', 'boolean'],
            'auto_share' => ['nullable', 'boolean'],
        ]);

        $contact = DriverSafetyContact::query()->create([
            'driver_id' => (int) $request->user()->id,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'relation' => $data['relation'] ?? null,
            'is_primary' => (bool) ($data['is_primary'] ?? false),
            'auto_share' => (bool) ($data['auto_share'] ?? true),
        ]);

        return Reply::ok([
            'id' => (int) $contact->id,
            'name' => $contact->name,
            'phone' => $contact->phone,
            'relation' => $contact->relation,
            'is_primary' => (bool) $contact->is_primary,
            'auto_share' => (bool) $contact->auto_share,
        ], 201);
    }

    /** DELETE /driver/safety/contacts/{id} */
    public function destroyContact(Request $request, int $id): JsonResponse
    {
        $deleted = DriverSafetyContact::query()
            ->where('driver_id', (int) $request->user()->id)
            ->where('id', $id)
            ->delete();

        if ($deleted === 0) {
            throw DomainException::notFound();
        }

        return Reply::ok(['ok' => true]);
    }

    /** POST /driver/safety/sos */
    public function sos(Request $request): JsonResponse
    {
        $data = $request->validate([
            'booking_id' => ['nullable', 'integer'],
            'kind' => ['nullable', 'string', 'max:16'],
            'category' => ['nullable', 'string', 'max:32'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'hold_ms' => ['nullable', 'integer'],
            'note' => ['nullable', 'string'],
        ]);

        $driver = $request->user();
        $bookingId = $data['booking_id'] ?? null;

        // Attribute the SOS to the office that owns the BOOKING, not just the
        // driver's home office. A driver with no office (or on loan to another
        // office's ride) would otherwise write a null/wrong office_id, and that
        // ride's office console would never see the emergency. Fall back to the
        // driver's own office when there is no booking.
        $officeId = $this->resolveOfficeId($driver, $bookingId);

        $event = DriverSafetyEvent::query()->create([
            'driver_id' => (int) $driver->id,
            'booking_id' => $bookingId,
            'office_id' => $officeId,
            'kind' => $data['kind'] ?? 'sos',
            'category' => $data['category'] ?? null,
            'status' => 'open',
            'note' => $data['note'] ?? null,
            'lat' => $data['lat'] ?? null,
            'lng' => $data['lng'] ?? null,
            'hold_ms' => $data['hold_ms'] ?? null,
            'created_at' => now(),
        ]);

        // Actually fan the alert out in realtime. The response advertises
        // sharedWith:['fleetos','office','contacts'] — before this the row was
        // written but nothing was published, so no console was ever notified.
        // `admin` fulfils the sharedWith:'fleetos' promise above — the fleet ops
        // room now hears every SOS live. Additive: the driver/office/booking
        // rooms the apps consume are unchanged; no app subscribes to `admin`.
        $channels = [Channel::admin(), Channel::driver((int) $driver->id)];
        if ($officeId !== null) {
            $channels[] = Channel::office($officeId);
        }
        if ($bookingId !== null) {
            $channels[] = Channel::booking((int) $bookingId);
        }

        $this->events->emit(new DomainEvent(
            EventType::SUPPORT_MESSAGE_CREATED,
            $channels,
            [
                'kind' => 'sos',
                'event_id' => (int) $event->id,
                'driver_id' => (int) $driver->id,
                'booking_id' => $bookingId,
                'office_id' => $officeId,
                'lat' => $data['lat'] ?? null,
                'lng' => $data['lng'] ?? null,
            ]
        ));

        return Reply::ok([
            'id' => (int) $event->id,
            'status' => $event->status,
            'sharedWith' => ['fleetos', 'office', 'contacts'],
        ], 201);
    }

    /**
     * The office to route this SOS to: the booking's office when a booking is
     * named (so the ride's office is alerted even for a loaned/office-less
     * driver), otherwise the driver's own office.
     */
    private function resolveOfficeId(object $driver, ?int $bookingId): ?int
    {
        if ($bookingId !== null) {
            $booking = RideBooking::query()->find($bookingId);
            if ($booking !== null && $booking->office_id !== null) {
                return (int) $booking->office_id;
            }
        }

        return $driver->officeId !== null ? (int) $driver->officeId : null;
    }

    /** POST /driver/safety/sos/{id}/end */
    public function endSos(Request $request, int $id): JsonResponse
    {
        $event = DriverSafetyEvent::query()
            ->where('driver_id', (int) $request->user()->id)
            ->where('id', $id)
            ->first();

        if ($event === null) {
            throw DomainException::notFound();
        }

        $event->status = 'closed';
        $event->save();

        return Reply::ok(['id' => (int) $event->id, 'status' => $event->status]);
    }

    /** POST /driver/safety/status-links */
    public function createStatusLink(Request $request): JsonResponse
    {
        $data = $request->validate([
            'booking_id' => ['nullable', 'integer'],
        ]);

        $token = Str::random(32);
        $expiresAt = now()->addHours(4);

        $link = DriverStatusLink::query()->create([
            'driver_id' => (int) $request->user()->id,
            'booking_id' => $data['booking_id'] ?? null,
            'token' => $token,
            'expires_at' => $expiresAt,
            'created_at' => now(),
        ]);

        return Reply::ok([
            'id' => (int) $link->id,
            'url' => url('/s/' . $token),
            'expiresAt' => $expiresAt->toIso8601String(),
        ], 201);
    }

    /** DELETE /driver/safety/status-links/{id} */
    public function destroyStatusLink(Request $request, int $id): JsonResponse
    {
        $link = DriverStatusLink::query()
            ->where('driver_id', (int) $request->user()->id)
            ->where('id', $id)
            ->first();

        if ($link === null) {
            throw DomainException::notFound();
        }

        $link->revoked_at = now();
        $link->save();

        return Reply::ok(['ok' => true]);
    }
}
