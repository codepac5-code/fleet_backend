<?php

namespace App\Http\Services\Driver\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Dispatch\DispatchService;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dispatch offers. Accept is atomic (first driver wins); on success the backend
 * assigns the ride and emits `dispatch.ride_assigned` + `booking.status_changed`.
 */
class DriverOffersController extends Controller
{
    public function __construct(private DispatchService $dispatch)
    {
    }

    public function accept(Request $request, int $id): JsonResponse
    {
        $ok = $this->dispatch->accept($id, (int) $request->user()->id);

        if (! $ok) {
            throw DomainException::make('offer_unavailable', 409);
        }

        return Reply::ok(['accepted' => true, 'bookingId' => $id]);
    }

    public function reject(Request $request, int $id): Response
    {
        $this->dispatch->reject($id, (int) $request->user()->id);

        return response()->noContent();
    }
}
