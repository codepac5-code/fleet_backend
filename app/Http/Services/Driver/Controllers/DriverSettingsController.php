<?php

namespace App\Http\Services\Driver\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\Support\Reply;
use App\Models\DriverAppSetting;
use App\Models\DriverDeletionRequest;
use App\Models\Office;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Driver app settings + onboarding status: language/preferences, payment
 * toggles, reported OS permissions, and the account-deletion request. Settings
 * are upserted one row per driver (`driver_app_settings`).
 */
class DriverSettingsController extends Controller
{
    /** GET /driver/onboarding — account status + linked office (boot routing). */
    public function onboarding(Request $request): JsonResponse
    {
        $driver = $request->user();
        $status = $driver->status ?? ((bool) $driver->isActive ? 'active' : 'pending');

        $office = null;
        if ($driver->officeId !== null) {
            $o = Office::query()->find((int) $driver->officeId);
            if ($o !== null) {
                $office = [
                    'id' => (int) $o->id,
                    'officeName' => $o->officeName ?? null,
                    'linkStatus' => $status === 'active' ? 'linked' : 'pending',
                ];
            }
        }

        return Reply::ok([
            'accountStatus' => $status,
            'office' => $office,
        ]);
    }

    /** PATCH /driver/preferences — language + any other app preferences. */
    public function preferences(Request $request): JsonResponse
    {
        $data = $request->validate([
            'language' => ['sometimes', 'string', 'max:8'],
            'locale' => ['sometimes', 'string', 'max:8'],
        ]);

        $locale = $data['language'] ?? $data['locale'] ?? null;
        $this->settings($request)->update(array_filter(['locale' => $locale], fn ($v) => $v !== null));

        return Reply::ok(['ok' => true, 'locale' => $locale]);
    }

    /** PATCH /driver/payment-settings — payout + cash toggles. */
    /**
     * GET /driver/payment-settings — the driver's current payout preferences.
     *
     * The PATCH below already existed, but nothing could READ these values back,
     * so the app hardcoded `auto_payout: true` / `accept_cash: true` as its
     * on-screen defaults. Opening the screen and pressing Save then wrote those
     * invented defaults over whatever the driver had actually chosen.
     */
    public function paymentSettingsShow(Request $request): JsonResponse
    {
        $s = $this->settings($request);

        return Reply::ok([
            'auto_payout' => (bool) $s->auto_payout,
            'accept_cash' => (bool) $s->accept_cash,
            'payout_bank_id' => $s->payout_bank_id,
        ]);
    }

    public function paymentSettings(Request $request): JsonResponse
    {
        $data = $request->validate([
            'auto_payout' => ['sometimes', 'boolean'],
            'autoWeeklyPayout' => ['sometimes', 'boolean'],
            'accept_cash' => ['sometimes', 'boolean'],
            'acceptCash' => ['sometimes', 'boolean'],
            'payout_bank_id' => ['sometimes', 'nullable', 'string', 'max:64'],
            'payoutBankId' => ['sometimes', 'nullable', 'string', 'max:64'],
        ]);

        $patch = array_filter([
            'auto_payout' => $data['auto_payout'] ?? $data['autoWeeklyPayout'] ?? null,
            'accept_cash' => $data['accept_cash'] ?? $data['acceptCash'] ?? null,
            'payout_bank_id' => $data['payout_bank_id'] ?? $data['payoutBankId'] ?? null,
        ], fn ($v) => $v !== null);

        $this->settings($request)->update($patch);

        return Reply::ok(['ok' => true]);
    }

    /** PATCH /driver/permissions — record the last reported OS grants. */
    public function permissions(Request $request): JsonResponse
    {
        $granted = $request->all();
        $this->settings($request)->update(['permissions' => $granted]);

        return Reply::ok(['ok' => true]);
    }

    /** POST /driver/account-deletion — office-confirmed deletion request. */
    public function requestDeletion(Request $request): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $req = DriverDeletionRequest::query()->create([
            'driver_id' => (int) $request->user()->id,
            'status' => 'pending',
            'reason' => $data['reason'] ?? null,
            'created_at' => now(),
        ]);

        return Reply::ok(['id' => (int) $req->id, 'status' => $req->status], 201);
    }

    /** The driver's settings row, created on first touch. */
    private function settings(Request $request): DriverAppSetting
    {
        return DriverAppSetting::query()->firstOrCreate(
            ['driver_id' => (int) $request->user()->id],
        );
    }
}
