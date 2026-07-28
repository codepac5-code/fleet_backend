<?php

namespace App\Http\Services\Panel\Support\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\FamilyMember;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class FamilyMembersPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope): View
    {
        $type = $request->query('type');
        $type = $type !== null && $type !== '' ? (string) $type : null;

        $members = FamilyMember::query()
            ->when($type !== null, fn ($q) => $q->where('type', $type))
            ->orderByDesc('id')
            ->limit(300)
            ->get();

        $guardians = User::query()
            ->whereIn('id', $members->pluck('user_id')->unique()->all())
            ->get(['id', 'firstName', 'lastName', 'phoneNumber'])
            ->keyBy('id');

        $countBy = fn (string $t) => FamilyMember::query()->where('type', $t)->count();

        return view('panel.support.family-members', [
            'entity' => $scope->guard(),
            'members' => $members,
            'guardians' => $guardians,
            'typeFilter' => $type,
            'counts' => [
                'minor' => $countBy('minor'),
                'elder' => $countBy('elder'),
                'adult' => $countBy('adult'),
                'total' => FamilyMember::query()->count(),
            ],
        ]);
    }
}
