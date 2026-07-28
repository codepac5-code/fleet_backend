<?php

namespace App\Http\Services\Panel\Admin\Audit\Controller;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogPageController extends Controller
{
    public function __invoke(Request $request): View
    {
        // AuditLog uses ResolvesTenantConnection → this is already scoped to the
        // active country shard; one country never sees another's audit trail.
        $query = AuditLog::query()->orderByDesc('id');

        $action = trim((string) $request->query('action', ''));
        $actorType = trim((string) $request->query('actor_type', ''));

        if ($action !== '') {
            $query->where('action', 'like', '%' . $action . '%');
        }

        if ($actorType !== '') {
            $query->where('actor_type', $actorType);
        }

        $logs = $query->paginate(40)->withQueryString();

        $actorTypes = AuditLog::query()
            ->whereNotNull('actor_type')
            ->distinct()
            ->pluck('actor_type')
            ->all();

        return view('panel.audit.index', [
            'logs' => $logs,
            'action' => $action,
            'actorType' => $actorType,
            'actorTypes' => $actorTypes,
        ]);
    }
}
