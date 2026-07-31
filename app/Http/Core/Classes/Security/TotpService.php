<?php

namespace App\Http\Core\Classes\Security;

/**
 * RFC 6238 time-based one-time passwords (SHA-1, 6 digits, 30s step) — the
 * scheme Google Authenticator, Authy and 1Password all implement. Written
 * against the RFC rather than pulled in as a dependency: it is ~60 lines of
 * HMAC and the project ships no TOTP package.
 */
class TotpService
{
    public const PERIOD = 30;
    private const DIGITS = 6;
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /** A fresh base32 secret (20 random bytes = the RFC's SHA-1 key size). */
    public function generateSecret(): string
    {
        return $this->base32Encode(random_bytes(20));
    }

    public function codeAt(string $secret, int $timestamp): string
    {
        $counter = intdiv($timestamp, self::PERIOD);
        $binary = pack('N*', 0, $counter);
        $hash = hash_hmac('sha1', $binary, $this->base32Decode($secret), true);

        $offset = ord($hash[strlen($hash) - 1]) & 0x0f;
        $value = ((ord($hash[$offset]) & 0x7f) << 24)
            | ((ord($hash[$offset + 1]) & 0xff) << 16)
            | ((ord($hash[$offset + 2]) & 0xff) << 8)
            | (ord($hash[$offset + 3]) & 0xff);

        return str_pad((string) ($value % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * `window` steps either side are accepted so a phone clock a few seconds off
     * — or a code typed as it rolls over — still verifies.
     */
    public function verify(string $secret, string $code, int $window = 1, ?int $now = null): bool
    {
        $code = preg_replace('/\D/', '', $code) ?? '';

        if (strlen($code) !== self::DIGITS || $secret === '') {
            return false;
        }

        $now ??= time();

        for ($step = -$window; $step <= $window; $step++) {
            if (hash_equals($this->codeAt($secret, $now + ($step * self::PERIOD)), $code)) {
                return true;
            }
        }

        return false;
    }

    /** The `otpauth://` URI an authenticator app imports (QR or manual entry). */
    public function provisioningUri(string $secret, string $account, string $issuer): string
    {
        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=%d&period=%d',
            rawurlencode($issuer),
            rawurlencode($account),
            $secret,
            rawurlencode($issuer),
            self::DIGITS,
            self::PERIOD
        );
    }

    /** The secret in groups of four, for keying into an app by hand. */
    public function formatSecret(string $secret): string
    {
        return trim(chunk_split($secret, 4, ' '));
    }

    private function base32Encode(string $binary): string
    {
        $bits = '';

        foreach (str_split($binary) as $char) {
            $bits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $encoded = '';

        foreach (str_split($bits, 5) as $chunk) {
            $encoded .= self::ALPHABET[bindec(str_pad($chunk, 5, '0', STR_PAD_RIGHT))];
        }

        return $encoded;
    }

    private function base32Decode(string $secret): string
    {
        $secret = strtoupper((string) preg_replace('/[^A-Za-z2-7]/', '', $secret));
        $bits = '';

        foreach (str_split($secret) as $char) {
            $index = strpos(self::ALPHABET, $char);

            if ($index !== false) {
                $bits .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
            }
        }

        $binary = '';

        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $binary .= chr(bindec($chunk));
            }
        }

        return $binary;
    }
}
