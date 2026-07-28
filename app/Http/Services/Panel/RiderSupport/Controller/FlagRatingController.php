<?php

namespace App\Http\Services\Panel\RiderSupport\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Audit\AuditLogService;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\RideRating;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FlagRatingController extends Controller
{
    public function __invoke(int $rating, Request $request, EntityScope $scope, AuditLogService $audit): RedirectResponse
    {
        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        // RideRating is per-country (ResolvesTenantConnection) → this only ever
        // resolves a rating on the active shard; no cross-country flagging.
        $model = RideRating::query()->find($rating);

        if ($model === null) {
            return back()->with('error', textByLanguage('التقييم غير موجود', 'Rating not found'));
        }

        $audit->record(
            'rating.flagged',
            $scope->isAdmin() ? 'admin' : 'office',
            $scope->isAdmin() ? null : $scope->officeId(),
            'rating',
            (int) $model->id,
            array_filter([
                'stars' => (int) $model->stars,
                'ratee' => $model->ratee_type . ':' . $model->ratee_id,
                'note' => $data['note'] ?? null,
            ]),
            $request->ip()
        );

        return back()->with('status', textByLanguage('تمّ وضع علامة للمتابعة', 'Flagged for follow-up'));
    }
}
