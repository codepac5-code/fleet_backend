<?php

namespace App\Http\Services\User\Trips\Logic;

use App\Http\Core\Classes\Marketplace\FavoriteOfficeService;
use App\Http\Core\Classes\Rating\RatingService;
use App\Http\Core\Classes\Ride\BookingChatService;
use App\Http\Core\Classes\Support\RiderSupportService;
use App\Http\Core\Const\Ride\BookingStatus;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Core\Repositories\Dispatch\DispatchJobRepository;
use App\Http\Core\Repositories\Ride\RideBookingRepository;
use App\Http\Services\User\Support\Cursor;
use App\Http\Services\User\Support\Presenters\BookingPresenter;
use App\Http\Services\User\Support\Presenters\OfficePresenter;
use App\Models\BookingChatMessage;
use App\Models\LostItem;
use App\Models\Office;
use App\Models\RideBooking;

class TripService
{
    public function __construct(
        private RideBookingRepository $bookings,
        private RatingService $ratings,
        private DispatchJobRepository $jobs,
        private FavoriteOfficeService $favorites,
        private BookingChatService $chat,
        private RiderSupportService $support,
        private \App\Http\Core\Classes\Support\LostFoundService $lostFound = new \App\Http\Core\Classes\Support\LostFoundService()
    ) {
    }

    public function history(int $userId, ?string $status, ?string $cursor, $limit): array
    {
        $filter = match ($status) {
            'completed', 'past' => 'completed',
            'cancelled' => 'cancelled',
            default => 'active',
        };

        $limit = Cursor::limit($limit);
        $rows = $this->bookings->history($userId, $filter, Cursor::decode($cursor), $limit);

        $hasMore = $rows->count() > $limit;
        $items = $rows->take($limit);

        $offices = Office::query()->whereIn('id', $items->pluck('office_id')->unique()->all())->get()->keyBy('id');

        $data = $items->map(fn (RideBooking $b) => BookingPresenter::listRow(
            $b,
            null,
            $offices->has((int) $b->office_id) ? OfficePresenter::card($offices->get((int) $b->office_id)) : null
        ))->values()->all();

        return [
            'items' => $data,
            'nextCursor' => $hasMore ? Cursor::encode((int) $items->last()->id) : null,
        ];
    }

    public function rate(int $userId, int $bookingId, array $v): array
    {
        $booking = $this->owned($userId, $bookingId);

        // A ride can only be rated once it has actually happened. Without this
        // guard a booking still in `matching` (no driver, no trip) could be
        // rated — recording a driver/office rating for a trip that never ran.
        if ($booking->status !== BookingStatus::COMPLETED) {
            throw DomainException::make('ride_not_rateable', 409);
        }

        $stars = (int) $v['stars'];
        $comment = $v['comment'] ?? null;
        $tags = $v['tags'] ?? [];
        $bookAgain = array_key_exists('bookAgain', $v) ? (bool) $v['bookAgain'] : null;
        $favorite = ! empty($v['favorite']);

        $job = $this->jobs->withAssignedDriver($bookingId);

        if ($job !== null && $job->assigned_driver_id !== null) {
            $driverRating = $this->ratings->rate($bookingId, 'user', $userId, 'driver', (int) $job->assigned_driver_id, $stars, $comment);
            $driverRating->tags = $tags;
            $driverRating->book_again = $bookAgain;
            $driverRating->favorite = $favorite;
            $driverRating->save();
        }

        $this->ratings->rate($bookingId, 'user', $userId, 'office', (int) $booking->office_id, $stars, $comment);

        if ($favorite) {
            $this->favorites->add($userId, (int) $booking->office_id);
        }

        $booking->rated_at = now();
        $this->bookings->save($booking);

        return ['ok' => true];
    }

    public function lostItem(int $userId, int $bookingId, array $v): array
    {
        $booking = $this->owned($userId, $bookingId);

        $category = (string) $v['category'];
        $description = (string) ($v['description'] ?? '');

        // Description is optional (per contract); the support ticket still needs a
        // non-empty body, so fall back to the category when none was provided.
        $ticketBody = $description !== '' ? $description : ('Lost item reported: ' . $category);

        $ticket = $this->support->open($userId, 'lost_item', $bookingId, 'Lost item: ' . $category, $ticketBody);

        // Governed lost & found: files a RIDER report on the trip's office so the
        // office can match it to a driver's found-item report and arbitrate.
        $this->lostFound->reportLost($userId, $bookingId, (int) $booking->office_id, [
            'ticket_id' => $ticket['ticket_id'] ?? null,
            'category' => $category,
            'description' => $description,
            'share_masked_number' => ! empty($v['shareMaskedNumber']),
        ]);

        return [
            'ticketId' => $ticket['ticket_id'] ?? null,
            'status' => $ticket['status'] ?? 'open',
        ];
    }

    /** The rider's lost-item reports with their governed lifecycle status. */
    public function lostItems(int $userId): array
    {
        return $this->lostFound->forRider($userId);
    }

    /** The rider withdraws their own lost-item report. */
    public function cancelLostItem(int $userId, int $itemId): array
    {
        $item = $this->lostFound->cancel(\App\Http\Core\Const\LostItemStatus::REPORTER_RIDER, $userId, $itemId);

        return \App\Http\Core\Classes\Support\LostFoundService::present($item);
    }

    public function messages(int $userId, int $bookingId, ?string $cursor, $limit): array
    {
        $this->owned($userId, $bookingId);

        $limit = Cursor::limit($limit);
        $beforeId = Cursor::decode($cursor);

        $rows = BookingChatMessage::query()
            ->where('booking_id', $bookingId)
            ->when($beforeId !== null, fn ($q) => $q->where('id', '<', $beforeId))
            ->orderByDesc('id')
            ->limit($limit + 1)
            ->get();

        $hasMore = $rows->count() > $limit;
        $items = $rows->take($limit);

        $data = $items->reverse()->map(fn (BookingChatMessage $m) => $this->presentMessage($m))->values()->all();

        return [
            'items' => $data,
            'nextCursor' => $hasMore ? Cursor::encode((int) $items->last()->id) : null,
        ];
    }

    public function sendMessage(int $userId, int $bookingId, string $body): array
    {
        $this->owned($userId, $bookingId);

        $result = $this->chat->send($bookingId, BookingChatService::RIDER, $body);
        $message = BookingChatMessage::query()->find((int) $result['id']);

        return $this->presentMessage($message);
    }

    private function presentMessage(BookingChatMessage $m): array
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

    private function owned(int $userId, int $bookingId): RideBooking
    {
        $booking = $this->bookings->findForUser($bookingId, $userId);

        if ($booking === null) {
            throw DomainException::notFound();
        }

        return $booking;
    }
}
