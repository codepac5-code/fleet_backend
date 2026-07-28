<?php

namespace App\Http\Services\User\Marketplace\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Marketplace\FavoriteOfficeService;
use App\Http\Services\User\Support\Presenters\OfficePresenter;
use App\Http\Services\User\Support\Reply;
use App\Models\FavoriteOffice;
use App\Models\Office;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FavoritesController extends Controller
{
    public function __construct(private FavoriteOfficeService $favorites)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $rows = FavoriteOffice::query()
            ->where('user_id', (int) $request->user()->id)
            ->orderByDesc('id')
            ->get();

        $offices = Office::query()->whereIn('id', $rows->pluck('office_id')->all())->get()->keyBy('id');

        $data = $rows->map(function (FavoriteOffice $row) use ($offices) {
            $office = $offices->get($row->office_id);

            if ($office === null) {
                return null;
            }

            return array_merge(OfficePresenter::card($office), [
                'favoritedAt' => optional($row->created_at)->toIso8601String(),
            ]);
        })->filter()->values()->all();

        return Reply::ok($data);
    }

    public function store(Request $request, int $officeId): Response
    {
        $this->favorites->add((int) $request->user()->id, $officeId);

        return response()->noContent();
    }

    public function destroy(Request $request, int $officeId): Response
    {
        $this->favorites->remove((int) $request->user()->id, $officeId);

        return response()->noContent();
    }
}
