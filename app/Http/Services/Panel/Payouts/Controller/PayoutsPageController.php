<?php

namespace App\Http\Services\Panel\Payouts\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Payment\PayoutService;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class PayoutsPageController extends Controller
{
    public function __invoke(EntityScope $scope, PayoutService $payouts): View
    {
        $rows = $scope->isAdmin()
            ? array_map(fn ($p) => $this->row($p, $p->owner_type, (int) $p->owner_id), $payouts->pending())
            : array_map(fn ($p) => $this->row($p, 'office', (int) $scope->officeId()), $payouts->listFor('office', (int) $scope->officeId()));

        return view('panel.payouts.index', [
            'entity'   => $scope->guard(),
            'isAdmin'  => $scope->isAdmin(),
            'payouts'  => $rows,
            'currency' => ShardManager::currency(),
        ]);
    }

    private function row($p, string $ownerType, int $ownerId): array
    {
        return [
            'id'             => (int) $p->id,
            'owner_type'     => $ownerType,
            'owner_id'       => $ownerId,
            'source_account' => $p->source_account,
            'amount_minor'   => (int) $p->amount_minor,
            'currency_code'  => $p->currency_code,
            'status'         => $p->status,
            'processed_at'   => $p->processed_at ?? null,
        ];
    }
}
