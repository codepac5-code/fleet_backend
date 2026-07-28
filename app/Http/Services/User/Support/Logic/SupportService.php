<?php

namespace App\Http\Services\User\Support\Logic;

use App\Http\Core\Classes\Support\RiderSupportService;
use App\Http\Core\Exceptions\DomainException;
use App\Models\Complaint;
use App\Models\HelpSuggestion;
use App\Models\RiderSupportMessage;
use App\Models\RiderSupportTicket;
use Illuminate\Support\Str;

class SupportService
{
    public function __construct(private RiderSupportService $support)
    {
    }

    public function openTicket(int $userId, string $topic, ?int $tripId, string $message): array
    {
        $result = $this->support->open($userId, $topic, $tripId, $topic, $message);

        return ['ticketId' => $result['ticket_id'], 'status' => $result['status']];
    }

    public function listTickets(int $userId): array
    {
        return RiderSupportTicket::query()
            ->where('user_id', $userId)
            ->orderByDesc('last_message_at')
            ->get()
            ->map(fn (RiderSupportTicket $t) => $this->ticketRow($t, true))
            ->all();
    }

    public function showTicket(int $userId, int $ticketId): array
    {
        $ticket = RiderSupportTicket::query()->where('id', $ticketId)->where('user_id', $userId)->first();

        if ($ticket === null) {
            throw DomainException::notFound();
        }

        return $this->ticketRow($ticket, true);
    }

    public function complaint(int $userId, string $about, ?int $tripId, string $description, ?string $photoUrl): array
    {
        $routedTo = $about === 'driver' ? 'office' : 'fleetos';
        $priority = $about === 'safety' ? 'urgent' : 'normal';

        $complaint = Complaint::query()->create([
            'user_id' => $userId,
            'booking_id' => $tripId,
            'about' => $about,
            'description' => $description,
            'photo_url' => $photoUrl,
            'routed_to' => $routedTo,
            'priority' => $priority,
            'case_ref' => 'C-' . strtoupper(Str::random(8)),
            'status' => 'open',
        ]);

        return [
            'id' => (int) $complaint->id,
            'routed_to' => $complaint->routed_to,
            'priority' => $complaint->priority,
            'case_ref' => $complaint->case_ref,
            'status' => $complaint->status,
        ];
    }

    public function helpList(?string $category): array
    {
        return HelpSuggestion::query()
            ->when($category !== null, fn ($q) => $q->where('category', $category))
            ->orderByDesc('priority')
            ->get()
            ->map(fn (HelpSuggestion $a) => [
                'id' => (int) $a->id,
                'category' => $a->category,
                'title' => $this->localized($a, 'title'),
                'readMinutes' => $a->read_minutes !== null ? (int) $a->read_minutes : null,
            ])
            ->all();
    }

    public function helpShow(int $id): array
    {
        $article = HelpSuggestion::query()->find($id);

        if ($article === null) {
            throw DomainException::notFound();
        }

        return [
            'id' => (int) $article->id,
            'category' => $article->category,
            'title' => $this->localized($article, 'title'),
            'description' => $this->localized($article, 'description'),
            'read_minutes' => $article->read_minutes !== null ? (int) $article->read_minutes : null,
            'priority' => $article->priority !== null ? (int) $article->priority : null,
            'target_user' => $article->target_user,
        ];
    }

    private function localized(HelpSuggestion $article, string $base): ?string
    {
        $en = $article->{$base . '_en'} ?? null;

        return app()->getLocale() === 'en' && $en ? $en : ($article->{$base} ?? $en);
    }

    private function ticketRow(RiderSupportTicket $t, bool $withMessages): array
    {
        $messages = $withMessages
            ? RiderSupportMessage::query()->where('ticket_id', $t->id)->orderBy('id')->get()->map(fn (RiderSupportMessage $m) => [
                'id' => (int) $m->id,
                'ticket_id' => (int) $m->ticket_id,
                'sender_type' => $m->sender_type,
                'sender_id' => $m->sender_id !== null ? (int) $m->sender_id : null,
                'body' => $m->body,
                'created_at' => $m->created_at !== null ? $m->created_at->toIso8601ZuluString() : null,
            ])->all()
            : [];

        return [
            'id' => (int) $t->id,
            'user_id' => (int) $t->user_id,
            'tripId' => $t->booking_id !== null ? (int) $t->booking_id : null,
            'office_id' => $t->office_id !== null ? (int) $t->office_id : null,
            'category' => $t->category,
            'topic' => $t->topic ?? $t->category,
            'layer' => $t->layer,
            'subject' => $t->subject,
            'status' => $t->status,
            'last_message_at' => $t->last_message_at !== null ? $t->last_message_at->toIso8601ZuluString() : null,
            'messages' => $messages,
        ];
    }
}
