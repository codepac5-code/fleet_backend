<?php

namespace App\Http\Services\User\Payments\Logic;

use App\Http\Core\Classes\Account\CardGateway;
use App\Http\Core\Classes\Account\PromoService;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Core\Repositories\Account\RiderPaymentMethodRepository;
use App\Models\RiderPaymentMethod;
use Throwable;

class PaymentMethodService
{
    public function __construct(
        private RiderPaymentMethodRepository $repository,
        private CardGateway $gateway,
        private PromoService $promos
    ) {
    }

    public function list(int $userId): array
    {
        return $this->repository->listForUser($userId)
            ->map(fn (RiderPaymentMethod $m) => $this->present($m))
            ->values()
            ->all();
    }

    public function save(int $userId, string $stripePaymentMethodId, bool $setDefault): array
    {
        $isFirst = ! $this->repository->existsForUser($userId);

        if ($setDefault || $isFirst) {
            $this->repository->clearDefaults($userId);
        }

        // Ask the gateway what this card actually IS. Without it every saved
        // card landed with brand/last4/exp NULL and the rider's wallet listed
        // an anonymous, unidentifiable row — there is a sibling service
        // (Core\Classes\Account\RiderPaymentMethodService::add) that does this
        // correctly, but the routed controller uses THIS one.
        //
        // Fail-soft: a Stripe hiccup must not lose a card the gateway already
        // holds; it just lists without its brand until the next refresh.
        $card = [];

        try {
            $card = $this->gateway->describe($stripePaymentMethodId) ?? [];
        } catch (Throwable $e) {
            $card = [];
        }

        $method = $this->repository->create([
            'user_id' => $userId,
            'type' => 'card',
            'gateway_token' => $stripePaymentMethodId,
            'stripe_payment_method_id' => $stripePaymentMethodId,
            'brand' => $card['brand'] ?? null,
            'last4' => $card['last4'] ?? null,
            'exp' => $card['exp'] ?? null,
            'is_default' => $setDefault || $isFirst,
        ]);

        return $this->present($method);
    }

    public function setDefault(int $userId, int $id): array
    {
        $method = $this->owned($userId, $id);

        $this->repository->clearDefaults($userId);
        $method->is_default = true;
        $this->repository->save($method);

        return $this->present($method);
    }

    public function remove(int $userId, int $id): void
    {
        $method = $this->owned($userId, $id);
        $wasDefault = (bool) $method->is_default;
        $this->repository->delete($method);

        if ($wasDefault) {
            $next = $this->repository->firstForUser($userId);

            if ($next !== null) {
                $next->is_default = true;
                $this->repository->save($next);
            }
        }
    }

    public function setupIntent(int $userId): array
    {
        try {
            return $this->gateway->setupIntent($userId);
        } catch (Throwable $e) {
            throw DomainException::make('payments_unavailable', 503);
        }
    }

    public function promoList(): array
    {
        return ['promos' => $this->promos->available()];
    }

    public function redeem(string $code): array
    {
        $result = $this->promos->apply($code);
        $valid = (bool) ($result['valid'] ?? false);
        $isPercentage = (bool) ($result['isPercentage'] ?? true);

        return [
            'code' => $result['code'] ?? strtoupper(trim($code)),
            'applied' => $valid,
            'discount' => $valid ? (float) ($result['discount'] ?? 0.0) : 0.0,
            'discountType' => $isPercentage ? 'percentage' : 'fixed',
            'message' => $valid ? ($result['discount_label'] ?? 'Applied') : 'Invalid or expired code.',
        ];
    }

    private function owned(int $userId, int $id): RiderPaymentMethod
    {
        $method = $this->repository->findForUser($id, $userId);

        if ($method === null) {
            throw DomainException::notFound();
        }

        return $method;
    }

    private function present(RiderPaymentMethod $m): array
    {
        return [
            'id' => (int) $m->id,
            'user_id' => (int) $m->user_id,
            'type' => $m->type,
            'brand' => $m->brand,
            'last4' => $m->last4,
            'exp' => $m->exp,
            'is_default' => (bool) $m->is_default,
            'stripe_payment_method_id' => $m->stripe_payment_method_id,
            'stripe_setup_intent_id' => $m->stripe_setup_intent_id,
        ];
    }
}
