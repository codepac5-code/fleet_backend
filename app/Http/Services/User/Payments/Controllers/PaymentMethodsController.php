<?php

namespace App\Http\Services\User\Payments\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\Payments\Logic\PaymentMethodService;
use App\Http\Services\User\Payments\Requests\StorePaymentMethodRequest;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentMethodsController extends Controller
{
    public function __construct(private PaymentMethodService $methods)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return Reply::ok($this->methods->list((int) $request->user()->id));
    }

    public function store(StorePaymentMethodRequest $request): JsonResponse
    {
        $data = $request->validated();

        return Reply::ok(
            $this->methods->save((int) $request->user()->id, $data['stripePaymentMethodId'], ! empty($data['setDefault'])),
            201
        );
    }

    public function setDefault(Request $request, int $id): JsonResponse
    {
        return Reply::ok($this->methods->setDefault((int) $request->user()->id, $id));
    }

    public function destroy(Request $request, int $id): Response
    {
        $this->methods->remove((int) $request->user()->id, $id);

        return response()->noContent();
    }
}
