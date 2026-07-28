<?php

namespace App\Http\Services\Panel\Admin\Countries\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Countries\Request\StoreCountryRequest;
use App\Models\InfrastructureNode;
use Illuminate\Http\JsonResponse;

class StoreCountryController extends Controller
{
    public function __invoke(StoreCountryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['type'] = 'country';

        $node = InfrastructureNode::create($data);

        return response()->json([
            'statusCode' => 201,
            'message'    => textByLanguage('تم إنشاء الدولة بنجاح', 'Country created successfully'),
            'data'       => ['id' => $node->id],
        ], 201);
    }
}
