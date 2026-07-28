<?php

namespace App\Http\Services\Shared\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\Support\Reply;
use App\Models\Driver;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Push-token registration shared by the rider + driver apps
 * (`POST /devices`, guarded by `auth:user,driver`). The owner is derived from
 * the authenticated identity; the token is upserted so re-registering a device
 * just refreshes `last_seen_at`.
 */
class DeviceRegistrationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:16'],
            'owner_type' => ['nullable', 'string', 'max:32'],
        ]);

        $user = $request->user();
        $ownerType = $user instanceof Driver ? 'driver' : 'user';

        $device = DeviceToken::query()->updateOrCreate(
            ['token' => $data['token']],
            [
                'owner_type' => $ownerType,
                'owner_id' => (int) $user->id,
                'platform' => $data['platform'] ?? null,
                'last_seen_at' => now(),
            ],
        );

        return Reply::ok([
            'id' => (int) $device->id,
            'platform' => $device->platform,
            'last_seen_at' => optional($device->last_seen_at)->toIso8601String(),
        ], 201);
    }
}
