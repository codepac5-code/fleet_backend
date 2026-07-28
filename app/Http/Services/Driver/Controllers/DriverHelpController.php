<?php

namespace App\Http\Services\Driver\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\Support\Logic\SupportService;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Driver help centre (`GET /driver/help/articles`). Help articles are shared
 * content, so this reuses the (owner-agnostic) SupportService help methods.
 */
class DriverHelpController extends Controller
{
    public function __construct(private SupportService $support)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return Reply::ok($this->support->helpList(
            $request->query('category') !== null ? (string) $request->query('category') : null
        ));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        return Reply::ok($this->support->helpShow($id));
    }
}
