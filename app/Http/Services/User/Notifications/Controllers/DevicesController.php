<?php

namespace App\Http\Services\User\Notifications\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\Notifications\Logic\NotificationInboxService;
use App\Http\Services\User\Notifications\Requests\RegisterDeviceRequest;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DevicesController extends Controller
{
    public function __construct(private NotificationInboxService $inbox)
    {
    }

    public function store(RegisterDeviceRequest $request): JsonResponse
    {
        $data = $request->validated();

        return Reply::ok(
            $this->inbox->registerDevice((int) $request->user()->id, $data['token'], $data['platform'] ?? null),
            201
        );
    }

    public function destroy(Request $request, string $token): Response
    {
        $this->inbox->unregisterDevice((int) $request->user()->id, $token);

        return response()->noContent();
    }
}
