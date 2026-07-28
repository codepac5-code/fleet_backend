<?php

namespace App\Http\Services\Driver\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Dispatch\DispatchService;
use App\Http\Services\User\Support\Reply;
use App\Models\DriverPresence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Driver availability (`POST /driver/presence`). Records a presence heartbeat so
 * the dispatcher can offer this driver rides; echoes `presence.changed`.
 */
class DriverPresenceController extends Controller
{
    public function __construct(private DispatchService $dispatch)
    {
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:online,off,busy'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'busy_reason' => ['nullable', 'string', 'max:40'],
        ]);

        $driver = $request->user();
        $presence = $this->dispatch->heartbeat(
            (int) $driver->id,
            $driver->officeId !== null ? (int) $driver->officeId : null,
            $data['status'],
            isset($data['lat']) ? (float) $data['lat'] : null,
            isset($data['lng']) ? (float) $data['lng'] : null,
            $data['busy_reason'] ?? null,
        );

        // Fold the transition into the online-time accumulators. Re-read the row
        // so the prior session state (untouched by heartbeat) is accurate.
        $row = DriverPresence::query()->find((int) $driver->id);
        if ($row !== null) {
            $row->accumulateOnline($data['status']);
            $row->save();
        }

        return Reply::ok(['status' => $presence->status ?? $data['status']]);
    }
}
