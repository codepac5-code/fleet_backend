<?php

namespace App\Http\Core\Classes\Marketplace;

use App\Models\FavoriteOffice;

class FavoriteOfficeService
{
    public function add(int $userId, int $officeId): FavoriteOffice
    {
        return FavoriteOffice::query()->firstOrCreate([
            'user_id' => $userId,
            'office_id' => $officeId,
        ]);
    }

    public function remove(int $userId, int $officeId): void
    {
        FavoriteOffice::query()
            ->where('user_id', $userId)
            ->where('office_id', $officeId)
            ->delete();
    }

    public function list(int $userId): array
    {
        return FavoriteOffice::query()
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->pluck('office_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function isFavorite(int $userId, int $officeId): bool
    {
        return FavoriteOffice::query()
            ->where('user_id', $userId)
            ->where('office_id', $officeId)
            ->exists();
    }
}
