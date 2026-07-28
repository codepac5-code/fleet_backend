<?php

namespace App\Http\Core\Classes\Support;

use App\Http\Core\Classes\Event\DomainEvent;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Classes\Ride\OfficeReadModel;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventType;
use App\Http\Core\Const\Support\SupportActor;
use App\Http\Core\Const\Support\SupportLayer;
use App\Http\Core\Const\Support\SupportStatus;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Core\Repositories\Ride\RideBookingRepository;
use App\Http\Core\Repositories\Support\RiderSupportRepository;
use App\Models\RiderSupportMessage;
use App\Models\RiderSupportTicket;

class RiderSupportService
{
    public function __construct(
        private RiderSupportRepository $repository,
        private RideBookingRepository $bookings,
        private OfficeReadModel $offices,
        private ?EventBus $events = null
    ) {
    }

    public function callInfo(int $userId, int $bookingId): array
    {
        $booking = $this->bookings->findForUser($bookingId, $userId);

        if ($booking === null) {
            throw DomainException::notFound();
        }

        return $this->offices->contact((int) $booking->office_id);
    }

    public function open(int $userId, string $category, ?int $bookingId, string $subject, string $body): array
    {
        $body = trim($body);

        if ($subject === '' || $body === '') {
            throw DomainException::make('validation_failed');
        }

        $layer = SupportLayer::forCategory($category);
        $officeId = $this->officeFor($userId, $bookingId, $layer);

        $ticket = $this->repository->createTicket([
            'user_id' => $userId,
            'booking_id' => $bookingId,
            'office_id' => $officeId,
            'category' => $category,
            'layer' => $layer,
            'subject' => $subject,
            'status' => SupportStatus::OPEN,
            'last_message_at' => now(),
        ]);

        $this->store((int) $ticket->id, 'user', $userId, $body);
        $this->notify($ticket, $body);

        return $this->present($ticket);
    }

    public function report(int $userId, ?int $bookingId, string $kind, string $note): array
    {
        return $this->open($userId, 'safety_report', $bookingId, 'Report: ' . $kind, $note !== '' ? $note : $kind);
    }

    public function sos(int $userId, ?int $bookingId, float $lat, float $lng): array
    {
        $ticket = $this->open($userId, 'sos', $bookingId, 'SOS', 'Emergency at ' . $lat . ',' . $lng);

        if ($this->events !== null) {
            // Fleet admin + the rider always hear a rider SOS; the booking room
            // is added when the SOS happened inside a trip. Previously a
            // trip-less SOS published nothing at all.
            $channels = [Channel::admin(), Channel::user($userId)];

            if ($bookingId !== null) {
                $channels[] = Channel::booking($bookingId);
            }

            $this->events->emit(new DomainEvent(
                EventType::SUPPORT_MESSAGE_CREATED,
                $channels,
                ['ticket_id' => $ticket['ticket_id'], 'kind' => 'sos', 'lat' => $lat, 'lng' => $lng]
            ));
        }

        return $ticket;
    }

    public function list(int $userId, ?string $status): array
    {
        return $this->repository->listForUser($userId, $status)
            ->map(fn (RiderSupportTicket $t) => $this->present($t))
            ->all();
    }

    public function show(int $userId, int $ticketId): array
    {
        $ticket = $this->owned($userId, $ticketId);

        $messages = $this->repository->messagesFor($ticketId)
            ->map(fn (RiderSupportMessage $m) => [
                'id' => (int) $m->id,
                'from' => $m->sender_type,
                'body' => $m->body,
                'at' => optional($m->created_at)->toIso8601String(),
            ])
            ->all();

        return array_merge($this->present($ticket), ['messages' => $messages]);
    }

    public function reply(int $userId, int $ticketId, string $body): array
    {
        $body = trim($body);

        if ($body === '') {
            throw DomainException::make('validation_failed');
        }

        $ticket = $this->owned($userId, $ticketId);

        // A closed ticket is terminal — a new problem needs a new ticket, not a
        // silent reopen of a case staff considered done.
        if (SupportStatus::isTerminal((string) $ticket->status)) {
            throw DomainException::make('ticket_closed', 422);
        }

        $this->store($ticketId, 'user', $userId, $body);

        $ticket->last_message_at = now();

        // A rider reply pulls the case back to the staff queue.
        if ($ticket->status === SupportStatus::RESOLVED || $ticket->status === SupportStatus::PENDING) {
            $ticket->status = SupportStatus::OPEN;
        }

        $this->repository->saveTicket($ticket);
        $this->notify($ticket, $body);

        return $this->present($ticket);
    }

