<?php

namespace App\Http\Services\User\Marketplace\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Places\PlacesService;
use App\Http\Services\User\Marketplace\Requests\GeocodeRequest;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;

class GeocodeController extends Controller
{
    public function __construct(private PlacesService $places)
    {
    }

    public function reverse(GeocodeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $result = $this->places->reverse((float) $data['lat'], (float) $data['lng']);

        // GeocodingProvider::reverse() returns the formatted address under `title`.
        return Reply::ok(['address' => $result['title'] ?? ($result['address'] ?? null)]);
    }
}
