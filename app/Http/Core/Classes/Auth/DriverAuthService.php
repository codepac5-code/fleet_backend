<?php

namespace App\Http\Core\Classes\Auth;

use App\Http\Core\Classes\Notification\WhatsappSender;
use App\Http\Core\Classes\Ledger\DriverCurrency;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Rating\RatingService;
use App\Http\Core\Const\Ledger\OwnerType;
use App\Http\Core\Classes\Settings\AppSettings;
use App\Http\Core\Exceptions\DomainException;
use App\Models\Driver;
use App\Models\DriverApplication;
use Illuminate\Support\Facades\Cache;

class DriverAuthService
{
    private const EXPIRES = 120;
    private const RESEND = 60;
    private const MAX_ATTEMPTS = 5;

    /// Average stars actually awarded to this driver. Falls back to the stored
    /// column only when nobody has rated them yet, so a brand-new driver does
    /// not read as 0 stars out of 5.
    private static function realRating(int $driverId, float $stored): float
    {
        try {
            $summary = app(RatingService::class)->summaryFor('driver', $driverId);

            return ($summary['count'] ?? 0) > 0 ? (float) $summary['average'] : $stored;
        } catch (\Throwable) {
            return $stored;
        }
    }

    public function __construct(private DriverTokenIssuer $tokens)
    {
    }

    public function requestOtp(string $rawPhone): array
    {
        $phone = PhoneNumber::normalize($rawPhone);

        if ($phone === null) {
            throw DomainException::make('invalid_phone', 422);
        }

        if (Cache::has($this->sentKey($phone))) {
            throw DomainException::make('otp_throttled', 429);
        }

        $code = (string) random_int(1000, 9999);
        $ttl = AppSettings::int('otp_ttl_seconds', self::EXPIRES);

        Cache::put($this->codeKey($phone), $code, now()->addSeconds($ttl));
        Cache::put($this->sentKey($phone), true, now()->addSeconds(self::RESEND));
        Cache::forget($this->attemptsKey($phone));

        // Deliver the code over WhatsApp (Whatsapp Personal API). Best-effort:
        // the code is already cached, so a transient delivery failure is logged
        // (see WhatsappSender) but does not fail the request.
        $minutes = (int) ceil($ttl / 60);
        app(WhatsappSender::class)->send($phone, $this->otpMessage($code, $minutes));

        // Dev convenience: no SMS gateway locally, so surface the code in the log
        // (and the response when debugging) instead of leaving it only in cache.
        $devReveal = app()->environment('local') || config('app.debug');
        if ($devReveal) {
            \Illuminate\Support\Facades\Log::info("[DriverOTP] {$phone} => {$code}");
        }

        return array_filter([
            'otp_sent' => true,
            'expires_in' => $ttl,
            'resend_in' => self::RESEND,
            'dev_code' => $devReveal ? $code : null,
        ], fn ($v) => $v !== null);
    }

    public function verify(string $rawPhone, string $code): array
    {
        $phone = PhoneNumber::normalize($rawPhone);

        if ($phone === null) {
            throw DomainException::make('invalid_phone', 422);
        }

        $stored = Cache::get($this->codeKey($phone));

        if ($stored === null) {
            throw DomainException::make('code_expired', 410);
        }

        if ((int) Cache::get($this->attemptsKey($phone), 0) >= self::MAX_ATTEMPTS) {
            Cache::forget($this->codeKey($phone));
            throw DomainException::make('code_expired', 410);
        }

        if (! hash_equals((string) $stored, trim($code))) {
            Cache::increment($this->attemptsKey($phone));
            throw DomainException::make('invalid_code', 422);
        }

        Cache::forget($this->codeKey($phone));
        Cache::forget($this->sentKey($phone));
        Cache::forget($this->attemptsKey($phone));

        [, $national] = PhoneNumber::split($phone);
        $driver = Driver::query()->where('phoneNumber', $national)->first();

        if ($driver === null) {
            return ['is_registered' => false, 'status' => 'not_registered'];
        }

        if (! (bool) $driver->isActive) {
            return ['is_registered' => true, 'status' => 'pending', 'driver' => $this->present($driver)];
        }

        return [
            'is_registered' => true,
            'status' => 'active',
            'access_token' => $this->tokens->issue($driver, 'driverx'),
            'token_type' => 'Bearer',
            'driver' => $this->present($driver),
        ];
    }

    public function apply(string $rawPhone, array $in): array
    {
        $phone = PhoneNumber::normalize($rawPhone);

        if ($phone === null) {
            throw DomainException::make('invalid_phone', 422);
        }

        $application = DriverApplication::query()->create([
            'phone' => $phone,
            'name' => $in['name'] ?? null,
            'first_name' => $in['first_name'] ?? null,
            'last_name' => $in['last_name'] ?? null,
            'gender' => $in['gender'] ?? null,
            'country' => $in['country'] ?? null,
            'city' => $in['city'] ?? null,
            'region' => $in['region'] ?? null,
            'address' => $in['address'] ?? null,
            'car_owner' => (bool) ($in['car_owner'] ?? false),
            'vehicle_type' => $in['vehicle_type'] ?? null,
            'license_number' => $in['license_number'] ?? null,
            'license_path' => $this->storeLicense($in['license_file'] ?? null, $in['license_ext'] ?? null),
            'office_id' => isset($in['office_id']) ? (int) $in['office_id'] : null,
            'invite_code' => $in['invite_code'] ?? null,
            'kind' => ($in['office_id'] ?? $in['invite_code'] ?? null) !== null ? 'link' : 'apply',
            'status' => 'pending',
        ]);

        return ['application_id' => (int) $application->id, 'status' => 'pending'];
    }