    public function officeTickets(int $officeId, ?string $status): array
    {
        return $this->repository->officeLayer($officeId, $status)
            ->map(fn (RiderSupportTicket $t) => $this->present($t))
            ->all();
    }

    public function fleetTickets(?string $status, ?string $category): array
    {
        return $this->repository->fleetLayer($status, $category)
            ->map(fn (RiderSupportTicket $t) => $this->present($t))
            ->all();
    }

    public function thread(int $ticketId): array
    {
        $ticket = $this->find($ticketId);

        $messages = $this->repository->messagesFor($ticketId)
            ->map(fn (RiderSupportMessage $m) => [
                'id' => (int) $m->id,
                'from' => $m->sender_type,
                'body' => $m->body,
                'at' => optional($m->created_at)->toIso8601String(),
            ])
            ->all();

        return array_merge($this->present($ticket), ['messages' => $messages]);
    }

    public function staffReply(int $ticketId, string $senderType, int $senderId, string $body): array
    {
        $body = trim($body);

        if ($body === '') {
            throw DomainException::make('validation_failed');
        }

        $ticket = $this->find($ticketId);

        if (SupportStatus::isTerminal((string) $ticket->status)) {
            throw DomainException::make('ticket_closed', 422);
        }

        $this->store($ticketId, $senderType, $senderId, $body);

        $ticket->last_message_at = now();

        // Staff following up on a resolved ticket puts it back in progress.
        if ($ticket->status === SupportStatus::RESOLVED) {
            $ticket->status = SupportStatus::OPEN;
        }

        $this->repository->saveTicket($ticket);
        $this->notifyRider($ticket, $body);

        return $this->present($ticket);
    }

    public function officeThread(int $officeId, int $ticketId): array
    {
        $this->assertOfficeOwns($officeId, $ticketId);

        return $this->thread($ticketId);
    }

    public function officeReply(int $officeId, int $ticketId, int $agentId, string $body): array
    {
        $this->assertOfficeOwns($officeId, $ticketId);

        return $this->staffReply($ticketId, 'office', $agentId, $body);
    }

    private function assertOfficeOwns(int $officeId, int $ticketId): void
    {
        $ticket = $this->find($ticketId);

        if ($ticket->layer !== SupportLayer::OFFICE || (int) $ticket->office_id !== $officeId) {
            throw DomainException::notFound();
        }
    }

    /**
     * GOVERNED status change: the target must be a valid status, the move must be
     * a legal edge of the machine, and the acting role must be allowed to drive
     * it. Setting the same status is an idempotent no-op. Anything else is
     * refused with a governance error rather than silently written.
     */
    public function setStatus(int $ticketId, string $status, string $actorRole = SupportActor::FLEETOS): array
    {
        if (! SupportStatus::isValid($status)) {
            throw DomainException::make('invalid_status');
        }

        $ticket = $this->find($ticketId);
        $from = (string) $ticket->status;

        if ($from === $status) {
            return $this->present($ticket); // no-op
        }

        if (! SupportStatus::roleCanTransition($actorRole, $from, $status)) {
            throw DomainException::make('illegal_support_transition', 422);
        }

        $ticket->status = $status;
        $this->repository->saveTicket($ticket);
        $this->notifyStatus($ticket, $actorRole);

        return $this->present($ticket);
    }

    /** Office-scoped status change: the office may only govern its OWN tickets. */
    public function officeSetStatus(int $officeId, int $ticketId, string $status): array
    {
        $this->assertOfficeOwns($officeId, $ticketId);

        return $this->setStatus($ticketId, $status, SupportActor::OFFICE);
    }

    /** Office-scoped escalation: the office may only escalate its OWN tickets. */
    public function officeEscalate(int $officeId, int $ticketId, ?string $note = null): array
    {
        $this->assertOfficeOwns($officeId, $ticketId);

        return $this->escalate($ticketId, SupportActor::OFFICE, $note);
    }

