<?php

namespace App\Http\Services\Setup\Controller;

use App\Http\Controllers\Controller;
use App\Models\InfrastructureNode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreateCountryController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'            => ['required', 'string', 'max:120'],
            'country_code'    => ['required', 'string', 'size:2'],
            'city'            => ['nullable', 'string', 'max:120'],
            'currency_code'   => ['nullable', 'string', 'size:3'],
            'currency_symbol' => ['nullable', 'string', 'max:8'],
        ]);

        $global = config('database.connections.global');

        try {
            $node = InfrastructureNode::query()->updateOrCreate(
                ['type' => 'country', 'country_code' => strtoupper($data['country_code'])],
                [
                    'name'            => $data['name'],
                    'city'            => $data['city'] ?? null,
                    'currency_code'   => $data['currency_code'] ? strtoupper($data['currency_code']) : null,
                    'currency_symbol' => $data['currency_symbol'] ?? null,
                    'db_host'         => $global['host'] ?? '127.0.0.1',
                    'db_port'         => $global['port'] ?? 3306,
                    'db_name'         => $global['database'] ?? null,
                    'db_user'         => $global['username'] ?? null,
                    'db_pass'         => $global['password'] ?? '',
                    'is_active'       => true,
                ]
            );
        } catch (\Throwable $e) {
            return response()->json([
                'ok'      => false,
                'message' => textByLanguage('فشل إنشاء الدولة', 'Failed to create country'),
                'error'   => $e->getMessage(),
            ], 200);
        }

        return response()->json([
            'ok'      => true,
            'message' => textByLanguage('تم تسجيل أول دولة', 'First country registered'),
            'data'    => ['id' => $node->id],
        ]);
    }
}
