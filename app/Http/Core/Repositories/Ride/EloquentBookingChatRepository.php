<?php

namespace App\Http\Core\Repositories\Ride;

use App\Models\BookingChatMessage;
use Illuminate\Support\Collection;

class EloquentBookingChatRepository implements BookingChatRepository
{
    public function messages(int $bookingId, ?int $beforeId, int $limit): Collection
    {
        $query = BookingChatMessage::query()->where('booking_id', $bookingId);

        if ($beforeId !== null) {
            $query->where('id', '<', $beforeId);
        }

        return $query->orderByDesc('id')->limit($limit)->get();
    }

    public function create(array $attributes): BookingChatMessage
    {
        return BookingChatMessage::query()->create($attributes);
    }

    public function markRead(int $bookingId, string $readerType): void
    {
        BookingChatMessage::query()
            ->where('booking_id', $bookingId)
            ->where('from_type', '!=', $readerType)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
