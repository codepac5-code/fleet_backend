<?php

namespace App\Http\Services\Panel\Support\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\Complaint;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ComplaintsPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope): View
    {
        $status = $request->query('status');
        $status = $status !== null && $status !== '' ? (string) $status : null;
        $about = $request->query('about');
        $about = $about !== null && $about !== '' ? (string) $about : null;

        // complaints is a GLOBAL table; scope every read to the active country so
        // one country's dashboard never shows another's complaints. Null country =
        // aggregate "all countries" (super-admin) → no filter.
        $country = Complaint::activeCountryCode();

        // An office only ever sees complaints stamped with its own id (from the
        // complained-about booking); office-less complaints stay admin-only.
        $officeId = $scope->isOffice() || $scope->isEmployee() ? $scope->officeId() : null;

        $inCountry = fn () => Complaint::query()
            ->when($country !== null, fn ($q) => $q->where('country_code', $country))
            ->when($officeId !== null, fn ($q) => $q->where('office_id', $officeId));

        $complaints = $inCountry()
            ->with('user:id,firstName,lastName,phoneNumber')
            ->when($status !== null, fn ($q) => $q->where('status', $status))
            ->when($about !== null, fn ($q) => $q->where('about', $about))
            ->orderByRaw("CASE WHEN priority = 'urgent' THEN 0 ELSE 1 END")
            ->orderByDesc('id')
            ->limit(300)
            ->get();

        $countBy = fn (string $s) => $inCountry()->where('status', $s)->count();

        return view('panel.support.complaints', [
            'entity' => $scope->guard(),
            'complaints' => $complaints,
            'statusFilter' => $status,
            'aboutFilter' => $about,
            'counts' => [
                'open' => $countBy('open'),
                'urgent' => $inCountry()->where('priority', 'urgent')->whereNotIn('status', ['resolved', 'dismissed'])->count(),
                'resolved' => $countBy('resolved'),
                'total' => $inCountry()->count(),
            ],
        ]);
    }
}
