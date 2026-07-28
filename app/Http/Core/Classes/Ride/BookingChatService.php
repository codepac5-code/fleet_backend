<?php

namespace App\Http\Core\Classes\Ride;

use App\Http\Core\Classes\Event\DomainEvent;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventType;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Core\Repositories\Dispatch\DispatchJobRepository;
use App\Http\Core\Repositories\Ledger\CommissionSnapshotRepository;
use App\Http\Core\Repositories\Ride\BookingChatRepository;
use App\Models\BookingChatMessage;

class BookingChatService
{
    const RIDER = 'rider';
    const DRIVER = 'driver';

    public function __construct(
        private BookingChatRepository $repository,
        private DispatchJobRepository $jobs,
        private CommissionSnapshotRepository $settlements,
        private ?EventBus $events = null
    ) {
    }

    public function messages(int $bookingId, ?int $beforeId, int $limit): array
    {
        return $this->repository->messages($bookingId, $beforeId, $limit)
            ->reverse()
            ->map(fn (BookingChatMessage $m) => $this->present($m))
            ->values()
            ->all();
    }

    public function send(int $bookingId, string $fromType, string $body): array
    {
        $body = trim($body);

        if ($body === '') {
            throw DomainException::make('empty_message');
        }

        $this->assertChatOpen($bookingId);

        $message = $this->repository->create([
            'booking_id' => $bookingId,
            'from_type' => $fromType,
            'body' => $body,
        ]);

        if ($this->events !== null) {
            $this->events->emit(new DomainEvent(
                EventType::BOOKING_CHAT_MESSAGE,
                [Channel::booking($bookingId)],
                [
                    'booking_id' => $bookingId,
                    'message_id' => (int) $message->id,
                    'from' => $fromType,
                    'sender' => $fromType,
                    'sender_role' => $fromType,
                    'body' => $body,
                    'text' => $body,
                    'created_at' => optional($message->created_at)->toIso8601ZuluString(),
                ]
            ));
        }

        return $this->present($message);
    }

    public function read(int $bookingId, string $readerType): void
    {
        $this->repository->markRead($bookingId, $readerType);
    }

    private function assertChatOpen(int $bookingId): void
    {
        if ($this->jobs->activeAssignment($bookingId) === null) {
            throw DomainException::make('chat_unavailable', 403);
        }

        if ($this->settlements->existsForBooking($bookingId)) {
            throw DomainException::make('chat_closed', 403);
        }
    }

    private function present(BookingChatMessage $message): array
    {
        return [
            'id' => (int) $message->id,
            'from' => $message->from_type,
            'body' => $message->body,
            'read' => $message->read_at !== null,
            'at' => optional($message->created_at)->toIso8601String(),
        ];
    }
}
