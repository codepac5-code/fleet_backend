<?php

namespace App\Http\Services\User\Marketplace\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\Marketplace\Logic\MarketplaceService;
use App\Http\Services\User\Marketplace\Requests\EstimateRequest;
use App\Http\Services\User\Marketplace\Requests\OfficesSearchRequest;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function __construct(private MarketplaceService $marketplace)
    {
    }

    public function officesSearch(OfficesSearchRequest $request): JsonResponse
    {
        return Reply::ok($this->marketplace->officesSearch($request->validated()['route']));
    }

    public function estimate(EstimateRequest $request): JsonResponse
    {
        $data = $request->validated();

        return Reply::ok($this->marketplace->estimate($data['pickup'], $data['dropoff']));
    }

    public function officeProfile(Request $request, int $id): JsonResponse
    {
        return Reply::ok($this->marketplace->officeProfile($id));
    }

    public function catalogServices(Request $request): JsonResponse
    {
        return Reply::ok($this->marketplace->catalogServices());
    }

    public function catalogClasses(Request $request): JsonResponse
    {
        $service = $request->filled('service') ? (int) $request->query('service') : null;

        return Reply::ok($this->marketplace->catalogClasses($service));
    }

    public function placesSuggest(Request $request): JsonResponse
    {
        $user = $request->user();

        return Reply::ok($this->marketplace->placesSuggest(
            (int) $user->id,
            trim((string) $request->query('q', '')),
            $this->resolveCountry($request, $user)
        ));
    }

    /**
     * The country to restrict place suggestions to. Authoritative, not just the
     * client header: X-Country (device location) → the user's stored
     * `current_country` → the country derived from their dial code. Guarantees a
     * value so Google predictions are always confined to the rider's country.
     */
    private function resolveCountry(Request $request, $user): ?string
    {
        $header = trim((string) $request->header('X-Country', ''));
        if ($header !== '') {
            return $header;
        }

        if (! empty($user->current_country)) {
            return (string) $user->current_country;
        }

        if (! empty($user->dialCode)) {
            $iso = \Illuminate\Support\Facades\DB::table('countries')
                ->where('phone_code', $user->dialCode)
                ->value('iso2');
            if (! empty($iso)) {
                return (string) $iso;
            }
        }

        return null;
    }

    public function placeDetails(Request $request): JsonResponse
    {
        return Reply::ok($this->marketplace->placeDetails(
            trim((string) $request->query('place_id', ''))
        ));
    }
}
