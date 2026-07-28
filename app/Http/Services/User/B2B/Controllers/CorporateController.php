<?php

namespace App\Http\Services\User\B2B\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\B2B\Logic\B2BService;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CorporateController extends Controller
{
    public function __construct(private B2BService $b2b)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return Reply::ok($this->b2b->corporateInvoices((int) $request->user()->id));
    }
}
