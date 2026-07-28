<?php

namespace App\Http\Services\User\Scheduled\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\Scheduled\Logic\ScheduledRideService;
use App\Http\Services\User\Scheduled\Requests\CreateScheduledRequest;
use App\Http\Services\User\Scheduled\Requests\UpdateScheduledRequest;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScheduledController extends Controller
{
    public function __construct(private ScheduledRideService $scheduled)
    {
    }

    public function store(CreateScheduledRequest $request): JsonResponse
    {
        return Reply::ok($this->scheduled->create((int) $request->user()->id, $request->validated()), 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        return Reply::ok($this->scheduled->show((int) $request->user()->id, $id));
    }

    public function update(UpdateScheduledRequest $request, int $id): JsonResponse
    {
        return Reply::ok($this->scheduled->update((int) $request->user()->id, $id, $request->validated()));
    }

    public function destroy(Request $request, int $id): Response
    {
        $this->scheduled->cancel((int) $request->user()->id, $id);

        return response()->noContent();
    }
}
