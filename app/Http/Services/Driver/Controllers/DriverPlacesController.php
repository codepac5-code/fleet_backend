<?php

namespace App\Http\Services\Driver\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Places\PlacesService;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Services\User\Support\Reply;
use App\Models\SavedPlace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Driver saved places (CRUD) + place autocomplete. Rows are scoped to the
 * authenticated driver via `saved_places.driver_id`.
 */
class DriverPlacesController extends Controller
{
    public function __construct(private PlacesService $places)
    {
    }

    private function present(SavedPlace $p): array
    {
        return [
            'id' => (int) $p->id,
            'label' => $p->label,
            'icon' => $p->icon,
            'title' => $p->title,
            'address' => $p->address,
            'lat' => $p->lat !== null ? (float) $p->lat : null,
            'lng' => $p->lng !== null ? (float) $p->lng : null,
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $items = SavedPlace::query()
            ->forDriver((int) $request->user()->id)
            ->orderByDesc('id')
            ->get()
            ->map(fn (SavedPlace $p) => $this->present($p))
            ->all();

        return Reply::ok(['items' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:32'],
            'icon' => ['nullable', 'string', 'max:16'],
            'title' => ['required', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $place = SavedPlace::query()->create([
            'driver_id' => (int) $request->user()->id,
            'label' => $data['label'],
            'icon' => $data['icon'] ?? 'pin',
            'title' => $data['title'],
            'address' => $data['address'] ?? null,
            'lat' => $data['lat'],
            'lng' => $data['lng'],
        ]);

        return Reply::ok($this->present($place), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'label' => ['sometimes', 'string', 'max:32'],
            'icon' => ['sometimes', 'nullable', 'string', 'max:16'],
            'title' => ['sometimes', 'string', 'max:120'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'lat' => ['sometimes', 'numeric', 'between:-90,90'],
            'lng' => ['sometimes', 'numeric', 'between:-180,180'],
        ]);

        $place = $this->owned($request, $id);
        $place->fill($data);
        $place->save();

        return Reply::ok($this->present($place));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->owned($request, $id)->delete();

        return Reply::ok(['ok' => true]);
    }

    public function search(Request $request): JsonResponse
    {
        $q = (string) $request->query('q', '');
        $lat = $request->query('lat') !== null ? (float) $request->query('lat') : null;
        $lng = $request->query('lng') !== null ? (float) $request->query('lng') : null;

        $results = $q === '' ? [] : $this->places->autocomplete($q, $lat, $lng, null);

        return Reply::ok(['results' => $results]);
    }

    private function owned(Request $request, int $id): SavedPlace
    {
        $place = SavedPlace::query()->where('id', $id)->forDriver((int) $request->user()->id)->first();

        if ($place === null) {
            throw DomainException::notFound();
        }

        return $place;
    }
}
