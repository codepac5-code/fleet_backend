<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Security\TotpService;
use App\Http\Core\Classes\Security\TwoFactorService;
use App\Http\Services\Panel\Auth\Logic\TwoFactorChallenge;
use App\Models\StaffTwoFactor;

class TwoFactorTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_07_28_000004_create_staff_two_factor_table.php',
    ];

    private function service(): TwoFactorService
    {
        return new TwoFactorService(new TotpService());
    }

    // RFC 6238 appendix B: key "12345678901234567890" (base32 below), SHA-1,
    // T=59 → 94287082; the 6-digit truncation is the last six of that.
    public function test_totp_matches_the_rfc_test_vector(): void
    {
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

        $this->assertSame('287082', (new TotpService())->codeAt($secret, 59));
        $this->assertSame('081804', (new TotpService())->codeAt($secret, 1111111109));
    }

    public function test_verify_accepts_the_neighbouring_step_but_not_a_distant_one(): void
    {
        $totp = new TotpService();
        $secret = $totp->generateSecret();
        $now = 1_700_000_000;

        $this->assertTrue($totp->verify($secret, $totp->codeAt($secret, $now), 1, $now));
        $this->assertTrue($totp->verify($secret, $totp->codeAt($secret, $now - 30), 1, $now));
        $this->assertFalse($totp->verify($secret, $totp->codeAt($secret, $now - 300), 1, $now));
    }

    public function test_verify_rejects_malformed_input(): void
    {
        $totp = new TotpService();
        $secret = $totp->generateSecret();

        $this->assertFalse($totp->verify($secret, ''));
        $this->assertFalse($totp->verify($secret, '12345'));
        $this->assertFalse($totp->verify($secret, 'abcdef'));
    }

    public function test_generated_secret_round_trips_through_base32(): void
    {
        $totp = new TotpService();
        $secret = $totp->generateSecret();

        $this->assertSame(32, strlen($secret));
        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $secret);
        $this->assertStringContainsString('secret=' . $secret, $totp->provisioningUri($secret, 'ops@fleet', 'FleetOS'));
    }

    public function test_enrolment_is_not_active_until_confirmed(): void
    {
        $service = $this->service();
        $setup = $service->beginEnrollment('admin', 1, 'ops@fleet');

        $this->assertFalse($service->isEnabled('admin', 1), 'an unconfirmed secret protects nothing');

        $code = (new TotpService())->codeAt($setup['secret'], time());
        $codes = $service->confirm('admin', 1, $code);

        $this->assertCount(8, $codes);
        $this->assertTrue($service->isEnabled('admin', 1));
    }

    public function test_confirm_rejects_a_wrong_code(): void
    {
        $service = $this->service();
        $service->beginEnrollment('admin', 1, 'ops@fleet');

        $this->assertNull($service->confirm('admin', 1, '000000'));
        $this->assertFalse($service->isEnabled('admin', 1));
    }

    public function test_secret_is_encrypted_at_rest(): void
    {
        $service = $this->service();
        $setup = $service->beginEnrollment('admin', 1, 'ops@fleet');

        $stored = StaffTwoFactor::query()->where('staff_id', 1)->value('secret');

        $this->assertNotSame($setup['secret'], $stored);
        $this->assertStringNotContainsString($setup['secret'], (string) $stored);
    }

    public function test_recovery_code_works_once(): void
    {
        $service = $this->service();
        $setup = $service->beginEnrollment('admin', 1, 'ops@fleet');
        $codes = $service->confirm('admin', 1, (new TotpService())->codeAt($setup['secret'], time()));

        $this->assertTrue($service->verify('admin', 1, $codes[0]));
        $this->assertFalse($service->verify('admin', 1, $codes[0]), 'a recovery code is consumed on use');
        $this->assertTrue($service->verify('admin', 1, $codes[1]));

        $record = StaffTwoFactor::query()->where('staff_id', 1)->first();
        $this->assertSame(6, $service->remainingRecoveryCodes($record));
    }

    public function test_two_offices_sharing_an_id_across_countries_stay_separate(): void
    {
        $service = $this->service();

        $sy = $service->beginEnrollment('office', 3, 'office3@sy');
        $service->confirm('office', 3, (new TotpService())->codeAt($sy['secret'], time()));

        // Same office id, different country — a distinct enrolment row, and the
        // SY code must not open the QA account. (The shard is stamped by hand:
        // the sqlite harness has no live shard for country() to resolve.)
        StaffTwoFactor::query()->where('staff_id', 3)->update(['country_code' => 'sy']);

        $qa = $service->beginEnrollment('office', 3, 'office3@qa');
        $service->confirm('office', 3, (new TotpService())->codeAt($qa['secret'], time()));
        StaffTwoFactor::query()->whereNull('country_code')->update(['country_code' => 'qa']);

        $this->assertSame(2, StaffTwoFactor::query()->where('staff_id', 3)->count());
        $this->assertTrue($service->verify('office', 3, (new TotpService())->codeAt($sy['secret'], time()), 'sy'));
        $this->assertTrue($service->verify('office', 3, (new TotpService())->codeAt($qa['secret'], time()), 'qa'));
        $this->assertFalse($service->verify('office', 3, (new TotpService())->codeAt($sy['secret'], time()), 'qa'));
    }

    public function test_disable_removes_the_enrolment(): void
    {
        $service = $this->service();
        $setup = $service->beginEnrollment('admin', 1, 'ops@fleet');
        $service->confirm('admin', 1, (new TotpService())->codeAt($setup['secret'], time()));

        $service->disable('admin', 1);

        $this->assertFalse($service->isEnabled('admin', 1));
        $this->assertNull(StaffTwoFactor::query()->where('staff_id', 1)->first());
    }

    public function test_challenge_without_a_pending_login_completes_nothing(): void
    {
        $challenge = new TwoFactorChallenge($this->service());

        $this->assertNull($challenge->pending());
        $this->assertNull($challenge->complete('123456'));
    }

    public function test_challenge_rejects_a_wrong_code_without_logging_anyone_in(): void
    {
        $service = $this->service();
        $setup = $service->beginEnrollment('admin', 1, 'ops@fleet');
        $service->confirm('admin', 1, (new TotpService())->codeAt($setup['secret'], time()));

        session(['panel_two_factor_pending' => ['guard' => 'admin', 'id' => 1, 'country' => null, 'remember' => false]]);

        $challenge = new TwoFactorChallenge($service);

        $this->assertNull($challenge->complete('000000'));
        $this->assertNotNull($challenge->pending(), 'a failed attempt keeps the challenge open');
        $this->assertFalse(auth('admin')->check());
    }

    public function test_missing_table_reads_as_no_two_factor(): void
    {
        $this->app['db']->connection('global')->statement('DROP TABLE staff_two_factor');

        $this->assertFalse($this->service()->isEnabled('admin', 1), 'an unprovisioned table never locks the panel');
    }
}
