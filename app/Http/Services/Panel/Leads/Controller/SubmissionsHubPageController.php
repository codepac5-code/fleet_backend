<?php

namespace App\Http\Services\Panel\Leads\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\ContactMessage;
use App\Models\DriverJobApplication;
use App\Models\OfficeRequest;
use Illuminate\Contracts\View\View;
use Throwable;

class SubmissionsHubPageController extends Controller
{
    public function __invoke(EntityScope $scope): View
    {
        return view('panel.leads.hub', [
            'entity' => $scope->guard(),
            'pending' => [
                'drivers' => $this->count(fn () => DriverJobApplication::query()->where('status', 'pending')->count()),
                'offices' => $this->count(fn () => OfficeRequest::query()->where('status', 'new')->count()),
                'contacts' => $this->count(fn () => ContactMessage::query()->where('status', 'new')->count()),
            ],
            'totals' => [
                'drivers' => $this->count(fn () => DriverJobApplication::query()->count()),
                'offices' => $this->count(fn () => OfficeRequest::query()->count()),
                'contacts' => $this->count(fn () => ContactMessage::query()->count()),
            ],
        ]);
    }

    private function count(callable $fn): int
    {
        try {
            return (int) $fn();
        } catch (Throwable $e) {
            return 0;
        }
    }
}
