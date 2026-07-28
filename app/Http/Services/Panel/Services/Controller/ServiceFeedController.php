<?php

namespace App\Http\Services\Panel\Services\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Services\Logic\ServiceAnalytics;
use App\Http\Services\Panel\Services\Logic\ServiceRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceFeedController extends Controller
{
    public function __invoke(Request $request, int $service, ServiceRepository $services, ServiceAnalytics $analytics): JsonResponse
    {
        $type = $request->query('type') === 'offices' ? 'offices' : 'drivers';
        $model = $services->findOrFail($service);

        $page = $type === 'offices'
            ? $analytics->officesFeed($model)
            : $analytics->driversFeed($model);

        $items = collect($page->items())->map(function ($row) use ($type) {
            if ($type === 'offices') {
                return [
                    'id'      => (int) $row->id,
                    'name'    => $row->name ?: '—',
                    'meta'    => $row->city ?: '',
                    'extra'   => (int) $row->drivers,
                    'trips'   => (int) $row->trips,
                    'revenue' => getPriceFormat($row->revenue),
                ];
            }

            return [
                'id'      => (int) $row->id,
                'name'    => trim($row->name) ?: '—',
                'meta'    => trim(($row->dialCode ? '+' . ltrim($row->dialCode, '+') . ' ' : '') . $row->phoneNumber),
                'extra'   => round((float) $row->rating, 1),
                'trips'   => (int) $row->trips,
                'revenue' => getPriceFormat($row->revenue),
            ];
        })->all();

        return response()->json([
            'type'     => $type,
            'items'    => $items,
            'total'    => $page->total(),
            'hasMore'  => $page->hasMorePages(),
            'nextPage' => $page->hasMorePages() ? $page->currentPage() + 1 : null,
        ]);
    }
}
