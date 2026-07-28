<?php

namespace App\Http\Services\User\Profile\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Core\Repositories\Places\SavedPlaceRepository;
use App\Http\Services\User\Profile\Requests\SavePlaceRequest;
use App\Http\Services\User\Support\Presenters\ResourcePresenter;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlacesController extends Controller
{
    public function __construct(private SavedPlaceRepository $places)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $data = $this->places->listForUser((int) $request->user()->id)
            ->map(fn ($place) => ResourcePresenter::place($place))
            ->all();

        return Reply::ok($data);
    }

    public function store(SavePlaceRequest $request): JsonResponse
    {
        $data = $request->validated();

        $place = $this->places->create([
            'user_id' => (int) $request->user()->id,
            'label' => $data['label'],
            'icon' => $data['icon'] ?? 'pin',
            'title' => $data['title'] ?? $data['label'],
            'address' => $data['address'] ?? null,
            'lat' => $data['lat'],
            'lng' => $data['lng'],
        ]);

        return Reply::ok(ResourcePresenter::place($place), 201);
    }

    public function update(SavePlaceRequest $request, int $id): JsonResponse
    {
        $place = $this->places->findForUser($id, (int) $request->user()->id);

        if ($place === null) {
            throw DomainException::notFound();
        }

        $data = $request->validated();

        $place->label = $data['label'];
        $place->icon = $data['icon'] ?? $place->icon;
        $place->title = $data['title'] ?? $data['label'];
        $place->address = $data['address'] ?? $place->address;
        $place->lat = $data['lat'];
        $place->lng = $data['lng'];
        $this->places->save($place);

        return Reply::ok(ResourcePresenter::place($place));
    }

    public function destroy(Request $request, int $id): Response
    {
        $place = $this->places->findForUser($id, (int) $request->user()->id);

        if ($place === null) {
            throw DomainException::notFound();
        }

        $this->places->delete($place);

        return response()->noContent();
    }
}
