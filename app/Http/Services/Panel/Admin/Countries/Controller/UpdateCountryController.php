<?php

namespace App\Http\Services\Panel\Admin\Countries\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Countries\Request\UpdateCountryRequest;
use App\Models\InfrastructureNode;
use Illuminate\Http\JsonResponse;

class UpdateCountryController extends Controller
{
    public function __invoke(UpdateCountryRequest $request, int $node): JsonResponse
    {
        $country = InfrastructureNode::query()
            ->where('id', $node)
            ->where('type', 'country')
            ->firstOrFail();

        $data = $request->validated();

        if (! $request->filled('db_pass')) {
            unset($data['db_pass']);
        }

        $country->update($data);

        return response()->json([
            'statusCode' => 200,
            'message'    => textByLanguage('تم تحديث بيانات الدولة', 'Country updated'),
            'data'       => ['id' => $country->id],
        ]);
    }
}
