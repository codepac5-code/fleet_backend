<?php

namespace App\Http\Core\Classes\Referral;

use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Const\Ledger\AccountType;
use App\Http\Core\Const\Ledger\Direction;
use App\Http\Core\Const\Ledger\OwnerType;
use App\Http\Core\Const\Ride\BookingStatus;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Core\GeoServices\ShardManager;
use App\Models\Referral;
use App\Models\ReferralSetting;
use App\Models\RideBooking;
use App\Models\User;
use Illuminate\Support\Str;
use Throwable;

/**
 * Rider referrals: a code per rider, an attribution when an invitee redeems it,
 * and a ledger-funded reward once the invitee actually rides.
 *
 * The reward is deliberately paid on a COMPLETED ride, not on signup — a code
 * alone costs the fleet nothing to fake. Both sides are credited from fleet
 * revenue in the country the qualifying ride happened in, because that is where
 * the money and the currency live.
 */
class ReferralService
{
    public function __construct(private FleetWalletService $wallet)
    {
    }

    /** The rider's own code, minted on first use and stable afterwards. */
    public function codeFor(int $userId): ?string
    {
        $user = User::query()->find($userId);

        if ($user === null) {
            return null;
        }

        if (! empty($user->referralCode)) {
            return $user->referralCode;
        }

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $code = strtoupper(Str::random(6));

            if (! User::query()->where('referralCode', $code)->exists()) {
                $user->referralCode = $code;
                $user->save();

                return $code;
            }
        }

        return null;
    }

    /**
     * Attributes a rider to whoever's code they entered. Only before their first
     * ride — a code is an invitation, not a retroactive claim.
     */
    public function redeem(int $inviteeUserId, string $code): array
    {
        $settings = ReferralSetting::current();

        if (! $settings->is_active) {
            throw DomainException::make('referrals_unavailable', 409, 'The referral programme is not running here.');
        }

        $code = strtoupper(trim($code));
        $referrer = User::query()->where('referralCode', $code)->first();

        if ($referrer === null) {
            throw DomainException::make('referral_code_invalid', 404, 'That code does not exist.');
        }

        if ((int) $referrer->id === $inviteeUserId) {
            throw DomainException::make('referral_self', 422, 'You cannot refer yourself.');
        }

        if (Referral::query()->where('invitee_user_id', $inviteeUserId)->exists()) {
            throw DomainException::make('referral_already_used', 409, 'This account already used a referral code.');
        }

        if ($this->completedRides($inviteeUserId) > 0) {
            throw DomainException::make('referral_too_late', 409, 'A code can only be entered before your first ride.');
        }

        $referral = Referral::query()->create([
            'referrer_user_id' => (int) $referrer->id,
            'invitee_user_id' => $inviteeUserId,
            'code' => $code,
            'status' => Referral::PENDING,
        ]);

        return [
            'status' => $referral->status,
            'reward' => [
                'invitee_minor' => $settings->invitee_reward_minor,
                'referrer_minor' => $settings->referrer_reward_minor,
                'currency' => ShardManager::currency(),
                'qualifying_rides' => $settings->qualifying_rides,
            ],
        ];
    }

    /**
     * Called when a ride completes. Pays both sides once the invitee has done
     * the required number of rides. Best-effort by design: a referral reward
     * must never be able to fail a completed ride.
     */
    public function qualify(int $inviteeUserId, int $bookingId): ?Referral
    {
        try {
            $referral = Referral::query()
                ->where('invitee_user_id', $inviteeUserId)
                ->where('status', Referral::PENDING)
                ->first();

            if ($referral === null) {
                return null;
            }

            $settings = ReferralSetting::current();

            if (! $settings->is_active) {
                return null;
            }

            if ($this->completedRides($inviteeUserId) < max(1, $settings->qualifying_rides)) {
                return null;
            }

            $currency = ShardManager::currency();

            $this->credit((int) $referral->referrer_user_id, $settings->referrer_reward_minor, $currency, 'referrer', (int) $referral->id);
            $this->credit($inviteeUserId, $settings->invitee_reward_minor, $currency, 'invitee', (int) $referral->id);

            $referral->status = Referral::REWARDED;
            $referral->qualifying_booking_id = $bookingId;
            $referral->referrer_reward_minor = $settings->referrer_reward_minor;
            $referral->invitee_reward_minor = $settings->invitee_reward_minor;
            $referral->currency_code = $currency;
            $referral->country_code = Referral::activeCountryCode();
            $referral->rewarded_at = now();
            $referral->save();

            return $referral;
        } catch (Throwable $e) {
            return null;
        }
    }

    /** What the rider sees on their referral screen. */
    public function summary(int $userId): array
    {
        $settings = ReferralSetting::current();

        $referrals = Referral::query()->where('referrer_user_id', $userId)->get(['status', 'referrer_reward_minor']);

        return [
            'code' => $this->codeFor($userId),
            'active' => (bool) $settings->is_active,
            'invited' => $referrals->count(),
            'rewarded' => $referrals->where('status', Referral::REWARDED)->count(),
            'earnedMinor' => (int) $referrals->sum('referrer_reward_minor'),
            'currency' => ShardManager::currency(),
            'reward' => [
                'referrer_minor' => $settings->referrer_reward_minor,
                'invitee_minor' => $settings->invitee_reward_minor,
                'qualifying_rides' => $settings->qualifying_rides,
            ],
            // Null while the rider has not been referred by anyone.
            'redeemed' => Referral::query()->where('invitee_user_id', $userId)->value('status'),
        ];
    }

    private function credit(int $userId, int $amountMinor, string $currency, string $side, int $referralId): void
    {
        if ($amountMinor <= 0) {
            return;
        }

        // Funded from fleet revenue — a referral is marketing spend, so it has to
        // come out of the fleet's own money, not appear from nowhere.
        $this->wallet->adjustment(
            [
                ['owner_type' => OwnerType::FLEET, 'owner_id' => OwnerType::FLEET_OWNER_ID, 'account_type' => AccountType::REVENUE, 'direction' => Direction::DEBIT, 'amount_minor' => $amountMinor],
                ['owner_type' => OwnerType::USER, 'owner_id' => $userId, 'account_type' => AccountType::WALLET, 'direction' => Direction::CREDIT, 'amount_minor' => $amountMinor],
            ],
            $currency,
            'referral:' . $referralId . ':' . $side,
            'referral reward (' . $side . ')',
            'referral',
            $referralId
        );
    }

    private function completedRides(int $userId): int
    {
        try {
            return (int) RideBooking::query()
                ->where('user_id', $userId)
                ->where('status', BookingStatus::COMPLETED)
                ->count();
        } catch (Throwable $e) {
            return 0;
        }
    }
}
