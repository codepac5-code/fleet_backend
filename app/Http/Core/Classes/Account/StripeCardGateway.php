<?php

namespace App\Http\Core\Classes\Account;

use App\Http\Core\Exceptions\DomainException;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Throwable;

/**
 * Real Stripe implementation of {@see CardGateway}.
 *
 * Bound in place of {@see NullCardGateway} whenever `services.stripe.secret`
 * is configured (see AppServiceProvider). Uses SetupIntents for card capture
 * (raw PAN/CVV never touch the backend) and describes saved `pm_…` tokens.
 */
class StripeCardGateway implements CardGateway
{
    private StripeClient $stripe;

    public function __construct(?string $secret = null)
    {
        $secret = (string) ($secret ?? config('services.stripe.secret'));

        if ($secret === '') {
            throw new RuntimeException('payments_unavailable');
        }

        $this->stripe = new StripeClient($secret);
    }

    public function setupIntent(int $userId): array
    {
        $user = User::query()->find($userId);

        if ($user === null) {
            throw new RuntimeException('payments_unavailable');
        }

        return $this->guardStripe(function () use ($user) {
            $customerId = $this->ensureCustomer($user);

            $intent = $this->stripe->setupIntents->create([
                'customer' => $customerId,
                'usage' => 'off_session',
                'payment_method_types' => ['card'],
            ]);

            return [
                'setupIntentId' => $intent->id,
                'clientSecret' => $intent->client_secret,
                'customerId' => $customerId,
                // Handed to the client so the SDK is keyed from the SAME Stripe
                // account that minted this secret. Shipping the publishable key in
                // the app instead invites a test key against a live secret (or the
                // reverse), which fails only at confirm time with an opaque error.
                // A publishable key is public by design — it can only create tokens.
                'publishableKey' => (string) config('services.stripe.public'),
            ];
        });
    }

    public function paymentIntent(int $userId, int $amountMinor, string $currency, ?int $paymentMethodId, string $idempotencyKey): array
    {
        $user = User::query()->find($userId);

        if ($user === null) {
            throw new RuntimeException('payments_unavailable');
        }

        return $this->guardStripe(function () use ($user, $userId, $amountMinor, $currency, $paymentMethodId, $idempotencyKey) {
            $customerId = $this->ensureCustomer($user);

            // Client-confirmed flow: the app confirms the intent with the returned
            // clientSecret + card (PaymentSheet). `allow_redirects: never` keeps it
            // return_url-free for wallet top-ups.
            $intent = $this->stripe->paymentIntents->create(
                [
                    'amount' => $amountMinor,
                    'currency' => strtolower($currency),
                    'customer' => $customerId,
                    'metadata' => [
                        'user_id' => (string) $userId,
                        'purpose' => 'topup',
                        'payment_method_id' => $paymentMethodId !== null ? (string) $paymentMethodId : '',
                        // The gateway webhook matches the ledger payment by this key
                        // (StripeGateway::verifyAndNormalize reads metadata.idempotency_key).
                        'idempotency_key' => $idempotencyKey,
                    ],
                    'automatic_payment_methods' => ['enabled' => true, 'allow_redirects' => 'never'],
                ],
                $idempotencyKey !== '' ? ['idempotency_key' => 'topup_pi_' . $idempotencyKey] : []
            );

            return [
                'id' => $intent->id,
                'clientSecret' => $intent->client_secret,
                'status' => $intent->status,
                'requiresAction' => in_array($intent->status, ['requires_action', 'requires_confirmation'], true),
            ];
        });
    }

    /**
     * Run a Stripe SDK call, converting any Stripe API error into a graceful
     * {@see DomainException} the app already handles — a 503 with a clear
     * message — instead of letting a raw SDK exception surface as a 500. The
     * commonest trigger here is an unsupported settlement currency (e.g. SYP on
     * the Syria shard, which Stripe rejects), where card top-up simply is not
     * available; the rider must see "unavailable", not a crash.
     */
    private function guardStripe(callable $fn)
    {
        try {
            return $fn();
        } catch (ApiErrorException $e) {
            Log::warning('Stripe card gateway error', ['message' => $e->getMessage()]);

            throw DomainException::make('payments_unavailable', 503, 'Card top-up is not available for your account currency yet.');
        }
    }

    public function paymentIntentStatus(string $paymentIntentId): ?string
    {
        try {
            $pi = $this->stripe->paymentIntents->retrieve($paymentIntentId, []);

            return $pi->status ?? null;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function describe(string $token): ?array
    {
        try {
            $pm = $this->stripe->paymentMethods->retrieve($token, []);
            $card = $pm->card ?? null;

            if ($card === null) {
                return null;
            }

            return [
                'brand' => (string) ($card->brand ?? ''),
                'last4' => (string) ($card->last4 ?? ''),
                'exp' => sprintf('%02d/%02d', (int) ($card->exp_month ?? 0), ((int) ($card->exp_year ?? 0)) % 100),
            ];
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Return the user's Stripe customer id, creating (and persisting) one on
     * first use.
     */
    private function ensureCustomer(User $user): string
    {
        $existing = (string) ($user->stripe_customer_id ?? '');

        if ($existing !== '') {
            return $existing;
        }

        $customer = $this->stripe->customers->create([
            'metadata' => ['user_id' => (string) $user->id],
            'phone' => (string) ($user->phoneNumber ?? ''),
        ]);

        $user->stripe_customer_id = $customer->id;
        $user->save();

        return $customer->id;
    }
}
