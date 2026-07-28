<?php

namespace App\Http\Services\User\B2B\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\B2B\Logic\B2BService;
use App\Http\Services\User\B2B\Requests\FamilyMemberRequest;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FamilyController extends Controller
{
    public function __construct(private B2BService $b2b)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return Reply::ok($this->b2b->familyList((int) $request->user()->id));
    }

    public function store(FamilyMemberRequest $request): JsonResponse
    {
        return Reply::ok($this->b2b->familyAdd((int) $request->user()->id, $request->validated()), 201);
    }

    public function update(FamilyMemberRequest $request, int $id): JsonResponse
    {
        return Reply::ok($this->b2b->familyUpdate((int) $request->user()->id, $id, $request->validated()));
    }

    public function destroy(Request $request, int $id): Response
    {
        $this->b2b->familyRemove((int) $request->user()->id, $id);

        return response()->noContent();
    }
}
