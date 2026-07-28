<?php

namespace App\Http\Services\Panel\Drivers\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Drivers\Logic\DriverProfile;
use App\Http\Services\Panel\Drivers\Logic\DriverRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverRatingsController extends Controller
{
    public function __invoke(Request $request, int $driver, DriverRepository $drivers, DriverProfile $profile): JsonResponse
    {
        $model = $drivers->findOrFail($driver);
        $page = $profile->ratingsFeed($model);

        $items = collect($page->items())->map(fn ($r) => [
            'rating'  => round((float) $r->rating, 1),
            'comment' => $r->description ?: '',
            'rater'   => $r->rater_name ?: textByLanguage('مجهول', 'Anonymous'),
            'order'   => $r->orderId ? '#' . $r->orderId : '',
            'when'    => $r->created_at ? Carbon::parse($r->created_at)->diffForHumans() : '',
        ])->all();

        return response()->json([
            'items'    => $items,
            'total'    => $page->total(),
            'hasMore'  => $page->hasMorePages(),
            'nextPage' => $page->hasMorePages() ? $page->currentPage() + 1 : null,
        ]);
    }
}
