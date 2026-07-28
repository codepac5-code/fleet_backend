<?php

namespace App\Http\Core\Classes\Audit;

use App\Models\AuditLog;
use Throwable;

class AuditLogService
{
    /**
     * Best-effort audit write: logging must NEVER break the action it records.
     * Returns null on failure — e.g. in "All countries" aggregate mode the
     * audit_logs table resolves to a non-insertable UNION view, and a global
     * entity's edit should still succeed even though its per-country audit
     * can't be written.
     */
    public function record(
        string $action,
        ?string $actorType = null,
        ?int $actorId = null,
        ?string $subjectType = null,
        ?int $subjectId = null,
        array $metadata = [],
        ?string $ip = null
    ): ?AuditLog {
        try {
            return AuditLog::query()->create([
                'actor_type' => $actorType,
                'actor_id' => $actorId,
                'action' => $action,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'metadata' => $metadata,
                'ip' => $ip,
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            return null;
        }
    }

    public function forSubject(string $subjectType, int $subjectId, int $limit = 50): array
    {
        return AuditLog::query()
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->all();
    }

    public function forActor(string $actorType, int $actorId, int $limit = 50): array
    {
        return AuditLog::query()
            ->where('actor_type', $actorType)
            ->where('actor_id', $actorId)
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->all();
    }
}
