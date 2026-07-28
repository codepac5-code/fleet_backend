<?php

namespace App\Http\Services\User\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Auth\TokenIssuer;
use App\Http\Services\User\Auth\Logic\ChallengeOtpLogic;
use App\Http\Services\User\Auth\Logic\PhoneChangeService;
use App\Http\Services\User\Auth\Logic\RefreshTokenService;
use App\Http\Services\User\Auth\Logic\SocialAuthLogic;
use App\Http\Services\User\Auth\Requests\PhoneChangeRequest;
use App\Http\Services\User\Auth\Requests\PhoneChangeVerifyRequest;
use App\Http\Services\User\Auth\Requests\RefreshRequest;
use App\Http\Services\User\Auth\Requests\RegisterRequest;
use App\Http\Services\User\Auth\Requests\RequestOtpRequest;
use App\Http\Services\User\Auth\Requests\SocialLoginRequest;
use App\Http\Services\User\Auth\Requests\VerifyOtpRequest;
use App\Http\Services\User\Support\Presenters\UserPresenter;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(
        private ChallengeOtpLogic $otp,
        private RefreshTokenService $refresh,
        private TokenIssuer $tokens,
        private UserPresenter $presenter,
        private PhoneChangeService $phoneChange,
        private SocialAuthLogic $social
    ) {
    }

    public function requestOtp(RequestOtpRequest $request): JsonResponse
    {
        $data = $request->validated();

        return Reply::ok($this->otp->request($data['dialCode'], $data['phone']));
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $data = $request->validated();

        return Reply::ok($this->otp->verify($data['challengeId'], $data['code']));
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        return Reply::ok(
            $this->otp->register($data['challengeId'], $data['firstName'], $data['lastName'], $data['email'] ?? null, $data['country'] ?? null),
            201
        );
    }

    public function refresh(RefreshRequest $request): JsonResponse
    {
        return Reply::ok($this->otp->refresh($request->validated()['refreshToken']));
    }

    public function social(SocialLoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        return Reply::ok($this->social->login($data['provider'], $data['token']));
    }

    public function changePhone(PhoneChangeRequest $request): JsonResponse
    {
        $data = $request->validated();

        return Reply::ok($this->phoneChange->request((int) $request->user()->id, $data['dialCode'], $data['phone']));
    }

    public function verifyPhoneChange(PhoneChangeVerifyRequest $request): JsonResponse
    {
        $data = $request->validated();

        return Reply::ok($this->phoneChange->confirm((int) $request->user()->id, $data['challengeId'], $data['code']));
    }

    public function me(Request $request): JsonResponse
    {
        return Reply::ok($this->presenter->present($request->user()));
    }

    public function logout(Request $request): Response
    {
        $user = $request->user();
        $this->refresh->revokeAllForUser((int) $user->id);
        $this->tokens->revokeCurrent($user);

        return response()->noContent();
    }
}
