<?php

namespace App\Http\Core\Repositories\Account;

use App\Models\RiderPaymentMethod;
use Illuminate\Support\Collection;

interface RiderPaymentMethodRepository
{
    public function listForUser(int $userId): Collection;

    public function create(array $attributes): RiderPaymentMethod;

    public function findForUser(int $id, int $userId): ?RiderPaymentMethod;

    public function existsForUser(int $userId): bool;

    public function clearDefaults(int $userId): void;

    public function firstForUser(int $userId): ?RiderPaymentMethod;

    public function save(RiderPaymentMethod $method): void;

    public function delete(RiderPaymentMethod $method): void;
}
