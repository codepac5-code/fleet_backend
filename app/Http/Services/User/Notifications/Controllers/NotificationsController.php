<?php

namespace App\Http\Services\User\Notifications\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\Notifications\Logic\NotificationInboxService;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function __construct(private NotificationInboxService $inbox)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return Reply::ok($this->inbox->list(
            (int) $request->user()->id,
            $request->boolean('unread'),
            $request->query('cursor') !== null ? (string) $request->query('cursor') : null,
            $request->query('limit')
        ));
    }

    public function read(Request $request, int $id): JsonResponse
    {
        return Reply::ok($this->inbox->read((int) $request->user()->id, $id));
    }

    public function readAll(Request $request): JsonResponse
    {
        return Reply::ok($this->inbox->readAll((int) $request->user()->id));
    }
}
