<?php

namespace App\Http\Services\Shared\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Ride\BookingChatService;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Services\User\Support\Cursor;
use App\Http\Services\User\Support\Reply;
use App\Models\BookingChatMessage;
use App\Models\RideBooking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Shared driver<->rider trip chat (`/bookings/{id}/chat`). Authenticated by
 * EITHER guard; `from_type` is inferred from the authenticated principal, who
 * must be a participant in the booking.
 */
class BookingChatController extends Controller
{
    public function __construct(private BookingChatService $chat)
    {
    }

    /** @return array{0:string,1:int} [type ('rider'|'driver'), id] */
    private function principal(): array
    {
        if (Auth::guard('driver')->check()) {
            return [BookingChatService::DRIVER, (int) Auth::guard('driver')->id()];
        }

        return [BookingChatService::RIDER, (int) Auth::guard('user')->id()];
    }

    private function assertParticipant(int $bookingId, string $type, int $id): void
    {
        $b = RideBooking::query()->find($bookingId);
        $ok = $b !== null && (
            ($type === BookingChatService::RIDER && (int) $b->user_id === $id) ||
            ($type === BookingChatService::DRIVER && (int) $b->driver_id === $id)
        );

        if (! $ok) {
            throw DomainException::notFound();
        }
    }

    public function history(Request $request, int $id): JsonResponse
    {
        [$type, $pid] = $this->principal();
        $this->assertParticipant($id, $type, $pid);

        $limit = Cursor::limit($request->query('limit'));
        $beforeId = Cursor::decode($request->query('cursor') !== null ? (string) $request->query('cursor') : null);

        $rows = BookingChatMessage::query()
            ->where('booking_id', $id)
            ->when($beforeId !== null, fn ($q) => $q->where('id', '<', $beforeId))
            ->orderByDesc('id')
            ->limit($limit + 1)
            ->get();

        $hasMore = $rows->count() > $limit;
        $items = $rows->take($limit);

        return Reply::ok([
            'items' => $items->reverse()->map(fn (BookingChatMessage $m) => $this->present($m))->values()->all(),
            'nextCursor' => $hasMore ? Cursor::encode((int) $items->last()->id) : null,
        ]);
    }

    public function send(Request $request, int $id): JsonResponse
    {
        [$type, $pid] = $this->principal();
        $this->assertParticipant($id, $type, $pid);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'client_msg_id' => ['nullable', 'string', 'max:64'],
        ]);

        $result = $this->chat->send($id, $type, $data['body']);
        $message = BookingChatMessage::query()->find((int) $result['id']);

        return Reply::ok($this->present($message), 201);
    }

    public function read(Request $request, int $id): JsonResponse
    {
        [$type, $pid] = $this->principal();
        $this->assertParticipant($id, $type, $pid);

        $this->chat->read($id, $type);

        return Reply::ok(['ok' => true, 'read_at' => now()->toIso8601ZuluString()]);
    }

    private function present(BookingChatMessage $m): array
    {
        return [
            'id' => (int) $m->id,
            'booking_id' => (int) $m->booking_id,
            'from_type' => $m->from_type,
            'body' => $m->body,
            'read_at' => $m->read_at !== null ? $m->read_at->toIso8601ZuluString() : null,
            'created_at' => $m->created_at !== null ? $m->created_at->toIso8601ZuluString() : null,
        ];
    }
}
