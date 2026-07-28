<?php

namespace App\Http\Services\Panel\Admin\Ops\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Ops\HeartbeatService;
use App\Models\EventOutbox;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class OpsHealthPageController extends Controller
{
    public function __invoke(HeartbeatService $heartbeats): View
    {
        return view('panel.ops.index', [
            'pending' => $this->safeCount(fn () => DB::table('jobs')->count()),
            'byQueue' => $this->pendingByQueue(),
            'failedCount' => $this->safeCount(fn () => DB::table('failed_jobs')->count()),
            'failed' => $this->recentFailed(),
            'outboxPending' => $this->safeCount(fn () => EventOutbox::query()->where('status', 'pending')->count()),
            'daemons' => $heartbeats->all(),
        ]);
    }

    private function pendingByQueue(): array
    {
        try {
            return DB::table('jobs')
                ->selectRaw('queue, COUNT(*) as c')
                ->groupBy('queue')
                ->pluck('c', 'queue')
                ->all();
        } catch (Throwable $e) {
            return [];
        }
    }

    private function recentFailed(): array
    {
        try {
            return DB::table('failed_jobs')
                ->orderByDesc('id')
                ->limit(25)
                ->get(['id', 'uuid', 'queue', 'exception', 'failed_at'])
                ->map(fn ($f) => [
                    'id' => $f->id,
                    'queue' => $f->queue,
                    'error' => mb_strimwidth((string) strtok((string) $f->exception, "\n"), 0, 160, '…'),
                    'failed_at' => $f->failed_at,
                ])
                ->all();
        } catch (Throwable $e) {
            return [];
        }
    }

    private function safeCount(callable $fn): int
    {
        try {
            return (int) $fn();
        } catch (Throwable $e) {
            return 0;
        }
    }
}