    /**
     * GOVERNED escalation: hand an OFFICE-desk ticket up to the FleetOS platform
     * desk. Only office/fleetos staff may escalate, only an office-layer ticket
     * that isn't terminal, and it lands OPEN on the platform layer (no office
     * scope) so it re-enters the queue for a platform agent.
     */
    public function escalate(int $ticketId, string $actorRole = SupportActor::OFFICE, ?string $note = null): array
    {
        if (! SupportActor::isStaff($actorRole)) {
            throw DomainException::make('forbidden', 403);
        }

        $ticket = $this->find($ticketId);

        if ($ticket->layer !== SupportLayer::OFFICE) {
            throw DomainException::make('not_escalatable', 422);
        }

        if (SupportStatus::isTerminal((string) $ticket->status)) {
            throw DomainException::make('ticket_closed', 422);
        }

        $ticket->layer = SupportLayer::FLEETOS;
        $ticket->office_id = null;
        $ticket->status = SupportStatus::OPEN;
        $this->repository->saveTicket($ticket);

        if ($note !== null && trim($note) !== '') {
            $this->store($ticketId, $actorRole, 0, trim($note));
        }

        $this->notifyStatus($ticket, $actorRole);

        return $this->present($ticket);
    }

    private function find(int $ticketId): RiderSupportTicket
    {
        $ticket = $this->repository->find($ticketId);

        if ($ticket === null) {
            throw DomainException::notFound();
        }

        return $ticket;
    }

    private function notifyRider(RiderSupportTicket $ticket, string $body): void
    {
        if ($this->events === null) {
            return;
        }

        $this->events->emit(new DomainEvent(
            EventType::SUPPORT_MESSAGE_CREATED,
            [Channel::user((int) $ticket->user_id)],
            ['ticket_id' => (int) $ticket->id, 'body' => $body, 'created_at' => now()->toIso8601ZuluString()]
        ));
    }

    /** Tell the rider their ticket's state changed (resolved/closed/escalated/reopened). */
    private function notifyStatus(RiderSupportTicket $ticket, string $actorRole): void
    {
        if ($this->events === null) {
            return;
        }

        $this->events->emit(new DomainEvent(
            EventType::SUPPORT_MESSAGE_CREATED,
            [Channel::user((int) $ticket->user_id)],
            [
                'ticket_id' => (int) $ticket->id,
                'status' => (string) $ticket->status,
                'layer' => (string) $ticket->layer,
                'by' => $actorRole,
            ]
        ));
    }

    private function officeFor(int $userId, ?int $bookingId, string $layer): ?int
    {
        if ($layer !== SupportLayer::OFFICE || $bookingId === null) {
            return null;
        }

        $booking = $this->bookings->findForUser($bookingId, $userId);

        return $booking !== null ? (int) $booking->office_id : null;
    }

    private function owned(int $userId, int $ticketId): RiderSupportTicket
    {
        $ticket = $this->repository->findForUser($ticketId, $userId);

        if ($ticket === null) {
            throw DomainException::notFound();
        }

        return $ticket;
    }

    private function notify(RiderSupportTicket $ticket, string $body): void
    {
        // SOS carries its own richer realtime emit (with coordinates) in sos();
        // don't double-publish it here.
        if ($this->events === null || $ticket->category === 'sos') {
            return;
        }

        // Office-desk tickets ring the owning office (unchanged); everything on
        // the FleetOS layer — refund/payment/safety and any office-less ticket —
        // rings the fleet admin room, which previously heard nothing.
        // Staff-facing only (rider is notified elsewhere), so this is additive.
        $channel = ($ticket->layer === SupportLayer::OFFICE && $ticket->office_id !== null)
            ? Channel::office((int) $ticket->office_id)
            : Channel::admin();

        $this->events->emit(new DomainEvent(
            EventType::SUPPORT_MESSAGE_CREATED,
            [$channel],
            ['ticket_id' => (int) $ticket->id, 'body' => $body, 'layer' => (string) $ticket->layer]
        ));
    }

    private function store(int $ticketId, string $senderType, int $senderId, string $body): void
    {
        $this->repository->addMessage([
            'ticket_id' => $ticketId,
            'sender_type' => $senderType,
            'sender_id' => $senderId,
            'body' => $body,
            'created_at' => now(),
        ]);
    }

    private function present(RiderSupportTicket $ticket): array
    {
        return [
            'ticket_id' => (int) $ticket->id,
            'category' => $ticket->category,
            'layer' => $ticket->layer,
            'subject' => $ticket->subject,
            'status' => $ticket->status,
            'booking_id' => $ticket->booking_id !== null ? (int) $ticket->booking_id : null,
            'office_id' => $ticket->office_id !== null ? (int) $ticket->office_id : null,
            'last_reply_at' => optional($ticket->last_message_at)->toIso8601String(),
        ];
    }
}
