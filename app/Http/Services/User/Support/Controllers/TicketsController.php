<?php

namespace App\Http\Services\User\Support\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\Support\Logic\SupportService;
use App\Http\Services\User\Support\Reply;
use App\Http\Services\User\Support\Requests\OpenTicketRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketsController extends Controller
{
    public function __construct(private SupportService $support)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return Reply::ok($this->support->listTickets((int) $request->user()->id));
    }

    public function store(OpenTicketRequest $request): JsonResponse
    {
        $data = $request->validated();

        return Reply::ok(
            $this->support->openTicket((int) $request->user()->id, $data['topic'], isset($data['tripId']) ? (int) $data['tripId'] : null, $data['message']),
            201
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        return Reply::ok($this->support->showTicket((int) $request->user()->id, $id));
    }

    public function reply(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        return Reply::ok($this->support->replyToTicket((int) $request->user()->id, $id, $data['message']), 201);
    }
}
