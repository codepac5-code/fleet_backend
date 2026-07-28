<?php

namespace App\Http\Core\Classes\Places;

use App\Http\Core\Exceptions\DomainException;
use App\Http\Core\Repositories\Places\SavedPlaceRepository;
use App\Models\SavedPlace;

class SavedPlaceService
{
    private const LABELS = ['home', 'work', 'other'];

    public function __construct(private SavedPlaceRepository $repository)
    {
    }

    public function list(int $userId): array
    {
        return $this->repository->listForUser($userId)
            ->map(fn (SavedPlace $p) => $this->present($p))
            ->all();
    }

    public function create(int $userId, string $label, string $title, float $lat, float $lng): array
    {
        $place = $this->repository->create([
            'user_id' => $userId,
            'label' => $this->normalizeLabel($label),
            'title' => $title,
            'lat' => $lat,
            'lng' => $lng,
        ]);

        return $this->present($place);
    }

    public function update(int $userId, int $id, array $attrs): array
    {
        $place = $this->owned($userId, $id);

        if (isset($attrs['label'])) {
            $place->label = $this->normalizeLabel((string) $attrs['label']);
        }

        foreach (['title', 'lat', 'lng'] as $field) {
            if (array_key_exists($field, $attrs)) {
                $place->{$field} = $attrs[$field];
            }
        }

        $this->repository->save($place);

        return $this->present($place);
    }

    public function delete(int $userId, int $id): void
    {
        $this->repository->delete($this->owned($userId, $id));
    }

    private function owned(int $userId, int $id): SavedPlace
    {
        $place = $this->repository->findForUser($id, $userId);

        if ($place === null) {
            throw DomainException::notFound();
        }

        return $place;
    }

    private function normalizeLabel(string $label): string
    {
        $label = strtolower(trim($label));

        return in_array($label, self::LABELS, true) ? $label : 'other';
    }

    private function present(SavedPlace $place): array
    {
        return [
            'id' => (int) $place->id,
            'label' => $place->label,
            'title' => $place->title,
            'lat' => (float) $place->lat,
            'lng' => (float) $place->lng,
        ];
    }
}
