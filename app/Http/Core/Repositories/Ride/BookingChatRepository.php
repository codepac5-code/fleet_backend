<?php

namespace App\Http\Core\Repositories\Ride;

use App\Models\BookingChatMessage;
use Illuminate\Support\Collection;

interface BookingChatRepository
{
    public function messages(int $bookingId, ?int $beforeId, int $limit): Collection;

    public function create(array $attributes): BookingChatMessage;

    public function markRead(int $bookingId, string $readerType): void;
}
