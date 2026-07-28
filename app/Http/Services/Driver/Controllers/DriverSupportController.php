<?php

namespace App\Http\Services\Driver\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Settings\AppSettings;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Services\User\Support\Reply;
use App\Models\Driver;
use App\Models\DriverRepliesIssue;
use App\Models\DriversIssue;
use App\Models\Issue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Driver support.
 *
 * - Tickets are threaded conversations in the polymorphic `issues` table
 *   (`owner = Driver`), with a message thread in `replies` (FK → `issues`).
 * - Trip issues are one-shot reports in `drivers_issues` (reviewed by the office;
 *   no reply thread).
 */
class DriverSupportController extends Controller
{
    /** The FleetOS platform support/safety phone (configurable via the panel). */
    public function contact(): JsonResponse
    {
        return Reply::ok(['support_phone' => AppSettings::string('support_phone', '')]);
    }

    public function ticket(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'string', 'max:255'],
        ]);

        $issue = Issue::query()->create([
            'owner_type' => Driver::class,
            'owner_id' => (int) $request->user()->id,
            'subject' => $data['subject'],
            'description' => $data['description'] ?? '',
            'photo' => $data['photo'] ?? null,
            'mode' => 'support',
            'status' => 'open',
            'isClosed' => false,
            'priority' => 0,
        ]);

        return Reply::ok(['id' => (int) $issue->id, 'status' => $issue->status ?? 'open'], 201);
    }

    public function tripIssue(Request $request): JsonResponse
    {
        $data = $request->validate([
            'booking_id' => ['nullable', 'integer', 'min:1'],
            'subject' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'string', 'max:255'],
        ]);

        $desc = $data['description'] ?? '';
        if (isset($data['booking_id'])) {
            $desc = 'Booking #' . $data['booking_id'] . ' — ' . $desc;
        }

        $issue = DriversIssue::query()->create([
            'subject' => $data['subject'],
            'description' => $desc,
            'photo' => $data['photo'] ?? null,
            'driverId' => (int) $request->user()->id,
            'isClosed' => false,
        ]);

        return Reply::ok(['id' => (int) $issue->id, 'status' => 'open'], 201);
    }

    public function replies(Request $request, int $id): JsonResponse
    {
        $this->ownedIssue($request, $id);

        $items = DriverRepliesIssue::query()
            ->where('issueId', $id)
            ->orderBy('id')
            ->get()
            ->map(fn (DriverRepliesIssue $r) => [
                'id' => (int) $r->id,
                'sender_type' => class_basename((string) $r->sender_type),
                'sender_name' => $r->senderName,
                'body' => $r->content,
                'image' => $r->imageUrl,
                'created_at' => $r->created_at !== null ? $r->created_at->toIso8601ZuluString() : null,
            ])->all();

        return Reply::ok(['items' => $items]);
    }

    public function reply(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'string', 'max:255'],
        ]);

        $this->ownedIssue($request, $id);
        $driver = $request->user();

        $reply = DriverRepliesIssue::query()->create([
            'issueId' => $id,
            'sender_type' => Driver::class,
            'sender_id' => (int) $driver->id,
            'senderName' => trim(((string) $driver->firstName) . ' ' . ((string) $driver->lastName)),
            'content' => $data['body'],
            'imageUrl' => $data['image'] ?? null,
        ]);

        return Reply::ok([
            'id' => (int) $reply->id,
            'sender_type' => 'driver',
            'body' => $reply->content,
            'created_at' => $reply->created_at !== null ? $reply->created_at->toIso8601ZuluString() : null,
        ], 201);
    }

    /** The ticket must be an `issues` row owned by this driver. */
    private function ownedIssue(Request $request, int $id): Issue
    {
        $issue = Issue::query()
            ->where('id', $id)
            ->where('owner_type', Driver::class)
            ->where('owner_id', $request->user()->id)
            ->first();

        if ($issue === null) {
            throw DomainException::notFound();
        }

        return $issue;
    }
}
