<?php

namespace App\Http\Services\Driver\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Auth\DriverAuthService;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Services\User\Support\Reply;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Driver profile + vehicle updates (`PATCH /driver/me`, `PATCH /driver/vehicle`).
 */
class DriverProfileController extends Controller
{
    public function __construct(private DriverAuthService $auth)
    {
    }

    public function updateMe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'firstName' => ['sometimes', 'string', 'max:120'],
            'lastName' => ['sometimes', 'string', 'max:120'],
            'email' => ['sometimes', 'nullable', 'email', 'max:190'],
            'phoneNumber' => ['sometimes', 'string', 'max:24'],
        ]);

        $driver = $request->user();
        $driver->fill($data);
        $driver->save();

        return Reply::ok($this->auth->present($driver));
    }

    public function updateVehicle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'model' => ['sometimes', 'string', 'max:120'],
            'plate' => ['sometimes', 'string', 'max:32'],
            'color' => ['sometimes', 'string', 'max:40'],
            'modelYear' => ['sometimes', 'integer', 'min:1950', 'max:2100'],
            'seatsCount' => ['sometimes', 'integer', 'min:1', 'max:20'],
        ]);

        $driver = $request->user();
        $vehicle = $driver->vehicleId !== null ? Vehicle::query()->find($driver->vehicleId) : null;

        if ($vehicle === null) {
            throw DomainException::notFound();
        }

        $vehicle->fill($data);
        $vehicle->save();

        return Reply::ok([
            'id' => (int) $vehicle->id,
            'model' => $vehicle->model,
            'plate' => $vehicle->plate,
            'color' => $vehicle->color,
            'modelYear' => $vehicle->modelYear !== null ? (int) $vehicle->modelYear : null,
            'seatsCount' => $vehicle->seatsCount !== null ? (int) $vehicle->seatsCount : null,
        ]);
    }
}
