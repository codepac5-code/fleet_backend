<?php

namespace App\Http\Services\User\Trips\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\Support\Presenters\RatingTagPresenter;
use App\Http\Services\User\Support\Reply;
use App\Models\RatingTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RatingTagsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $stars = $request->query('stars');
        $stars = is_numeric($stars) ? (int) $stars : null;

        // The rider screen rates the driver AND the office, so it needs both
        // groups in one call; each tag carries the audience it belongs to.
        return Reply::ok([
            'tags' => RatingTagPresenter::forAudience([RatingTag::AUDIENCE_RIDER, RatingTag::AUDIENCE_OFFICE], $stars),
        ]);
    }
}
