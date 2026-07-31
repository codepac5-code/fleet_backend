<?php

namespace App\Http\Core\Classes\Security;

use App\Http\Core\GeoServices\ShardContext;
use App\Http\Core\GeoServices\ShardManager;
use App\Models\StaffTwoFactor;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

/**
 * Panel-staff TOTP enrolment and verification. Secrets are encrypted at rest and
 * recovery codes stored as hashes, so a database read alone never yields a
 * working second factor. Every read is fail-safe: an unprovisioned table means
 * "no 2FA", never a locked-out panel.
 */
class TwoFactorService
{
    private const RECOVERY_CODES = 8;

    public function __construct(private TotpService $totp)
    {
    }

    public function record(string $guard, int $staffId, ?string $country = null): ?StaffTwoFactor
    {
        try {
            return StaffTwoFactor::query()
                ->where('guard', $guard)
                ->where('staff_id', $staffId)
                ->where('country_code', $country ?? $this->country($guard))
                ->first();
        } catch (Throwable $e) {
            return null;
        }
    }

    public function isEnabled(string $guard, int $staffId): bool
    {
        return $this->record($guard, $staffId)?->isConfirmed() === true;
    }

    /**
     * Starts (or restarts) enrolment: a fresh unconfirmed secret plus what the
     * screen needs to show. Restarting is safe — an unconfirmed secret protects
     * nothing yet, and a confirmed one is left untouched.
     */
    public function beginEnrollment(string $guard, int $staffId, string $account): array
    {
        $existing = $this->record($guard, $staffId);

        if ($existing?->isConfirmed() === true) {
            return ['already_enrolled' => true];
        }

        $secret = $this->totp->generateSecret();

        $record = $existing ?? new StaffTwoFactor();
        $record->guard = $guard;
        $record->staff_id = $staffId;
        $record->country_code = $this->country($guard);
        $record->secret = Crypt::encryptString($secret);
        $record->recovery_codes = null;
        $record->confirmed_at = null;
        $record->save();

        return [
            'secret' => $secret,
            'formatted' => $this->totp->formatSecret($secret),
            'uri' => $this->totp->provisioningUri($secret, $account, $this->issuer()),
        ];
    }

    /** Confirms enrolment with a live code; returns the one-time recovery codes. */
    public function confirm(string $guard, int $staffId, string $code): ?array
    {
        $record = $this->record($guard, $staffId);

        if ($record === null || $record->isConfirmed()) {
            return null;
        }

        $secret = $this->secretOf($record);

        if ($secret === null || ! $this->totp->verify($secret, $code)) {
            return null;
        }

        $codes = [];

        for ($i = 0; $i < self::RECOVERY_CODES; $i++) {
            $codes[] = strtoupper(Str::random(5) . '-' . Str::random(5));
        }

        $record->recovery_codes = Crypt::encryptString(json_encode(array_map(fn ($c) => Hash::make($c), $codes)));
        $record->confirmed_at = now();
        $record->save();

        return $codes;
    }

    /**
     * Verifies a login challenge: a TOTP code, or one recovery code which is
     * consumed on use.
     */
    public function verify(string $guard, int $staffId, string $code, ?string $country = null): bool
    {
        $record = $this->record($guard, $staffId, $country);

        if ($record === null || ! $record->isConfirmed()) {
            return false;
        }

        $secret = $this->secretOf($record);

        if ($secret !== null && $this->totp->verify($secret, $code)) {
            $record->last_used_at = now();
            $record->save();

            return true;
        }

        return $this->consumeRecoveryCode($record, $code);
    }

    public function disable(string $guard, int $staffId, ?string $country = null): void
    {
        $this->record($guard, $staffId, $country)?->delete();
    }

    /** How many recovery codes are still unused, for the security screen. */
    public function remainingRecoveryCodes(StaffTwoFactor $record): int
    {
        return count($this->recoveryHashes($record));
    }

    private function consumeRecoveryCode(StaffTwoFactor $record, string $code): bool
    {
        $code = strtoupper(trim($code));
        $hashes = $this->recoveryHashes($record);

        foreach ($hashes as $index => $hash) {
            if (Hash::check($code, $hash)) {
                unset($hashes[$index]);
                $record->recovery_codes = Crypt::encryptString(json_encode(array_values($hashes)));
                $record->last_used_at = now();
                $record->save();

                return true;
            }
        }

        return false;
    }

    private function recoveryHashes(StaffTwoFactor $record): array
    {
        if ($record->recovery_codes === null) {
            return [];
        }

        try {
            $decoded = json_decode(Crypt::decryptString($record->recovery_codes), true);

            return is_array($decoded) ? $decoded : [];
        } catch (Throwable $e) {
            return [];
        }
    }

    private function secretOf(StaffTwoFactor $record): ?string
    {
        try {
            return Crypt::decryptString($record->secret);
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Admins are platform-wide (null); office/employee ids repeat per country,
     * so their enrolment is bound to the shard they signed in against.
     */
    public function country(string $guard): ?string
    {
        if ($guard === 'admin') {
            return null;
        }

        try {
            $node = ShardManager::current() ?? ShardContext::current();
            $code = $node->country_code ?? null;

            return $code !== null && $code !== '' ? strtolower((string) $code) : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    private function issuer(): string
    {
        $name = config('app.name');

        return is_string($name) && $name !== '' ? $name : 'FleetOS';
    }
}
