<?php

namespace App\Http\Core\Classes\Account;

use App\Http\Core\Exceptions\DomainException;
use App\Http\Core\Repositories\Account\SafetyContactRepository;
use App\Models\SafetyContact;

class SafetyContactService
{
    public function __construct(private SafetyContactRepository $repository)
    {
    }

    public function list(int $userId): array
    {
        return $this->repository->listForUser($userId)
            ->map(fn (SafetyContact $c) => $this->present($c))
            ->all();
    }

    public function create(int $userId, string $name, string $phone, bool $autoShare): array
    {
        $contact = $this->repository->create([
            'user_id' => $userId,
            'name' => $name,
            'phone' => $phone,
            'auto_share' => $autoShare,
        ]);

        return $this->present($contact);
    }

    public function delete(int $userId, int $id): void
    {
        $contact = $this->repository->findForUser($id, $userId);

        if ($contact === null) {
            throw DomainException::notFound();
        }

        $this->repository->delete($contact);
    }

    private function present(SafetyContact $contact): array
    {
        return [
            'id' => (int) $contact->id,
            'name' => $contact->name,
            'phone' => $contact->phone,
            'auto_share' => (bool) $contact->auto_share,
        ];
    }
}
