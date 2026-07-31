<?php

namespace App\Http\Services\User\Account\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Referral\ReferralService;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferralsController extends Controller
{
    public function __construct(private ReferralService $referrals)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return Reply::ok($this->referrals->summary((int) $request->user()->id));
    }

    public function redeem(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:32'],
        ]);

        return Reply::ok($this->referrals->redeem((int) $request->user()->id, $data['code']));
    }
}
