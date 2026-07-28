<?php

namespace App\Http\Core\Classes\Account;

use App\Http\Core\Exceptions\DomainException;
use App\Http\Core\Repositories\Account\RiderPaymentMethodRepository;
use App\Models\RiderPaymentMethod;
use Throwable;

class RiderPaymentMethodService
{
    public function __construct(
        private CardGateway $gateway,
        private RiderPaymentMethodRepository $repository
    ) {
    }

    public function list(int $userId): array
    {
        $cards = $this->repository->listForUser($userId)
            ->map(fn (RiderPaymentMethod $m) => $this->present($m))
            ->all();

        $cards[] = ['id' => 'wallet', 'type' => 'wallet', 'is_default' => false];
        $cards[] = ['id' => 'cash', 'type' => 'cash', 'is_default' => false];

        return $cards;
    }

    public function setupIntent(int $userId): array
    {
        try {
            return $this->gateway->setupIntent($userId);
        } catch (Throwable $e) {
            throw DomainException::make('payments_unavailable');
        }
    }

    public function add(int $userId, string $token, ?string $brand, ?string $last4, ?string $exp): array
    {
        if ($token === '') {
            throw DomainException::make('validation_failed');
        }

        if ($brand === null || $last4 === null) {
            $described = $this->gateway->describe($token);

            if ($described === null) {
                throw DomainException::make('payments_unavailable');
            }

            $brand = $described['brand'] ?? $brand;
            $last4 = $described['last4'] ?? $last4;
            $exp = $described['exp'] ?? $exp;
        }

        $isFirst = ! $this->repository->existsForUser($userId);

        $method = $this->repository->create([
            'user_id' => $userId,
            'type' => 'card',
            'brand' => $brand,
            'last4' => $last4,
            'exp' => $exp,
            'gateway_token' => $token,
            'is_default' => $isFirst,
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

    private function owned(int $userId, int $id): RiderPaymentMethod
    {
        $method = $this->repository->findForUser($id, $userId);

        if ($method === null) {
            throw DomainException::notFound();
        }

        return $method;
    }

    private function present(RiderPaymentMethod $method): array
    {
        return [
            'id' => (int) $method->id,
            'type' => $method->type,
            'brand' => $method->brand,
            'last4' => $method->last4,
            'exp' => $method->exp,
            'is_default' => (bool) $method->is_default,
        ];
    }
}
