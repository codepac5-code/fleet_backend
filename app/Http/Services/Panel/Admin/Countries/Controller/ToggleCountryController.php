<?php

namespace App\Http\Services\Panel\Admin\Countries\Controller;

use App\Http\Controllers\Controller;
use App\Models\InfrastructureNode;
use Illuminate\Http\JsonResponse;

class ToggleCountryController extends Controller
{
    public function __invoke(int $node): JsonResponse
    {
        $country = InfrastructureNode::query()
            ->where('id', $node)
            ->where('type', 'country')
            ->firstOrFail();

        $country->update(['is_active' => ! $country->is_active]);

        return response()->json([
            'statusCode' => 200,
            'message'    => $country->is_active
                ? textByLanguage('تم تفعيل الدولة', 'Country enabled')
                : textByLanguage('تم تعطيل الدولة', 'Country disabled'),
            'data'       => ['is_active' => (bool) $country->is_active],
        ]);
    }
}
