<?php

namespace App\Http\Services\Driver\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Auth\DriverAuthService;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\User\Support\Reply;
use App\Models\DriverApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public driver onboarding writes: apply to drive and request an office link.
 * Both create a `driver_applications` row (kind `apply` / `link`) reviewed by
 * FleetOS or the selected office. No auth — the driver has no account yet.
 */
class DriverApplicationsController extends Controller
{
    public function __construct(private DriverAuthService $auth)
    {
    }

    /** POST /driver/applications — "Apply to drive". */
    public function apply(Request $request): JsonResponse
    {
        $data = $request->validate([
            'country_iso' => ['nullable', 'string', 'max:2'],
            'full_name' => ['sometimes', 'string', 'max:190'],
            'name' => ['sometimes', 'string', 'max:190'],
            'first_name' => ['required', 'string', 'max:30'],
            'last_name' => ['required', 'string', 'max:30'],
            'gender' => ['nullable', 'in:male,female'],
            'phone' => ['required', 'string', 'max:24'],
            'country' => ['required', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'region' => ['required', 'string', 'max:120'],
            'address' => ['required', 'string', 'max:500'],
            'car_owner' => ['nullable', 'boolean'],
            'vehicle_type' => ['nullable', 'string', 'max:120'],
            'license_number' => ['nullable', 'string', 'max:64'],
            'license_file' => ['nullable', 'string'],       // optional base64 document
            'license_ext' => ['nullable', 'string', 'max:8'],
            'office_id' => ['nullable', 'integer'],
            'invite_code' => ['nullable', 'string', 'max:64'],
        ]);

        // Create the application in the shard of the country the driver selected.
        $iso2 = $data['country_iso'] ?? $request->header('X-Country');
        if ($iso2) {
            $node = ShardManager::byCountryCode($iso2);
            if ($node === null) {
                throw DomainException::make('country_not_supported', 422);
            }
            ShardManager::activate($node);
        }

        $fullName = $data['full_name']
            ?? $data['name']
            ?? trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));

        $result = $this->auth->apply($data['phone'], [
            'name' => $fullName,
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'gender' => $data['gender'] ?? null,
            'country' => $data['country'] ?? null,
            'city' => $data['city'] ?? null,
            'region' => $data['region'] ?? null,
            'address' => $data['address'] ?? null,
            'car_owner' => (bool) ($data['car_owner'] ?? false),
            'vehicle_type' => $data['vehicle_type'] ?? null,
            'license_number' => $data['license_number'] ?? null,
            'license_file' => $data['license_file'] ?? null,
            'license_ext' => $data['license_ext'] ?? null,
            'office_id' => $data['office_id'] ?? null,
            'invite_code' => $data['invite_code'] ?? null,
        ]);

        return Reply::ok([
            'id' => $result['application_id'],
            'status' => $result['status'],
        ], 201);
    }

    /** POST /offices/link-requests — link an existing/new driver to an office. */
    public function linkOffice(Request $request): JsonResponse
    {
        $data = $request->validate([
            'invite_code' => ['nullable', 'string', 'max:64'],
            'office_id' => ['nullable', 'integer'],
            'office_query' => ['nullable', 'string', 'max:190'],
            'driver_ref' => ['nullable', 'string', 'max:64'],
        ]);

        // Phone comes from the authenticated driver when switching office from
        // inside the app; during onboarding the link is anonymous until review.
        // The route is public, so resolve the driver via the guard (nullable).
        $driver = auth('driver')->user();
        $phone = $driver !== null
            ? ltrim((string) $driver->dialCode, '+') . (string) $driver->phoneNumber
            : ($data['driver_ref'] ?? '');

        $application = DriverApplication::query()->create([
            'phone' => $phone,
            'name' => $driver !== null
                ? trim(((string) $driver->firstName) . ' ' . ((string) $driver->lastName))
                : null,
            'city' => $data['office_query'] ?? null,
            'office_id' => $data['office_id'] ?? null,
            'invite_code' => $data['invite_code'] ?? null,
            'kind' => 'link',
            'status' => 'pending',
        ]);

        return Reply::ok([
            'linkRequestId' => (int) $application->id,
            'status' => $application->status,
        ], 201);
    }
}
