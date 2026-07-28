<?php

namespace App\Http\Services\Driver\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\Support\Reply;
use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Driver notification inbox (`GET /driver/notifications`, `POST /driver/notifications/read`).
 * Reads the shared `app_notifications` table scoped to `notifiable_type = driver`.
 */
class DriverNotificationsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $driverId = (int) $request->user()->id;
        $filter = (string) $request->query('filter', 'all');

        $query = AppNotification::query()
            ->where('notifiable_type', 'driver')
            ->where('notifiable_id', $driverId);

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        $rows = $query->orderByDesc('id')->limit(50)->get();

        return Reply::ok([
            'items' => $rows->map(fn (AppNotification $n) => [
                'id' => (int) $n->id,
                'type' => $n->type,
                'template_key' => $n->template_key,
                'title' => $n->title,
                'body' => $n->body,
                'data' => $n->data,
                'read_at' => $n->read_at !== null ? $n->read_at->toIso8601ZuluString() : null,
                'created_at' => $n->created_at !== null ? $n->created_at->toIso8601ZuluString() : null,
            ])->all(),
            'nextCursor' => null,
        ]);
    }

    public function read(Request $request): JsonResponse
    {
        $driverId = (int) $request->user()->id;

        $updated = AppNotification::query()
            ->where('notifiable_type', 'driver')
            ->where('notifiable_id', $driverId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return Reply::ok(['updated' => $updated]);
    }
}
