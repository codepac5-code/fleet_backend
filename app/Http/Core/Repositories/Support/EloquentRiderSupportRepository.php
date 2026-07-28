<?php

namespace App\Http\Core\Repositories\Support;

use App\Http\Core\Const\Support\SupportLayer;
use App\Models\RiderSupportMessage;
use App\Models\RiderSupportTicket;
use Illuminate\Support\Collection;

class EloquentRiderSupportRepository implements RiderSupportRepository
{
    public function createTicket(array $attributes): RiderSupportTicket
    {
        return RiderSupportTicket::query()->create($attributes);
    }

    public function find(int $ticketId): ?RiderSupportTicket
    {
        return RiderSupportTicket::query()->find($ticketId);
    }

    public function officeLayer(int $officeId, ?string $status): Collection
    {
        $query = RiderSupportTicket::query()
            ->where('layer', SupportLayer::OFFICE)
            ->where('office_id', $officeId)
            ->orderByDesc('last_message_at')
            ->orderByDesc('id');

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        return $query->get();
    }

    public function fleetLayer(?string $status, ?string $category): Collection
    {
        $query = RiderSupportTicket::query()
            ->where('layer', SupportLayer::FLEETOS)
            ->orderByDesc('last_message_at')
            ->orderByDesc('id');

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        if ($category !== null && $category !== '') {
            $query->where('category', $category);
        }

        return $query->get();
    }

    public function findForUser(int $ticketId, int $userId): ?RiderSupportTicket
    {
        return RiderSupportTicket::query()->where('id', $ticketId)->where('user_id', $userId)->first();
    }

    public function listForUser(int $userId, ?string $status): Collection
    {
        $query = RiderSupportTicket::query()
            ->where('user_id', $userId)
            ->orderByDesc('last_message_at')
            ->orderByDesc('id');

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        return $query->get();
    }

    public function saveTicket(RiderSupportTicket $ticket): void
    {
        $ticket->save();
    }

    public function addMessage(array $attributes): RiderSupportMessage
    {
        return RiderSupportMessage::query()->create($attributes);
    }

    public function messagesFor(int $ticketId): Collection
    {
        return RiderSupportMessage::query()->where('ticket_id', $ticketId)->orderBy('id')->get();
    }
}
