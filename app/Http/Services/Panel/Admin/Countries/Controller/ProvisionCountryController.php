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

        // Cloning a full reference schema (150+ tables) + seeding takes longer
        // than PHP's default max_execution_time, which silently killed the request
        // mid-clone and surfaced as "Provisioning failed" even though the shard
        // was created. Let it run to completion and survive a client disconnect.
        @set_time_limit(0);
        @ignore_user_abort(true);

        try {
            // `--seed` is REQUIRED: without the catalog (roles/permissions, services,
            // documents, cancellation reasons, rating tags) the new country's offices
            // and employees resolve to zero permissions and no services.
            $exit   = Artisan::call('fleet:shard-provision', ['--id' => $country->id, '--seed' => true]);
            $output = trim(Artisan::output());

            return response()->json([
                'ok'      => $exit === 0,
                'message' => $exit === 0
                    ? textByLanguage('تم تجهيز قاعدة بيانات الدولة بالكامل', 'Country database fully provisioned')
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
