<?php

namespace App\Http\Services\User\Booking\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\Booking\Logic\BookingService;
use App\Http\Services\User\Booking\Requests\CancelBookingRequest;
use App\Http\Services\User\Booking\Requests\ChangeOfficeRequest;
use App\Http\Services\User\Booking\Requests\CreateBookingRequest;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookings)
    {
    }

    public function store(CreateBookingRequest $request): JsonResponse
    {
        $data = $request->validated();
        $key = (string) ($data['idempotency_key'] ?? $request->header('Idempotency-Key') ?? '');

        return Reply::ok($this->bookings->create((int) $request->user()->id, $data, $key), 201);
    }

    public function cancel(CancelBookingRequest $request, int $id): JsonResponse
    {
        $reason = $request->validated()['reason'] ?? null;

        return Reply::ok($this->bookings->cancel((int) $request->user()->id, $id, $reason));
    }

    public function changeOffice(ChangeOfficeRequest $request, int $id): JsonResponse
    {
        $officeId = (int) $request->validated()['office_id'];

        return Reply::ok($this->bookings->changeOffice((int) $request->user()->id, $id, $officeId));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        return Reply::ok($this->bookings->detail((int) $request->user()->id, $id));
    }
}
