<?php

namespace App\Http\Services\User\Support\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Settings\AppSettings;
use App\Http\Services\User\Support\Logic\SupportService;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HelpController extends Controller
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

    /** Fleet support contact info exposed to the app (configurable in the panel). */
    public function contact(): JsonResponse
    {
        return Reply::ok([
            'support_phone' => AppSettings::string('support_phone', ''),
        ]);
    }
}