    public function logout(Driver $driver): void
    {
        $this->tokens->revokeCurrent($driver);
    }

    public function present(Driver $driver): array
    {
        $office = $driver->officeId !== null ? $driver->office : null;
        $vehicle = $driver->vehicleId !== null ? $driver->vehicle : null;
        $status = $driver->status ?? ((bool) $driver->isActive ? 'active' : 'pending');

        return [
            'id' => (int) $driver->id,
            // Legacy fields (kept for existing consumers).
            'name' => trim(((string) $driver->firstName) . ' ' . ((string) $driver->lastName)),
            'phone_masked' => PhoneNumber::mask('+' . ltrim((string) $driver->dialCode, '+') . (string) $driver->phoneNumber),
            'office_id' => $driver->officeId !== null ? (int) $driver->officeId : null,
            'is_active' => (bool) $driver->isActive,
            // The REAL average of this driver's ride_ratings, not the
            // `drivers.rating` column. That column is written once by the
            // seeder (`rand(0, 40) / 10`, so 0.0-4.0) and never recomputed when
            // a rider rates a trip — which is why drivers showed values like
            // "0.6 rating" that no rider ever gave them.
            'rating' => self::realRating((int) $driver->id, (float) ($driver->rating ?? 0)),
            // Rich profile fields consumed by the driver app's `Driver.fromJson`.
            'firstName' => $driver->firstName,
            'lastName' => $driver->lastName,
            'userName' => $driver->userName,
            'phoneNumber' => $driver->phoneNumber,
            'dialCode' => $driver->dialCode,
            'status' => $status,
            'isActive' => (bool) $driver->isActive,
            // The LEDGER balance, not the legacy `drivers.walletBalance` column.
            //
            // That column is denormalised seed data that nothing maintains: on
            // the SY shard driver 33 carried 165202 in it while having zero
            // ledger accounts, zero commission snapshots and zero completed
            // trips — so `driver/me` advertised a six-figure balance for a
            // driver whose withdrawable balance is genuinely 0, and disagreed
            // with `driver/home` and `driver/wallet` (both ledger-backed) on the
            // very same screen refresh. Payouts settle against the ledger, so
            // the ledger is the only number safe to show.
            // Resolved through the container like RatingService above, so this
            // service keeps its single constructor dependency. Fail-soft: a
            // ledger hiccup shows 0, never the stale column.
            'walletBalance' => (function () use ($driver) {
                try {
                    return app(FleetWalletService::class)->walletBalanceMinor(
                        OwnerType::DRIVER,
                        (int) $driver->id,
                        DriverCurrency::resolve($driver, null)
                    );
                } catch (\Throwable) {
                    return 0;
                }
            })(),
            'office' => $office !== null ? [
                'id' => (int) $office->id,
                'officeName' => $office->officeName ?? null,
                'contactNumber' => $office->contactNumber ?? ($office->phone ?? null),
            ] : null,
            'vehicle' => $vehicle !== null ? [
                'id' => (int) $vehicle->id,
                'model' => $vehicle->model ?? null,
                'plate' => $vehicle->plate ?? null,
                'color' => $vehicle->color ?? null,
            ] : null,
        ];
    }

    /**
     * Decode an optional base64 license document and store it on the public
     * disk, returning its public URL (or null when no file was supplied).
     */
    private function storeLicense(?string $base64, ?string $ext): ?string
    {
        if (empty($base64)) {
            return null;
        }

        $raw = $base64;
        $comma = strpos($raw, ',');
        if ($comma !== false && str_starts_with($raw, 'data:')) {
            $raw = substr($raw, $comma + 1);
        }

        $binary = base64_decode($raw, true);
        if ($binary === false) {
            throw DomainException::make('invalid_license_file', 422);
        }

        $ext = preg_replace('/[^a-z0-9]/i', '', (string) ($ext ?? 'jpg')) ?: 'jpg';
        $path = 'driver_applications/licenses/' . \Illuminate\Support\Str::uuid() . ".{$ext}";
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $binary);

        return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
    }

    private function otpMessage(string $code, int $minutes): string
    {
        return "رمز الدخول إلى تطبيق السائق: {$code}\n"
            . "ينتهي خلال {$minutes} دقيقة. لا تشاركه مع أحد.\n\n"
            . "Your driver app verification code: {$code} (valid {$minutes} min).";
    }

    private function codeKey(string $phone): string
    {
        return 'driver:otp:code:' . $phone;
    }

    private function sentKey(string $phone): string
    {
        return 'driver:otp:sent:' . $phone;
    }

    private function attemptsKey(string $phone): string
    {
        return 'driver:otp:attempts:' . $phone;
    }
}
