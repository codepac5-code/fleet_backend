<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerService;
use App\Http\Core\Classes\Referral\ReferralService;
use App\Http\Core\Exceptions\DomainException;
use App\Models\Referral;
use App\Models\ReferralSetting;
use App\Models\RideBooking;
use App\Models\User;

class ReferralTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_07_28_000008_create_referrals_tables.php',
    ];

    protected array $tenantMigrations = [
        // User has no explicit connection, so in tests it lives on the default one.
        '2024_10_23_085910_create_users_table.php',
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_28_000008_create_referrals_tables.php',
    ];

    private ReferralService $referrals;
    private FleetWalletService $wallet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->wallet = new FleetWalletService(new LedgerService());
        $this->referrals = new ReferralService($this->wallet);

        ReferralSetting::query()->create([
            'is_active' => true,
            'referrer_reward_minor' => 2000,
            'invitee_reward_minor' => 1000,
            'qualifying_rides' => 1,
        ]);
    }

    private function rider(string $phone): User
    {
        return User::query()->create([
            'firstName' => 'R', 'lastName' => $phone, 'phoneNumber' => $phone,
            'dialCode' => '963', 'password' => 'secret1234', 'isActive' => 1,
        ]);
    }

    private function completeRide(int $userId, int $bookingId = null): RideBooking
    {
        return RideBooking::query()->create([
            'id' => $bookingId, 'user_id' => $userId, 'office_id' => 1, 'service' => 'ride',
            'service_class' => 'economy', 'pricing_style' => 'meter', 'status' => 'completed',
            'pickup_lat' => 33.5, 'pickup_lng' => 36.3, 'dropoff_lat' => 33.6, 'dropoff_lng' => 36.4,
            'currency_code' => 'USD',
        ]);
    }

    public function test_a_code_is_minted_once_and_stays_stable(): void
    {
        $rider = $this->rider('900100');

        $first = $this->referrals->codeFor((int) $rider->id);
        $second = $this->referrals->codeFor((int) $rider->id);

        $this->assertNotNull($first);
        $this->assertSame($first, $second);
        $this->assertSame($first, $rider->refresh()->referralCode);
    }

    public function test_redeeming_attributes_the_invitee_without_paying_yet(): void
    {
        $referrer = $this->rider('900101');
        $invitee = $this->rider('900102');
        $code = $this->referrals->codeFor((int) $referrer->id);

        $this->referrals->redeem((int) $invitee->id, $code);

        $referral = Referral::query()->where('invitee_user_id', $invitee->id)->first();
        $this->assertSame(Referral::PENDING, $referral->status);
        $this->assertSame(0, $this->wallet->walletBalanceMinor('user', (int) $referrer->id, 'USD'), 'nothing is paid before a ride happens');
    }

    public function test_a_completed_ride_pays_both_sides_once(): void
    {
        $referrer = $this->rider('900103');
        $invitee = $this->rider('900104');
        $this->referrals->redeem((int) $invitee->id, $this->referrals->codeFor((int) $referrer->id));

        $ride = $this->completeRide((int) $invitee->id);
        $this->referrals->qualify((int) $invitee->id, (int) $ride->id);

        $this->assertSame(2000, $this->wallet->walletBalanceMinor('user', (int) $referrer->id, 'USD'));
        $this->assertSame(1000, $this->wallet->walletBalanceMinor('user', (int) $invitee->id, 'USD'));
        $this->assertSame(Referral::REWARDED, Referral::query()->where('invitee_user_id', $invitee->id)->value('status'));

        // A second completed ride must not pay again.
        $this->referrals->qualify((int) $invitee->id, (int) $this->completeRide((int) $invitee->id)->id);
        $this->assertSame(2000, $this->wallet->walletBalanceMinor('user', (int) $referrer->id, 'USD'));
    }

    public function test_the_reward_comes_out_of_fleet_revenue(): void
    {
        $referrer = $this->rider('900105');
        $invitee = $this->rider('900106');
        $this->referrals->redeem((int) $invitee->id, $this->referrals->codeFor((int) $referrer->id));

        $this->referrals->qualify((int) $invitee->id, (int) $this->completeRide((int) $invitee->id)->id);

        $this->assertSame(-3000, $this->wallet->revenueBalanceMinor('fleet', 0, 'USD'), 'a referral is marketing spend, not money from nowhere');
    }

    public function test_a_rider_cannot_refer_themselves(): void
    {
        $rider = $this->rider('900107');

        $this->expectException(DomainException::class);
        $this->referrals->redeem((int) $rider->id, $this->referrals->codeFor((int) $rider->id));
    }

    public function test_a_code_cannot_be_entered_after_the_first_ride(): void
    {
        $referrer = $this->rider('900108');
        $invitee = $this->rider('900109');
        $this->completeRide((int) $invitee->id);

        $this->expectException(DomainException::class);
        $this->referrals->redeem((int) $invitee->id, $this->referrals->codeFor((int) $referrer->id));
    }

    public function test_a_rider_can_only_use_one_code_ever(): void
    {
        $first = $this->rider('900110');
        $second = $this->rider('900111');
        $invitee = $this->rider('900112');

        $this->referrals->redeem((int) $invitee->id, $this->referrals->codeFor((int) $first->id));

        $this->expectException(DomainException::class);
        $this->referrals->redeem((int) $invitee->id, $this->referrals->codeFor((int) $second->id));
    }

    public function test_an_inactive_programme_pays_nothing(): void
    {
        $referrer = $this->rider('900113');
        $invitee = $this->rider('900114');
        $this->referrals->redeem((int) $invitee->id, $this->referrals->codeFor((int) $referrer->id));

        ReferralSetting::query()->update(['is_active' => false]);

        $this->referrals->qualify((int) $invitee->id, (int) $this->completeRide((int) $invitee->id)->id);

        $this->assertSame(0, $this->wallet->walletBalanceMinor('user', (int) $referrer->id, 'USD'));
        $this->assertSame(Referral::PENDING, Referral::query()->where('invitee_user_id', $invitee->id)->value('status'));
    }

    public function test_two_qualifying_rides_can_be_required(): void
    {
        ReferralSetting::query()->update(['qualifying_rides' => 2]);

        $referrer = $this->rider('900115');
        $invitee = $this->rider('900116');
        $this->referrals->redeem((int) $invitee->id, $this->referrals->codeFor((int) $referrer->id));

        $this->referrals->qualify((int) $invitee->id, (int) $this->completeRide((int) $invitee->id)->id);
        $this->assertSame(0, $this->wallet->walletBalanceMinor('user', (int) $referrer->id, 'USD'), 'one ride is not enough');

        $this->referrals->qualify((int) $invitee->id, (int) $this->completeRide((int) $invitee->id)->id);
        $this->assertSame(2000, $this->wallet->walletBalanceMinor('user', (int) $referrer->id, 'USD'));
    }
}
