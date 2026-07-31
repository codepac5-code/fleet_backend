<?php

namespace App\Http\Services\Panel\Bookings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Bookings\Logic\BookingRepository;
use App\Http\Services\Panel\Bookings\Logic\ScheduledTripPresenter;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduledDataController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, BookingRepository $bookings): JsonResponse
    {
        $group = (string) $request->query('status', 'upcoming');
        if (! array_key_exists($group, BookingRepository::COLUMN_STATUSES)) {
            $group = 'upcoming';
        }

        $date     = $request->query('date') ?: null;
        $driver   = (int) $request->query('driver') ?: null;
        $page     = max(1, (int) $request->query('page', 1));
        $officeId = $scope->isAdmin() ? ((int) $request->query('office') ?: null) : $scope->officeId();

        $paginator = $bookings->scheduledData($group, $date, $driver, $officeId, $page);
        $entity    = $scope->guard();

        $data = array_map(
            fn ($row) => ScheduledTripPresenter::toArray($row, $entity),
            $paginator->items()
        );

        return response()->json([
            'data'         => $data,
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'stats'        => $bookings->scheduledStats($group, $date, $driver, $officeId),
        ]);
    }
}
