<?php

namespace Tests\Feature\Fleet;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class RiderV2WhatsappOtpTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_07_11_000007_create_rider_account_tables.php',
    ];

    protected array $tenantMigrations = [
        '2024_10_23_085910_create_users_table.php',
        '2026_07_16_000001_create_rider_refresh_tokens_table.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cache.default' => 'array',
            'services.whatsapp.base_url' => 'https://wa.test',
            'services.whatsapp.prefix' => 'whatsapp/api/v1',
            'services.whatsapp.token' => 'test-token',
            'services.whatsapp.session_id' => 'test-session',
        ]);
        Cache::flush();
    }

    public function test_otp_request_delivers_code_via_whatsapp(): void
    {
        Http::fake(['*' => Http::response(['status' => true], 200)]);

        $res = $this->postJson('user/auth/otp/request', ['dialCode' => '+974', 'phone' => '55123456'])
            ->assertStatus(200)
            ->assertJsonPath('status', true);

        $challengeId = $res->json('data.challengeId');
        $code = (string) Cache::get('rider:challenge:' . $challengeId)['code'];

        Http::assertSent(function ($request) use ($code) {
            return $request->url() === 'https://wa.test/whatsapp/api/v1/message/text/send'
                && $request->method() === 'POST'
                && $request->hasHeader('Authorization', 'Bearer test-token')
                && $request['session_id'] === 'test-session'
                && $request['receiver'] === '+97455123456'
                && str_contains((string) $request['text'], $code);
        });
    }

    public function test_no_whatsapp_call_when_unconfigured(): void
    {
        config(['services.whatsapp.token' => null, 'services.whatsapp.session_id' => null]);
        Http::fake();

        $this->postJson('user/auth/otp/request', ['dialCode' => '+974', 'phone' => '55123456'])
            ->assertStatus(200);

        Http::assertNothingSent();
    }
}
