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

        return Reply::ok([
            'tags' => RatingTagPresenter::forAudience(RatingTag::AUDIENCE_RIDER, $stars),
        ]);
    }
}
