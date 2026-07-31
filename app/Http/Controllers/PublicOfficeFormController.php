<?php

namespace App\Http\Controllers;

use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\City;
use App\Models\InfrastructureNode;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Feeds the public office-application form with REAL, per-country data: the
 * cities and services that actually exist on the chosen country's shard. Both
 * are per-shard tables, so we activate that country's connection and read them —
 * exactly what the rider apps and the panel see for that country. Read-only and
 * best-effort: any failure returns empty lists so the form degrades to plain
 * text entry rather than breaking.
 */
class PublicOfficeFormController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $nodeId = (int) $request->query('country', 0);

        $node = InfrastructureNode::query()
            ->where('id', $nodeId)
            ->where('type', 'country')
            ->where('is_active', true)
            ->first();

        if ($node === null) {
            return response()->json(['cities' => [], 'services' => []]);
        }

        try {
            ShardManager::activate($node);
            $conn = TenantConnection::current();

            $cities = City::on($conn)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])
                ->all();

            // The `services` table titles a service with `title` / `title_en`
            // (there is NO `name` column — fillable lists one but the schema does
            // not have it). Localise, falling back across the two.
            $ar = app()->getLocale() === 'ar';
            $services = Service::on($conn)
                ->where('status', 1)
                ->orderBy('id')
                ->get(['id', 'title', 'title_en'])
                ->map(fn ($s) => ['id' => $s->id, 'name' => ($ar ? $s->title : $s->title_en) ?: ($s->title ?: $s->title_en)])
                ->all();
        } catch (Throwable $e) {
            return response()->json(['cities' => [], 'services' => []]);
        }

        return response()->json(['cities' => $cities, 'services' => $services]);
    }
}
