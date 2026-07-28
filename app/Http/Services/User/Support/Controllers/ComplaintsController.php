<?php

namespace App\Http\Services\User\Support\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\Support\Logic\SupportService;
use App\Http\Services\User\Support\Reply;
use App\Http\Services\User\Support\Requests\ComplaintRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplaintsController extends Controller
{
    public function __construct(private SupportService $support)
    {
    }

    public function store(ComplaintRequest $request): JsonResponse
    {
        $data = $request->validated();

        return Reply::ok(
            $this->support->complaint(
                (int) $request->user()->id,
                $data['about'],
                isset($data['tripId']) ? (int) $data['tripId'] : null,
                $data['description'],
                $data['photoUrl'] ?? null
            ),
            201
        );
    }

    /**
     * Upload a complaint evidence photo and return its URL, so the rider can
     * attach it to a complaint (posted separately as `photoUrl`). Stored on the
     * public disk — NOT tied to the user's profile like /me/photo.
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'max:5120'],
        ]);

        $path = $request->file('photo')->store('complaints', 'public');

        return Reply::ok(['url' => $path]);
    }
}
