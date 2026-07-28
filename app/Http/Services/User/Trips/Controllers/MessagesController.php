<?php

namespace App\Http\Services\User\Trips\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\Support\Reply;
use App\Http\Services\User\Trips\Logic\TripService;
use App\Http\Services\User\Trips\Requests\SendMessageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    public function __construct(private TripService $trips)
    {
    }

    public function index(Request $request, int $id): JsonResponse
    {
        return Reply::ok($this->trips->messages(
            (int) $request->user()->id,
            $id,
            $request->query('cursor') !== null ? (string) $request->query('cursor') : null,
            $request->query('limit')
        ));
    }

    public function store(SendMessageRequest $request, int $id): JsonResponse
    {
        return Reply::ok(
            $this->trips->sendMessage((int) $request->user()->id, $id, $request->validated()['body']),
            201
        );
    }
}
