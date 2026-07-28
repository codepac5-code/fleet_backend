<?php

namespace App\Http\Services\Panel\Admin\Offices\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeAnalytics;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfficeFeedController extends Controller
{
    public function __invoke(Request $request, int $office, OfficeRepository $offices, OfficeAnalytics $analytics): JsonResponse
    {
        $type = $request->query('type');
        $type = in_array($type, ['vehicles', 'services'], true) ? $type : 'drivers';
        $model = $offices->findOrFail($office);

        $page = match ($type) {
            'vehicles' => $analytics->vehiclesFeed($model),
            'services' => $analytics->servicesFeed($model),
            default    => $analytics->driversFeed($model),
        };

        $items = collect($page->items())->map(function ($row) use ($type) {
            if ($type === 'vehicles') {
                return [
                    'name'  => trim($row->vehicleBrand . ' ' . $row->model) ?: '—',
                    'meta'  => $row->plate ?: '',
                    'stat'  => $row->seatsCount ? $row->seatsCount . ' ' . textByLanguage('مقعد', 'seats') : '',
                    'value' => trim($row->driver) ?: '—',
                ];
            }

            if ($type === 'services') {
                return [
                    'name'  => $row->name ?: '—',
                    'meta'  => '',
                    'stat'  => $row->trips . ' ' . textByLanguage('رحلة', 'trips'),
                    'value' => getPriceFormat($row->revenue),
                ];
            }

            return [
                'name'  => trim($row->name) ?: '—',
                'meta'  => '★ ' . round((float) $row->rating, 1),
                'stat'  => $row->trips . ' ' . textByLanguage('رحلة', 'trips'),
                'value' => getPriceFormat($row->revenue),
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
