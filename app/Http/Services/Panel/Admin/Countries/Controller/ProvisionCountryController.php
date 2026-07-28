<?php

namespace App\Http\Services\Panel\Admin\Countries\Controller;

use App\Http\Controllers\Controller;
use App\Models\InfrastructureNode;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class ProvisionCountryController extends Controller
{
    public function __invoke(int $node): JsonResponse
    {
        $country = InfrastructureNode::query()
            ->where('id', $node)
            ->where('type', 'country')
            ->firstOrFail();

        if (! $country->db_host || ! $country->db_name) {
            return response()->json([
                'ok'      => false,
                'message' => textByLanguage('لا توجد بيانات قاعدة بيانات لهذه الدولة', 'This country has no database credentials'),
            ], 422);
        }

        try {
            $exit   = Artisan::call('fleet:shard-provision', ['--id' => $country->id]);
            $output = trim(Artisan::output());

            return response()->json([
                'ok'      => $exit === 0,
                'message' => $exit === 0
                    ? textByLanguage('تم تجهيز مخطط قاعدة البيانات', 'Database schema provisioned')
                    : textByLanguage('فشل تجهيز المخطط', 'Provisioning failed'),
                'data'    => ['output' => $output],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok'      => false,
                'message' => textByLanguage('فشل تجهيز المخطط', 'Provisioning failed'),
                'error'   => $e->getMessage(),
            ], 200);
        }
    }
}
