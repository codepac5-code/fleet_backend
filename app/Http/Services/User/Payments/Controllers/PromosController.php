<?php

namespace App\Http\Services\User\Payments\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\Payments\Logic\PaymentMethodService;
use App\Http\Services\User\Payments\Requests\RedeemPromoRequest;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromosController extends Controller
{
    public function __construct(private PaymentMethodService $methods)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return Reply::ok($this->methods->promoList());
    }

    public function redeem(RedeemPromoRequest $request): JsonResponse
    {
        return Reply::ok($this->methods->redeem($request->validated()['code']));
    }
}
