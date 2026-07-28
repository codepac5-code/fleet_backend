<?php

namespace App\Http\Core\Classes\Ride;

use App\Http\Core\Classes\Auth\PhoneNumber;
use App\Http\Core\Classes\Rating\RatingService;
use App\Models\Office;
use Throwable;

class OfficeReadModel
{
    public function __construct(private RatingService $ratings)
    {
    }

    public function paginate(?array $restrictIds, ?string $search, ?int $cursorId, int $limit): array
    {
        $query = Office::query();

        if ($restrictIds !== null) {
            $query->whereIn('id', $restrictIds);
        }

        if ($search !== null && $search !== '') {
            $query->where(fn ($w) => $w->where('officeName', 'like', '%' . $search . '%')
                ->orWhere('displayName', 'like', '%' . $search . '%'));
        }

        if ($cursorId !== null) {
            $query->where('id', '<', $cursorId);
        }

        return $query->orderByDesc('id')->limit($limit + 1)->get()->all();
    }

    public function summary(int $officeId): array
    {
        $office = $this->find($officeId);
        $rating = $this->ratings->summaryFor('office', $officeId);

        $stored = $office !== null ? (float) ($office->rating ?? 0) : 0.0;

        return [
            'office_id' => $officeId,
            'name' => $this->name($office, $officeId),
            'logo_url' => $this->logo($office),
            'verified' => $office !== null ? $this->verified($office) : false,
            'monitoring' => true,
            'rating' => $stored > 0 ? $stored : (float) $rating['average'],
            'ratings_count' => (int) $rating['count'],
        ];
    }

    public function contact(int $officeId): array
    {
        $office = $this->find($officeId);
        $phone = $office !== null && $office->contactNumber ? (string) $office->contactNumber : null;

        return [
            'office_id' => $officeId,
            'office_name' => $this->name($office, $officeId),
            'phone_masked' => $phone !== null ? PhoneNumber::mask($phone) : null,
            'online' => $office !== null ? $this->verified($office) : true,
        ];
    }

    private function find(int $officeId): ?Office
    {
        try {
            return Office::query()->find($officeId);
        } catch (Throwable $e) {
            return null;
        }
    }

    private function name(?Office $office, int $officeId): string
    {
        if ($office === null) {
            return 'Office #' . $officeId;
        }

        $name = $office->displayName ?: $office->officeName;

        return $name !== null && $name !== '' ? (string) $name : 'Office #' . $officeId;
    }

    private function logo(?Office $office): ?string
    {
        if ($office === null || ! $office->logo) {
            return null;
        }

        return asset('storage/' . $office->logo);
    }

    private function verified(Office $office): bool
    {
        $status = $office->status;

        if (is_bool($status)) {
            return $status;
        }

        return in_array((string) $status, ['1', 'active', 'approved', 'verified'], true);
    }
}
