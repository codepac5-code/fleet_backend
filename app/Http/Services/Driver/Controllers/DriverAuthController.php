<?php

namespace App\Http\Services\Driver\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Auth\DriverAuthService;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Driver auth (OTP → token). Mirrors the rider flow; issues a Passport
 * `driverx` token for active drivers.
 */
class DriverAuthController extends Controller
{
    public function __construct(private DriverAuthService $auth)
    {
    }

    public function requestOtp(Request $request): JsonResponse
    {
        // The driver app sends a single full `phone` (E.164); DriverAuthService
        // normalizes it. `dialCode` is accepted and prepended if sent separately.
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:24'],
            'dialCode' => ['nullable', 'string', 'max:8'],
            'country' => ['nullable', 'string', 'max:2'],
        ]);

        $this->routeToSelectedCountry($request);

        return Reply::ok($this->auth->requestOtp(($data['dialCode'] ?? '') . $data['phone']));
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:24'],
            'dialCode' => ['nullable', 'string', 'max:8'],
            'country' => ['nullable', 'string', 'max:2'],
            'code' => ['required', 'string', 'max:8'],
        ]);

        // Point the connection at the driver's own country database BEFORE the
        // lookup, so the account is checked in the shard the driver selected.
        $this->routeToSelectedCountry($request);

        return Reply::ok($this->auth->verify(($data['dialCode'] ?? '') . $data['phone'], $data['code']));
    }

    /**
     * Route the query to the database of the country the driver selected in the
     * login country picker. The `country` field (ISO-2) is authoritative; the
     * `X-Country` header is the fallback (the `tenant-shard` middleware already
     * resolves it, but activating explicitly here keeps the intent in the auth
     * code and fails loudly when the selected country has no provisioned shard).
     */
    private function routeToSelectedCountry(Request $request): void
    {
        $iso2 = $request->input('country') ?: $request->header('X-Country');

        if (! $iso2) {
            return; // middleware default / single-shard deployments
        }

        $node = ShardManager::byCountryCode($iso2);

        if ($node === null) {
            throw DomainException::make('country_not_supported', 422);
        }

        ShardManager::activate($node);
    }

    public function me(Request $request): JsonResponse
    {
        return Reply::ok($this->auth->present($request->user()));
    }

    public function logout(Request $request): Response
    {
        $this->auth->logout($request->user());

        return response()->noContent();
    }
}
