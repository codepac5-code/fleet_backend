<?php

namespace App\Http\Core\Repositories\Support;

use App\Models\RiderSupportMessage;
use App\Models\RiderSupportTicket;
use Illuminate\Support\Collection;

interface RiderSupportRepository
{
    public function createTicket(array $attributes): RiderSupportTicket;

    public function find(int $ticketId): ?RiderSupportTicket;

    public function officeLayer(int $officeId, ?string $status): Collection;

    public function fleetLayer(?string $status, ?string $category): Collection;

    public function findForUser(int $ticketId, int $userId): ?RiderSupportTicket;

    public function listForUser(int $userId, ?string $status): Collection;

    public function saveTicket(RiderSupportTicket $ticket): void;

    public function addMessage(array $attributes): RiderSupportMessage;

    public function messagesFor(int $ticketId): Collection;
}
