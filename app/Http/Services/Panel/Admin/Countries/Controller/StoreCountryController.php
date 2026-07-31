<?php

namespace App\Http\Services\Panel\Admin\Countries\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\GeoServices\CountryProfiles;
use App\Http\Core\GeoServices\CountrySupportService;
use App\Http\Services\Panel\Admin\Countries\Request\StoreCountryRequest;
use App\Models\InfrastructureNode;
use Illuminate\Http\JsonResponse;

class StoreCountryController extends Controller
{
    public function __invoke(StoreCountryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['type'] = 'country';

        // Prefill the currency from the bundled profile when the admin left it
        // blank — a known country is fully set up from just its ISO code.
        $profile = CountryProfiles::for((string) ($data['country_code'] ?? ''));
        if ($profile !== null) {
            $data['currency_code']   = $data['currency_code']   ?? $profile['currency_code'];
            $data['currency_symbol'] = $data['currency_symbol'] ?? $profile['currency_symbol'];
        }

        $node = InfrastructureNode::create($data);

        // Register the currency globally so conversion/wallet/pricing recognise
        // it immediately, even before the shard is provisioned.
        app(CountrySupportService::class)->registerCurrency($node);

        return response()->json([
            'statusCode' => 201,
            'message'    => textByLanguage('تم إنشاء الدولة بنجاح', 'Country created successfully'),
            'data'       => ['id' => $node->id],
        ], 201);
    }
}
