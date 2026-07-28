<?php

namespace App\Http\Services\Panel\RiderSupport\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Rating\RatingFeedService;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class RatingsPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, RatingFeedService $ratings): View
    {
        $maxStars = $request->query('max_stars');
        $maxStars = $maxStars !== null && $maxStars !== '' ? (int) $maxStars : null;
        $rateeType = $request->query('ratee_type');
        $rateeType = $rateeType !== null && $rateeType !== '' ? (string) $rateeType : null;

        $feed = $scope->isAdmin()
            ? $ratings->adminFeed($rateeType, $maxStars, 100)
            : $ratings->officeFeed((int) $scope->officeId(), $maxStars, 100);

        // Which of these ratings have already been flagged for follow-up. The
        // flag trail lives in the (per-country) audit log, so no schema change.
        $ids = array_map(fn ($r) => (int) $r['id'], $feed);
        $flaggedIds = $ids === []
            ? []
            : AuditLog::query()
                ->where('action', 'rating.flagged')
                ->where('subject_type', 'rating')
                ->whereIn('subject_id', $ids)
                ->pluck('subject_id')
                ->map(fn ($v) => (int) $v)
                ->all();

        return view('panel.ratings.index', [
            'entity'         => $scope->guard(),
            'isAdmin'        => $scope->isAdmin(),
            'ratings'        => $feed,
            'flaggedIds'     => $flaggedIds,
            'maxStars'       => $maxStars,
            'rateeTypeFilter' => $scope->isAdmin() ? $rateeType : null,
        ]);
    }
}
